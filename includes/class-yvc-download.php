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
 * Class YVC_FastDownload
 *
 * Handles video search using FastDownload API and yt-dlp.
 *
 * @category Core
 * @package  YouTube_Video_Checker
 * @author   Moche <ndegwamoche@gmail.com>
 * @license  MIT <https://opensource.org/licenses/MIT>
 * @link     https://github.com/youtube-video-checker
 */
class YVC_FastDownload
{
    /**
     * Search for a YouTube video using the FastDownload API.
     *
     * @param string $query The search query.
     * 
     * @return array The result or error message.
     */
    public function searchVideoUsingFastDownload($query)
    {
        $api_url = 'https://fastdownload.video/api/youtube/search?query=' . urlencode($query);
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return [
                'error' =>
                'Error fetching data from FastDownload API: ' . $response->get_error_message()
            ];
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['results'][0])) {
            $utils = new YVC_Utils();
            return [
                'id'    => $utils->getVideoIdFromUrl($data['results'][0]['link']),
                'title' => $data['results'][0]['title'],
                'link'  => $data['results'][0]['link']
            ];
        }

        return [
            'error' => 'No results found for query: ' . $query
        ];
    }

    /**
     * Search for a YouTube video using yt-dlp.
     *
     * @param string $query The search query.
     * 
     * @return array The result or error message.
     */
    public function searchVideoUsingYtDlp($query)
    {
        $command = escapeshellcmd("yt-dlp --quiet --print-json ytsearch:'$query' 2>&1");
        $output  = shell_exec($command);

        if ($output) {
            $result = json_decode($output, true);
            if (isset($result['id'])) {
                return $result;  // Return video details like ID
            }

            return [
                'error' => 'yt-dlp Error: ' . $output  // Return the error output from yt-dlp
            ];
        }

        return [
            'error' => 'yt-dlp failed to search or returned no result for: ' . $query
        ];
    }
}
