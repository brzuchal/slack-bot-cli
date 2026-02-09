<?php

declare(strict_types=1);

namespace SlackBot;

use Symfony\Component\Console\Application as ConsoleApplication;
use SlackBot\Commands\SendMessageCommand;
use SlackBot\Commands\ListChannelsCommand;
use SlackBot\Commands\PostWebhookCommand;
use SlackBot\Slack\SlackClient;
use Dotenv\Dotenv;

class Application extends ConsoleApplication
{
    private SlackClient $slackClient;

    public function __construct()
    {
        parent::__construct('Slack Bot CLI', '1.0.0');
        
        $this->loadConfiguration();
        $this->slackClient = new SlackClient($this->getSlackConfig());
        $this->registerCommands();
    }

    private function loadConfiguration(): void
    {
        $configPath = __DIR__ . '/../config/slack-config.json';
        
        if (!file_exists($configPath)) {
            throw new \RuntimeException("Configuration file not found: {$configPath}");
        }

        $config = json_decode(file_get_contents($configPath), true);
        
        if (!isset($config['slack_token']) || !isset($config['slack_signing_secret'])) {
            throw new \RuntimeException('Missing required Slack configuration keys');
        }
    }

    private function getSlackConfig(): array
    {
        $configPath = __DIR__ . '/../config/slack-config.json';
        return json_decode(file_get_contents($configPath), true);
    }

    private function registerCommands(): void
    {
        $this->add(new SendMessageCommand($this->slackClient));
        $this->add(new ListChannelsCommand($this->slackClient));
        $this->add(new PostWebhookCommand());
    }
}