<?php

namespace Slack;

class SlackClient {
    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    public function sendMessage($channel, $message) {
        $url = 'https://slack.com/api/chat.postMessage';
        $data = [
            'channel' => $channel,
            'text' => $message,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n" . "Authorization: Bearer " . $this->token,
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return json_decode($result);
    }
}

// Usage Example:
//$client = new SlackClient('your-slack-token');
//$client->sendMessage('#general', 'Hello Slack!');
