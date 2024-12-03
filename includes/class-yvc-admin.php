<?php

class YVC_Admin
{
    public static function enqueue_scripts($hook)
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
            plugin_dir_url(__FILE__) . '../build/index.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            filemtime(plugin_dir_path(__FILE__) . '../build/index.js'),
            true
        );

        wp_localize_script('youtube-video-checker-script', 'yvcData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => esc_url(rest_url('youtube-video-checker/v1/check-videos-progress')),
            'nonce' => wp_create_nonce('check_videos_nonce'),
        ]);
    }

    public static function create_admin_page()
    {
        add_menu_page(
            'YouTube Video Checker',
            'YouTube Checker',
            'manage_options',
            'youtube-video-checker',
            ['YVC_Admin', 'render_admin_page'],
            'dashicons-video-alt3'
        );
    }

    public static function render_admin_page()
    {
        echo '<div class="wrap"><div id="youtube-video-checker"></div></div>';
    }
}
