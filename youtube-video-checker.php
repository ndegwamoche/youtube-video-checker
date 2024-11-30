<?php

/**
 * Plugin Name: YouTube Video Checker
 * Description: Check posts for YouTube videos by category, identify missing videos, and add them to the respective posts to ensure all content is complete and properly embedded.
 * Version: 1.0
 * Author: Martin Ndegwa Moche
 * License: GPLv3
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue React app scripts
function yvc_enqueue_scripts($hook)
{
    // Only enqueue on admin pages
    if (!is_admin()) {
        return;
    }

    // Enqueue Bootstrap CSS (Ensure it's loaded from a CDN or local file)
    wp_enqueue_style(
        'bootstrap-css',
        'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',  // Use CDN for Bootstrap
        array(),
        null // Prevent caching
    );

    // Enqueue React app's compiled JS
    wp_enqueue_script(
        'youtube-video-checker-script',
        plugin_dir_url(__FILE__) . 'build/index.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js'),
        true
    );

    // Pass AJAX URL to React app
    wp_localize_script('youtube-video-checker-script', 'yvcData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
    ]);
}

add_action('admin_enqueue_scripts', 'yvc_enqueue_scripts');

// Create admin menu page
function yvc_admin_page()
{
?>
    <div class="wrap">
        <div id="youtube-video-checker"></div> <!-- React App will render here -->
    </div>
<?php
}

add_action('admin_menu', function () {
    add_menu_page(
        'YouTube Video Checker',
        'YouTube Checker',
        'manage_options',
        'youtube-video-checker',
        'yvc_admin_page',
        'dashicons-video-alt3'
    );
});

// AJAX handler to get categories
function yvc_get_categories()
{
    // Check if the current user has permission to access the categories
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'You do not have permission to view categories.']);
    }

    // Get all categories
    $categories = get_terms([
        'taxonomy' => 'category', // Default category taxonomy
        'hide_empty' => false,    // Include empty categories
    ]);

    if (is_wp_error($categories)) {
        wp_send_json_error(['message' => 'Unable to fetch categories.']);
    }

    // Prepare the categories as an array of ID => Name pairs
    $category_list = [];
    foreach ($categories as $category) {
        $category_list[] = [
            'id' => $category->term_id,
            'name' => $category->name,
        ];
    }

    // Send back the categories as a JSON response
    wp_send_json_success($category_list);
}

// Register the AJAX action for logged-in users
add_action('wp_ajax_yvc_get_categories', 'yvc_get_categories');

// AJAX handler for checking YouTube videos
// This handles the progress request (AJAX polling)
function get_progress_callback()
{
    session_start();

    // Return the current progress stored in the session
    if (isset($_SESSION['progress'])) {
        echo json_encode([
            'success' => true,
            'data' => $_SESSION['progress']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'data' => []
        ]);
    }

    die();
}

// This function checks the videos (processing logic)
// AJAX handler for checking YouTube videos (processing task)
function check_youtube_videos_callback()
{
    session_start();

    // Ensure there's no extra output before sending JSON
    ob_clean();

    // Retrieve category and posts
    $category_id = $_POST['category_id'];
    $posts = get_posts([
        'category' => $category_id,
        'posts_per_page' => -1
    ]);

    $totalPosts = count($posts);
    $postsProcessed = 0;

    // Initialize progress in session
    $_SESSION['progress'] = [
        'totalPosts' => $totalPosts,
        'postsProcessed' => $postsProcessed,
        'progress' => 0
    ];

    // Loop through posts and simulate checking
    foreach ($posts as $post) {
        $postsProcessed++;

        // Update progress in session
        $_SESSION['progress'] = [
            'totalPosts' => $totalPosts,
            'postsProcessed' => $postsProcessed,
            'progress' => round(($postsProcessed / $totalPosts) * 100)
        ];

        // Simulate some processing delay
        usleep(500000); // Adjust as needed (0.5 seconds delay)
    }

    // Finalizing the progress
    $_SESSION['progress'] = [
        'totalPosts' => $totalPosts,
        'postsProcessed' => $totalPosts,
        'progress' => 100
    ];

    // Return the final progress result (end of task)
    echo json_encode([
        'success' => true,
        'data' => $_SESSION['progress']
    ]);

    // End the process
    die();
}


// Register the AJAX action for checking YouTube videos
add_action('wp_ajax_check_youtube_videos', 'check_youtube_videos_callback');

// Register the AJAX action for getting progress
add_action('wp_ajax_get_progress', 'get_progress_callback');
