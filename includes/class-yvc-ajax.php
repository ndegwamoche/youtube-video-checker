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
 * Class YVC_Ajax
 *
 * Handles AJAX requests for fetching posts and categories
 * in the YouTube Video Checker plugin.
 *
 * @category Core
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 */
class YVC_Ajax
{
    /**
     * Handle the AJAX request to fetch posts within a specific category.
     *
     * Validates user permissions and input, retrieves posts, 
     * and checks for YouTube videos.
     *
     * @return void
     */
    public function get_posts()
    {
        // Check for permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                ['message' => 'You do not have permission to perform this action.'],
                403
            );
        }

        // Validate the category ID
        if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
            wp_send_json_error(['message' => 'No category ID provided.'], 400);
        }

        $category_id = intval($_POST['category_id']);

        // Fetch posts in the specified category
        $posts = get_posts(
            [
                'category'       => $category_id,
                'posts_per_page' => 100,
                'meta_key'       => 'wpb_post_views_count',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
            ]
        );

        if (empty($posts)) {
            wp_send_json_error(
                ['message' => 'No posts found in the selected category.'],
                404
            );
        }

        $processed_posts = [];

        foreach ($posts as $post) {
            $content = $post->post_content;

            // Check for YouTube links in the post content
            preg_match(
                '/https?:\/\/(www\.)?youtube\.com\/(?:watch\?v=|embed\/)([a-zA-Z0-9_-]+)/',
                $content,
                $matches
            );

            $youtube_url = isset($matches[0]) ? $matches[0] : '';
            $video_id    = isset($matches[2]) ? $matches[2] : '';

            $utils = new YVC_Utils();

            $video_exists = $utils->check_if_youtube_video_exist($video_id);

            if (empty($video_id) || $video_exists === false) {
                $processed_posts[] = [
                    'id'          => $post->ID,
                    'title'       => $post->post_title,
                    'youtube_url' => $youtube_url,
                    'video_id'    => $video_id,
                ];
            }
        }

        // Respond with the list of processed posts
        wp_send_json_success(
            [
                'videos' => $processed_posts,
                'total'  => count($processed_posts),
            ]
        );
    }

    /**
     * Handle the AJAX request to fetch categories.
     *
     * Retrieves and returns the list of categories in a structured format.
     *
     * @return void
     */
    public function get_categories()
    {
        // Check for permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                ['message' => 'You do not have permission to view categories.']
            );
        }

        $categories = get_terms(
            [
                'taxonomy'   => 'category',
                'hide_empty' => false,
            ]
        );

        if (is_wp_error($categories)) {
            wp_send_json_error(['message' => 'Unable to fetch categories.']);
        }

        $category_list = array_map(
            function ($category) {
                return [
                    'id'   => $category->term_id,
                    'name' => $category->name,
                ];
            },
            $categories
        );

        // Respond with the category list
        wp_send_json_success($category_list);
    }
}
