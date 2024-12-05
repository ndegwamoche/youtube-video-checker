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
 * Class YVC_Utils
 *
 * Provides utility functions for checking YouTube videos and extracting video IDs.
 *
 * @category Core
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 */
class YVC_Utils
{
    /**
     * Check if a YouTube video exists based on its ID.
     *
     * @param string $video_id The YouTube video ID.
     * 
     * @return bool True if the video exists, false otherwise.
     */
    public function checkIfYouTubeVideoExist($video_id)
    {
        $cache_key = 'youtube_video_exists_' . $video_id;
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result === '1';
        }

        $url = "https://www.youtube.com/watch?v=" . $video_id;
        $context = stream_context_create(
            [
                'http' => [
                    'method' => 'GET',
                    'ignore_errors' => true,
                ],
            ]
        );

        $content = @file_get_contents($url, false, $context);
        if ($content === false || strpos($content, "Video unavailable") !== false) {
            set_transient($cache_key, '0', 12 * HOUR_IN_SECONDS);
            return false;
        }

        set_transient($cache_key, '1', 12 * HOUR_IN_SECONDS);
        return true;
    }

    /**
     * Extract the video ID from a YouTube URL.
     *
     * @param string $url The YouTube URL.
     * 
     * @return string|null The extracted video ID, or null if not found.
     */
    public function getVideoIdFromUrl($url)
    {
        preg_match('/(?:watch\?v=|embed\/)([a-zA-Z0-9_-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
