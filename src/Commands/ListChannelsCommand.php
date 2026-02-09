<?php

declare(strict_types=1);

namespace SlackBot\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use SlackBot\Slack\SlackClient;

class ListChannelsCommand extends Command
{
    protected static $defaultName = 'slack:list-channels';
    protected static $defaultDescription = 'List all available Slack channels';

    public function __construct(private SlackClient $slackClient)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $channels = $this->slackClient->listChannels();

            if (empty($channels)) {
                $output->writeln('<info>No channels found</info>');
                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table->setHeaders(['Channel ID', 'Name', 'Members', 'Topic']);

            foreach ($channels as $channel) {
                $table->addRow([
                    $channel['id'],
                    $channel['name'],
                    $channel['num_members'] ?? 0,
                    $channel['topic']['value'] ?? '-',
                ]);
            }

            $table->render();
            $output->writeln("<info>\nTotal channels: " . count($channels) . "</info>");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}