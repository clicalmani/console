<?php

namespace Clicalmani\Console\Commands\Messenger;

use Clicalmani\Core\Support\Facades\DB;
use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

#[AsCommand(
    name: 'messenger:failed:retry',
    description: 'Retry one or all messages from quarantine'
)]
class FailedRetryCommand extends FailedCommand
{
    public function __construct(protected $rootPath)
    {
        parent::__construct($rootPath);
    }

    protected function configure(): void
    {
        parent::configure();
        
        $this->addArgument('id', InputArgument::OPTIONAL, 'Specific message ID to retry (leave empty to retry ALL)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $bus = container()->get('messenger');
        $serializer = new PhpSerializer(); // Uses Symfony's default serializer

        // Determine the list of messages to process
        if ($id) {
            $record = DB::table($this->failedTable)->where('id = ?', [$id])->first();
            if (!$record) {
                $output->writeln("<error>No message found with ID {$id}.</error>");
                return Command::FAILURE;
            }
            $messages = [$record];
        } else {
            $messages = DB::table($this->failedTable)->all();
            if (empty($messages)) {
                $output->writeln("<info>No messages to retry in quarantine.</info>");
                return Command::SUCCESS;
            }
        }

        $successCount = 0;

        foreach ($messages as $msg) {
            $output->write("Reconfiguring and dispatching message #{$msg->id}... ");

            try {
                // 1. Reconstruct the original envelope from entity's data
                $envelope = $serializer->decode([
                    'body'    => $msg->body,
                    'headers' => (array) json_decode($msg->headers, true)
                ]);

                // 2. Re-inject the message into the Bus to send it back through the normal cycle
                $bus->dispatch($envelope->getMessage());

                // 3. Delete the message from quarantine since it has been successfully re-sent
                DB::table($this->failedTable)->where('id = ?', [$msg->id])->delete();

                $output->writeln("<info>[OK]</info>");
                $successCount++;

            } catch (\Throwable $e) {
                $output->writeln("<error>[FAILED]</error>");
                $output->writeln("  └─ Unable to re-inject message: {$e->getMessage()}");
            }
        }

        $output->writeln("\n<info>Operation completed. {$successCount} message(s) successfully re-sent.</info>");
        return Command::SUCCESS;
    }
}