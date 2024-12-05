<?php

/**
 * YouTube Video Checker Plugin
 *
 * @category Plugin
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 * @requires PHP 7.4
 */

/**
 * Class YVC_Rest
 *
 * Handles REST API routes for the YouTube Video Checker plugin.
 *
 * @category Core
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 */
class YVC_Rest
{
    /**
     * Register REST API routes.
     *
     * @return void
     */
    public static function register_routes()
    {
        register_rest_route(
            'youtube-video-checker/v1',
            '/fix-video',
            [
                'methods'             => 'POST',
                'callback'            => ['YVC_Rest', 'check_youtube_videos_progress'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * Check and fix YouTube videos in posts.
     *
     * Handles fixing broken YouTube video embeds in posts by using FastDownload.
     *
     * @param WP_REST_Request $request The REST API request object.
     *
     * @return void
     */
    public static function check_youtube_videos_progress(WP_REST_Request $request)
    {
        $post_id    = $request->get_param('postId');
        $video_id   = $request->get_param('videoId');
        $video_title = $request->get_param('videoTitle');

        // Get the post object by ID
        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(
                ['message' => 'Post not found.'],
                404
            );
            return;
        }

        $post_content    = $post->post_content;
        $processed_posts = [];

        try {
            // Initialize FastDownload instance to search for a video
            $download = new YVC_FastDownload();
            $search_result = $download->search_video_using_fastdownload($video_title);

            // Handle errors from FastDownload API
            if (isset($search_result['error'])) {
                wp_send_json_error(
                    ['message' => 'FastDownload Error: ' . esc_html($search_result['error'])],
                    500
                );
                return;
            }

            $video_id_to_use = $search_result['id'];

            if (empty($video_id)) {
                $new_video_url = 'https://www.youtube.com/watch?v=' . $video_id_to_use;

                $fallback_content = sprintf(
                    '
                    <!-- wp:embed {"url":"%1$s","type":"video","providerNameSlug":"youtube",
                    "responsive":true,"className":"wp-embed-aspect-16-9 wp-has-aspect-ratio"} -->
                    <figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
                        <div class="wp-block-embed__wrapper">%1$s</div>
                    </figure>
                    <!-- /wp:embed -->',
                    esc_url($new_video_url)
                );

                wp_update_post(
                    [
                        'ID'           => $post_id,
                        'post_content' => $fallback_content,
                    ]
                );

                $processed_posts[] = 'Post ' . esc_html($post_id) . ' updated with fallback content.';
            } else {
                $updated_content = str_replace($video_id, $video_id_to_use, $post_content);

                wp_update_post(
                    [
                        'ID'           => $post_id,
                        'post_content' => $updated_content,
                    ]
                );
            }

            wp_send_json_success(
                [
                    'message'         => 'Videos fixed successfully.',
                    'processed_posts' => $processed_posts,
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error(
                ['message' => 'An error occurred while accessing FastDownload API: ' . esc_html($e->getMessage())],
                500
            );
        }
    }
}
