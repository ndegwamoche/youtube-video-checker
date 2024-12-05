<?php

/**
 * YouTube Video Checker Plugin
 * 
 * @category Plugin
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 */

/**
 * Class YVC_Init
 *
 * Initializes the YouTube Video Checker plugin by setting up admin, AJAX, 
 * and REST API handlers.
 *
 * @category Core
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 */
class YVC_Init
{
    /**
     * Constructor for YVC_Init
     *
     * Instantiates the required classes and registers WordPress hooks for
     * admin scripts, admin pages, AJAX, and REST API routes.
     */
    public function __construct()
    {
        // Instantiate classes
        $admin_handler = new YVC_Admin();
        $ajax_handler = new YVC_Ajax();
        $rest_handler = new YVC_Rest();

        // Register hooks
        add_action('admin_enqueue_scripts', [$admin_handler, 'enqueue_scripts']);
        add_action('admin_menu', [$admin_handler, 'create_admin_page']);
        add_action('wp_ajax_yvc_get_categories', [$ajax_handler, 'get_categories']);
        add_action('wp_ajax_yvc_get_posts', [$ajax_handler, 'get_posts']);
        add_action('rest_api_init', [$rest_handler, 'register_routes']);
    }
}
