<?php

class YVC_Rest
{
    public static function register_routes()
    {
        register_rest_route('youtube-video-checker/v1', '/fix-video', [
            'methods' => 'POST',
            'callback' => ['YVC_Rest', 'check_youtube_videos_progress'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function check_youtube_videos_progress(WP_REST_Request $request)
    {
        $post_id = $request->get_param('postId');
        $video_id = $request->get_param('videoId');
        $video_title = $request->get_param('videoTitle');

        // Get the post object by ID
        $post = get_post($post_id);

        // Check if the post exists
        if (!$post) {
            wp_send_json_error([
                'message' => 'Post not found.',
            ]);
            return;
        }

        // Get the post content
        $post_content = $post->post_content;

        // Initialize result array
        $processed_posts = [];

        try {
            // Initialize FastDownload instance to search for a video
            $download = new YVC_FastDownload();

            $search_result = $download->search_video_using_fastdownload($video_title);
            //$search_result = $download->search_video_using_yt_dlp($video_title);

            // If there's an error in the FastDownload API, return an error
            if (isset($search_result['error'])) {
                wp_send_json_error([
                    'message' => 'FastDownload Error: ' . $search_result['error']
                ]);
                return;
            }

            // print_r($search_result);
            // exit;

            // Set the found video ID if search is successful
            $video_id_to_use = $search_result['id'];


            if (empty($video_id)) {

                $new_video_url = 'https://www.youtube.com/watch?v=' . $video_id_to_use;

                // Define fallback content if $video_id is empty
                $fallback_content = '
                <!-- wp:embed {"url":"' . $new_video_url . '","type":"video","providerNameSlug":"youtube","responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
                <figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
                    <div class="wp-block-embed__wrapper">
                        ' . $new_video_url . '
                    </div>
                </figure>
                <!-- /wp:embed -->';



                // Update the post with fallback content
                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $fallback_content
                ]);

                // echo $fallback_content;
                // exit;

                $processed_posts[] = 'Post ' . $post_id . ' updated with fallback content.';
            } else {
                // Replace the old video ID with the new one in the post content
                $updated_content = str_replace($video_id, $video_id_to_use, $post_content);

                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $updated_content
                ]);
            }

            // Send success response
            wp_send_json_success([
                'message' => 'Videos fixed successfully.',
                'processed_posts' => $processed_posts
            ]);
        } catch (Exception $e) {
            // Handle exceptions and send error message
            wp_send_json_error([
                'message' => 'An error occurred while accessing FastDownload API: ' . $e->getMessage(),
            ]);
        }
    }
}
