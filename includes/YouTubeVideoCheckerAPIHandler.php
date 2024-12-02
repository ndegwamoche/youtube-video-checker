<?php
class YouTubeVideoCheckerAPIHandler
{
    private $client_id;
    private $client_secret;
    private $refresh_token;
    private $access_token;
    private $token_expires_in;

    public function __construct()
    {
        $this->client_id = '862693741312-9oh0qqrma3tnd61pgu16fetc8ek83i57.apps.googleusercontent.com';
        $this->client_secret = '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';
        $this->refresh_token = '1//048XimX2lTnAVCgYIARAAGAQSNwF-L9IrOlgoBZ5M6NHC7WvUvpyRQc8R3NAgAJ22Xs7s6o4KRuvMtCC-72LPIt_qkBzT_nKetvo';

        $this->fetch_access_token();
    }

    private function fetch_access_token()
    {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', [
            'body' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
                'grant_type' => 'refresh_token',
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('Error fetching access token: ' . $response->get_error_message());
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['access_token'])) {
            $this->access_token = $data['access_token'];
            $this->token_expires_in = time() + $data['expires_in'];
        } else {
            error_log('Failed to retrieve access token');
        }
    }

    public function get_access_token()
    {
        if (time() >= $this->token_expires_in) {
            $this->fetch_access_token();
        }
        return $this->access_token;
    }

    public function search_youtube($query)
    {
        $access_token = $this->get_access_token();
        if (!$access_token) {
            return null;
        }

        $response = wp_remote_get('https://www.googleapis.com/youtube/v3/search', [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
            ],
            'body' => [
                'part' => 'snippet',
                'maxResults' => 2,
                'q' => $query,
                'type' => 'video'
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('Error fetching YouTube data: ' . $response->get_error_message());
            return null;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
