<?php

class YVC_Utils
{
    public function check_if_youtube_video_exist($video_id)
    {
        $cache_key = 'youtube_video_exists_' . $video_id;
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result === '1';
        }

        $url = "https://www.youtube.com/watch?v=" . $video_id;
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if ($content === false || strpos($content, "Video unavailable") !== false) {
            set_transient($cache_key, '0', 12 * HOUR_IN_SECONDS);
            return false;
        }

        set_transient($cache_key, '1', 12 * HOUR_IN_SECONDS);
        return true;
    }

    public function get_video_id_from_url($url)
    {
        // Extract the video ID from the YouTube URL
        preg_match('/(?:watch\?v=|embed\/)([a-zA-Z0-9_-]+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
