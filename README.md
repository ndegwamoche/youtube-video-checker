
# YouTube Video Checker

**Version:** 1.0  
**Author:** [Martin Ndegwa Moche](https://www.linkedin.com/in/ndegwamoche/)  
**Description:** This WordPress plugin checks posts for YouTube videos by category, identifies missing or outdated videos, and ensures all posts are properly embedded with YouTube videos.

----------

## Features

-   **Categorical Video Checking:**
    -   Scan WordPress posts within specific categories for YouTube video embeds.
    -   Filter categories to focus on specific areas of your site.
-   **Detect Missing Videos:**
    -   Identify posts with missing or invalid YouTube video embeds.
-   **Embed YouTube Videos Automatically:**
    -   Add updated video links fetched via the FastDownload API to ensure posts are complete.
-   **Supports Multiple Embed Formats:**
    -   Standard YouTube URLs (e.g., `https://youtube.com/watch?v=...`).
    -   Embedded iframe URLs (e.g., `https://youtube.com/embed/...`).
    -   Short URLs (e.g., `https://youtu.be/...`).
-   **Admin Dashboard Integration:**
    -   Easy-to-use admin page to interact with categories and posts.
    -   AJAX-powered loading for a smooth and responsive experience.
-   **REST API Integration:**
    -   Custom REST API endpoints to extend the plugin functionality.

----------

![enter image description here](https://raw.githubusercontent.com/ndegwamoche/youtube-video-checker/main/screenshot.png)

## Installation

1.  **Download or Clone:**
    
    -   Download this plugin repository or clone it into the `/wp-content/plugins/` directory of your WordPress site:
        
        `git clone https://github.com/your-repo/youtube-video-checker.git` 
        
2.  **Activate the Plugin:**
    
    -   Log in to your WordPress admin dashboard.
    -   Navigate to **Plugins > Installed Plugins**.
    -   Locate **YouTube Video Checker** and click **Activate**.
3.  **Install Dependencies (if applicable):**
    
    -   Navigate to the plugin folder and run:
        
        `composer install
        npm install` 
        
----------

## Usage

### Admin Panel

-   Navigate to **YouTube Video Checker** in the WordPress admin menu.
-   Use the **Category Filter** dropdown to select a category.
-   Click **Check Videos** to scan posts for missing or invalid YouTube videos.

### Automatic Updates

-   **Detect Missing Videos:**
    -   Identifies posts without valid YouTube embeds.
-   **Replace Video Links:**
    -   Automatically updates invalid video links using the FastDownload API.
-   **Insert Default Embed:**
    -   If a post lacks a video, a default YouTube video embed block can be inserted.

----------

## File Structure
```
youtube-video-checker/
│
├── build/
│ ├── index.js # Compiled React components
│ ├── index.asset.php # Asset dependencies
│
├── includes/
│ ├── class-yvc-admin.php # Handles admin interface
│ ├── class-yvc-ajax.php # Handles AJAX requests
│ ├── class-yvc-download.php # FastDownload integration
│ ├── class-yvc-init.php # Plugin initializer
│ ├── class-yvc-rest.php # REST API handler
│ ├── class-yvc-utils.php # Utility functions
│
├── src/
│ ├── components/
│ │ ├── CategoryForm.js # React component for the admin form
│ │ ├── VideoList.js # React component for video lists
│ ├── app.js # Main React app entry
│ ├── index.js # React app renderer
│
├── youtube-video-checker.php # Main plugin file
├── composer.json # PHP dependency manager
├── package.json # Node.js dependencies
└── LICENSE # License file
```
----------

## REST API Endpoints

### Categories API

-   **Endpoint:** `/wp-admin/admin-ajax.php?action=yvc_get_categories`
-   **Method:** POST
-   **Purpose:** Fetches all available categories.

### Posts API

-   **Endpoint:** `/wp-admin/admin-ajax.php?action=yvc_get_posts`
-   **Method:** POST
-   **Purpose:** Fetches posts under a selected category.

### Video Update API

-   **Endpoint:** `/wp-json/yvc/v1/videos/check`
-   **Method:** POST
-   **Parameters:**
    -   `postId`: The ID of the post to check.
    -   `videoId`: The current YouTube video ID in the post.
    -   `videoTitle`: The title of the post or video for searching new videos.

----------

## Code Snippets

### Extract YouTube Video IDs

This function extracts YouTube video IDs from various formats of YouTube URLs.

`function extract_youtube_video_id($content) {
    $pattern = '/https:\/\/(?:www\.)?(youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    if (preg_match($pattern, $content, $matches)) {
        return $matches[2]; // YouTube video ID
    }
    return '';
}` 

### Replace Video Content in Posts

`$updated_content = str_replace($video_id, $video_id_to_use, $post_content);
if ($updated_content !== $post_content) {
    wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content,
    ]);
}` 

----------

## Contributing

Contributions are welcome! To contribute:

1.  Fork the repository.
2.  Create a new branch:
    
    `git checkout -b feature-branch-name` 
    
3.  Commit your changes:
    
    `git commit -m "Add a new feature"` 
    
4.  Push to the branch:
    
    `git push origin feature-branch-name` 
    
5.  Submit a pull request.

----------

## License

This plugin is licensed under the **MIT License**. See the LICENSE file for details.

----------

If you have any issues or need support, please contact [Martin Ndegwa Moche](https://www.linkedin.com/in/ndegwemoche/).
