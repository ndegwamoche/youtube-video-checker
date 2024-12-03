<?php
/*
Plugin Name: YouTube Video Checker
Description: Check posts for YouTube videos by category, identify missing videos, and add them to the respective posts to ensure all content is complete and properly embedded.
Version: 1.0
Author: Moche
Author URI: https://www.linkedin.com/in/ndegwer/
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('YVC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YVC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload required files
require_once YVC_PLUGIN_DIR . 'includes/class-yvc-init.php';
require_once YVC_PLUGIN_DIR . 'includes/class-yvc-admin.php';
require_once YVC_PLUGIN_DIR . 'includes/class-yvc-ajax.php';
require_once YVC_PLUGIN_DIR . 'includes/class-yvc-rest.php';
require_once YVC_PLUGIN_DIR . 'includes/class-yvc-utils.php';
require_once YVC_PLUGIN_DIR . 'includes/class-yvc-download.php';

// Initialize the plugin
new YVC_Init();
