<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class YouTubeVideoChecker
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_menu', [$this, 'create_admin_page']);
        add_action('wp_ajax_yvc_get_categories', [$this, 'get_categories']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route('youtube-video-checker/v1', '/check-videos-progress', [
            'methods' => 'POST',
            'callback' => [$this, 'check_youtube_videos_progress'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_categories()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'You do not have permission to view categories.']);
        }

        $categories = get_terms([
            'taxonomy' => 'category',
            'hide_empty' => false,
        ]);

        if (is_wp_error($categories)) {
            wp_send_json_error(['message' => 'Unable to fetch categories.']);
        }

        $category_list = array_map(function ($category) {
            return [
                'id' => $category->term_id,
                'name' => $category->name,
            ];
        }, $categories);

        wp_send_json_success($category_list);
    }

    public function enqueue_scripts($hook)
    {
        if (!is_admin()) {
            return;
        }

        wp_enqueue_style(
            'bootstrap-css',
            'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
            [],
            null
        );

        wp_enqueue_script(
            'youtube-video-checker-script',
            plugin_dir_url(__FILE__) . 'build/index.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            filemtime(plugin_dir_path(__FILE__) . 'build/index.js'),
            true
        );

        wp_localize_script('youtube-video-checker-script', 'yvcData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => esc_url(rest_url('youtube-video-checker/v1/check-videos-progress')),
            'nonce' => wp_create_nonce('check_videos_nonce'),
        ]);
    }

    public function create_admin_page()
    {
        add_menu_page(
            'YouTube Video Checker',
            'YouTube Checker',
            'manage_options',
            'youtube-video-checker',
            [$this, 'render_admin_page'],
            'dashicons-video-alt3'
        );
    }

    public function check_if_youtube_video_exist($video_id)
    {
        $cache_key = 'youtube_video_exists_' . $video_id;
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result === '1';
        }

        $url = "https://www.youtube.com/watch?v=" . $video_id;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if ($content === false || strpos($content, "Video unavailable") !== false) {
            set_transient($cache_key, '0', 12 * HOUR_IN_SECONDS);
            return false;
        }

        set_transient($cache_key, '1', 12 * HOUR_IN_SECONDS);
        return true;
    }

    public function search_video_using_yt_dlp($query)
    {
        // Run yt-dlp command to search for videos
        $command = escapeshellcmd("yt-dlp --quiet --print-json ytsearch:'$query' 2>&1");  // Capture both output and error
        $output = shell_exec($command);

        if ($output) {
            // Attempt to decode the JSON result
            $result = json_decode($output, true);
            if (isset($result['id'])) {
                return $result;  // Return video details like ID
            } else {
                // If JSON decode failed, return the error message from yt-dlp
                return [
                    'error' => 'yt-dlp Error: ' . $output  // Return the full error output from yt-dlp
                ];
            }
        } else {
            // If there's no output, handle the case where yt-dlp failed silently
            return [
                'error' => 'yt-dlp failed to search or returned no result for the query: ' . $query
            ];
        }

        return false;
    }

    public function render_admin_page()
    {
        echo '<div class="wrap"><div id="youtube-video-checker"></div></div>';
    }

    private function get_video_id_from_url($url)
    {
        // Extract the video ID from the YouTube URL
        preg_match('/(?:watch\?v=|embed\/)([a-zA-Z0-9_-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    public function search_video_using_fastdownload($query)
    {
        // Prepare the API URL
        $api_url = 'https://fastdownload.video/api/youtube/search?query=' . urlencode($query);

        // Use wp_remote_get to fetch data from the API
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return [
                'error' => 'Error fetching data from FastDownload API: ' . $response->get_error_message()
            ];
        }

        // Decode the JSON response
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['results']) && count($data['results']) > 0) {
            // Return the first result
            return [
                'id' => $this->get_video_id_from_url($data['results'][0]['link']),
                'title' => $data['results'][0]['title'],
                'link' => $data['results'][0]['link']
            ];
        }

        return [
            'error' => 'No results found for query: ' . $query
        ];
    }

    public function check_youtube_videos_progress(WP_REST_Request $request)
    {
        $nonce = $request->get_param('nonce');

        if (empty($nonce)) {
            return new WP_REST_Response([
                'success' => false,
                'data' => ['message' => 'Nonce verification failed.']
            ]);
        }

        // Validate category ID
        if (!isset($request['category_id']) || empty($request['category_id'])) {
            return new WP_REST_Response([
                'success' => false,
                'data' => ['message' => 'No category ID provided.']
            ]);
        }

        $category_id = intval($request['category_id']);
        // Get posts in the given category
        $posts = get_posts([
            'category' => $category_id,
            'posts_per_page' => -1,
            'meta_key' => 'wpb_post_views_count',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        ]);

        $totalPosts = count($posts);

        if ($totalPosts === 0) {
            wp_send_json_error(['message' => 'No posts found in the selected category.']);
        }

        // Initialize FastDownload API handler
        $count = 0;
        $processed_posts = [];

        foreach ($posts as $index => $post) {
            $content = $post->post_content;

            // Check for YouTube link in the content (standard or embedded)
            preg_match('/https?:\/\/(www\.)?youtube\.com\/(?:watch\?v=|embed\/)([a-zA-Z0-9_-]+)/', $content, $matches);
            $youtube_url = isset($matches[0]) ? $matches[0] : '';
            $video_id = isset($matches[2]) ? $matches[2] : '';

            if (empty($video_id)) {
                try {
                    // Use the FastDownload API to search for a video based on the post title
                    $search_result = $this->search_video_using_fastdownload($post->post_title);

                    if (isset($search_result['error'])) {
                        wp_send_json_error([
                            'message' => 'FastDownload Error: ' . $search_result['error']
                        ]);
                    }

                    // Extract the new video ID and URL
                    $new_video_id = $search_result['id'];
                    $new_video_url = 'https://www.youtube.com/embed/' . $new_video_id;

                    // Replace the old video URL (both standard or embed format) with the new one in the post content
                    $updated_content = preg_replace(
                        '/https?:\/\/(www\.)?youtube\.com\/(?:watch\?v=|embed\/)[a-zA-Z0-9_-]+/',
                        $new_video_url,
                        $post->post_content
                    );

                    // Update the post content with the new video URL
                    wp_update_post([
                        'ID' => $post->ID,
                        'post_content' => $updated_content
                    ]);

                    $processed_posts[] = 'Post ' . $post->ID . ' updated successfully.';
                } catch (Exception $e) {
                    wp_send_json_error([
                        'message' => 'An error occurred while accessing FastDownload API: ' . $e->getMessage(),
                        'progress' => round($count / $totalPosts) * 1000
                    ]);
                }
            } else {
                // Video ID exists, check if the YouTube video still exists
                if (!$this->check_if_youtube_video_exist($video_id)) {
                    try {
                        // Use FastDownload API to search for a new video
                        $search_result = $this->search_video_using_fastdownload($post->post_title);

                        if (isset($search_result['error'])) {
                            wp_send_json_error([
                                'message' => 'FastDownload Error: ' . $search_result['error'],
                                'progress' => round($count / $totalPosts) * 1000
                            ]);
                        }

                        // Extract the new video ID and URL
                        $new_video_url = 'https://www.youtube.com/embed/' . $search_result['id'];

                        // Replace the old video URL (both standard or embed format) with the new one in the post content
                        $updated_content = preg_replace(
                            '/https?:\/\/(www\.)?youtube\.com\/(?:watch\?v=|embed\/)[a-zA-Z0-9_-]+/',
                            $new_video_url,
                            $post->post_content
                        );

                        // Update the post content
                        wp_update_post([
                            'ID' => $post->ID,
                            'post_content' => $updated_content
                        ]);

                        $processed_posts[] = 'Post ' . $post->ID . ' updated successfully.';
                    } catch (Exception $e) {
                        // Halt progress and send error message
                        wp_send_json_error([
                            'message' => 'An error occurred while accessing FastDownload API: ' . $e->getMessage(),
                            'progress' => round($count / $totalPosts) * 1000
                        ]);
                    }
                }
            }

            $count++;

            // Update progress
            $progress = round($count / $totalPosts) * 100;
            update_option('yvc_video_update_progress', $progress);
        }

        // Once all posts are processed, reset the progress
        delete_option('yvc_video_update_progress');

        wp_send_json_success([
            'message' => 'Video check completed.',
            'progress' => 100,
            'processed_posts' => $processed_posts
        ]);
    }
}

new YouTubeVideoChecker();
