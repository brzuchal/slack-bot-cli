<?php

declare(strict_types=1);

namespace SlackBot\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SlackBot\Slack\SlackClient;

class SendMessageCommand extends Command
{
    protected static $defaultName = 'slack:send-message';
    protected static $defaultDescription = 'Send a message to a Slack channel';

    public function __construct(private SlackClient $slackClient)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('channel', InputArgument::REQUIRED, 'Slack channel ID or name')
            ->addArgument('message', InputArgument::REQUIRED, 'Message text to send')
            ->addOption('thread-ts', null, InputOption::VALUE_OPTIONAL, 'Thread timestamp for reply')
            ->addOption('blocks', null, InputOption::VALUE_OPTIONAL, 'JSON blocks file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $channel = $input->getArgument('channel');
            $message = $input->getArgument('message');
            $threadTs = $input->getOption('thread-ts');
            $blocksFile = $input->getOption('blocks');

            $payload = [
                'channel' => $channel,
                'text' => $message,
            ];

            if ($threadTs) {
                $payload['thread_ts'] = $threadTs;
            }

            if ($blocksFile && file_exists($blocksFile)) {
                $blocks = json_decode(file_get_contents($blocksFile), true);
                $payload['blocks'] = $blocks;
            }

            $response = $this->slackClient->sendMessage($channel, $message, $payload['blocks'] ?? []);

            if ($response['ok'] ?? false) {
                $output->writeln('<info>✓ Message sent successfully</info>');
                $output->writeln("Channel: {$response['channel']}");
                $output->writeln("Timestamp: {$response['ts']}");
                return Command::SUCCESS;
            } else {
                $output->writeln('<error>✗ Failed to send message: ' . ($response['error'] ?? 'Unknown error') . '</error>');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}