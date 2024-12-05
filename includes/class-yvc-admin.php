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
 * Class YVC_Admin
 *
 * Handles the admin-related functionalities of the YouTube Video Checker plugin,
 * including enqueuing scripts, creating menu pages, and rendering the admin UI.
 *
 * @category Admin
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/your-repo-link
 */
class YVC_Admin
{
    /**
     * Enqueue admin scripts and styles.
     *
     * Loads Bootstrap for the admin UI and the plugin's main JavaScript file.
     * Localizes the script with necessary data for AJAX and REST API usage.
     *
     * @param string $hook The current admin page hook.
     * 
     * @return void
     */
    public static function enqueueScripts($hook)
    {
        if (!is_admin()) {
            return;
        }

        // Enqueue Bootstrap CSS
        wp_enqueue_style(
            'bootstrap-css',
            'https://' .
                'stackpath.bootstrapcdn.com/' .
                'bootstrap/4.5.2/css/bootstrap.min.css',
            [],
            null
        );

        // Enqueue the plugin's main JavaScript file
        wp_enqueue_script(
            'youtube-video-checker-script',
            plugin_dir_url(__FILE__) . '../build/index.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
            filemtime(plugin_dir_path(__FILE__) . '../build/index.js'),
            true
        );

        // Localize the script with data for AJAX and REST API
        wp_localize_script(
            'youtube-video-checker-script',
            'yvcData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restUrl' => esc_url(
                    rest_url('youtube-video-checker/v1/check-videos-progress')
                ),
                'nonce'   => wp_create_nonce('check_videos_nonce'),
            ]
        );
    }

    /**
     * Create the admin menu page.
     *
     * Adds a top-level menu page for the plugin in the WordPress admin dashboard.
     *
     * @return void
     */
    public static function createAdminPage()
    {
        add_menu_page(
            'YouTube Video Checker',
            'YouTube Checker',
            'manage_options',
            'youtube-video-checker',
            ['YVC_Admin', 'renderAdminPage'],
            'dashicons-video-alt3'
        );
    }

    /**
     * Render the admin page.
     *
     * Outputs the HTML for the admin interface, 
     * where the React application will be rendered.
     *
     * @return void
     */
    public static function renderAdminPage()
    {
        echo '<div class="wrap"><div id="youtube-video-checker"></div></div>';
    }
}
