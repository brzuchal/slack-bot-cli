<?php

declare(strict_types=1);

namespace SlackBot\Tests\Slack;

use PHPUnit\Framework\TestCase;
use SlackBot\Slack\SlackClient;

class SlackClientTest extends TestCase
{
    private SlackClient $slackClient;

    protected function setUp(): void
    {
        $config = [
            'slack_token' => 'xoxb-test-token',
            'slack_signing_secret' => 'test-secret',
            'slack_app_id' => 'test-app-id',
            'webhook_url' => 'https://hooks.slack.com/test',
        ];

        $this->slackClient = new SlackClient($config);
    }

    public function testVerifyWebhookSignatureValid(): void
    {
        $timestamp = (string)time();
        $body = '{"type":"url_verification"}';
        $baseString = "v0:{$timestamp}:{$body}";
        $signature = 'v0=' . hash_hmac('sha256', $baseString, 'test-secret');

        $result = $this->slackClient->verifyWebhookSignature($timestamp, $signature, $body);

        $this->assertTrue($result);
    }

    public function testVerifyWebhookSignatureInvalid(): void
    {
        $timestamp = (string)time();
        $body = '{"type":"url_verification"}';
        $signature = 'v0=invalid-signature';

        $result = $this->slackClient->verifyWebhookSignature($timestamp, $signature, $body);

        $this->assertFalse($result);
    }

    public function testGetToken(): void
    {
        $this->assertEquals('xoxb-test-token', $this->slackClient->getToken());
    }

    public function testGetSigningSecret(): void
    {
        $this->assertEquals('test-secret', $this->slackClient->getSigningSecret());
    }
}