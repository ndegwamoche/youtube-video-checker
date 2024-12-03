<?php

class YVC_Init
{
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
