<?php

class YVC_FastDownload
{
    public function search_video_using_fastdownload($query)
    {
        // Prepare the API URL
        $api_url = 'https://fastdownload.video/api/youtube/search?query=' . urlencode($query);

        // Use wp_remote_get to fetch data from the API
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return [
                'error' => 'Error fetching data from FastDownload API: ' . $response->get_error_message()
            ];
        }

        // Decode the JSON response
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['results']) && count($data['results']) > 0) {

            $utils = new YVC_Utils();

            // Return the first result
            return [
                'id' => $utils->get_video_id_from_url($data['results'][0]['link']),
                'title' => $data['results'][0]['title'],
                'link' => $data['results'][0]['link']
            ];
        }

        return [
            'error' => 'No results found for query: ' . $query
        ];
    }

    public function search_video_using_yt_dlp($query)
    {
        // Run yt-dlp command to search for videos
        $command = escapeshellcmd("yt-dlp --quiet --print-json ytsearch:'$query' 2>&1");  // Capture both output and error
        $output = shell_exec($command);

        if ($output) {
            // Attempt to decode the JSON result
            $result = json_decode($output, true);
            if (isset($result['id'])) {
                return $result;  // Return video details like ID
            } else {
                // If JSON decode failed, return the error message from yt-dlp
                return [
                    'error' => 'yt-dlp Error: ' . $output  // Return the full error output from yt-dlp
                ];
            }
        } else {
            // If there's no output, handle the case where yt-dlp failed silently
            return [
                'error' => 'yt-dlp failed to search or returned no result for the query: ' . $query
            ];
        }

        return false;
    }
}
