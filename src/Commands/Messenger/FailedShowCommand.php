<?php

namespace Clicalmani\Console\Commands\Messenger;

use App\Models\FailedMessage;
use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'messenger:failed:show',
    description: 'List or view details of quarantined messages'
)]
class FailedShowCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();
        
        $this->addArgument('id', InputArgument::OPTIONAL, 'ID of the specific failed message');
        $this->addOption('remove', 'r', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Remove the message specified by ID without retrying it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');

        // ── CASE 1: MESSAGE DELETION (messenger:failed:remove) ─────────────────
        if ($id && $input->getOption('remove')) {
            $message = FailedMessage::find($id);
            if (!$message) {
                $output->writeln("<error>No message found with ID {$id}.</error>");
                return Command::FAILURE;
            }
            $message->delete();
            $output->writeln("<info>Message #{$id} has been permanently removed from quarantine.</info>");
            return Command::SUCCESS;
        }

        // ── CASE 2: VIEW DETAILS IN VERBOSE MODE (messenger:failed:show {id} --verbose) ──
        if ($id) {
            $message = FailedMessage::find($id);
            if (!$message) {
                $output->writeln("<error>No message found with ID {$id}.</error>");
                return Command::FAILURE;
            }

            $output->writeln("<info>=== Quarantined Message #{$message->id} ===</info>");
            $output->writeln("<comment>Queue          :</comment> {$message->queue_name}");
            $output->writeln("<comment>Failed At      :</comment> {$message->failed_at}");
            $output->writeln("<comment>Exception Class:</comment> <error>{$message->exception_class}</error>");
            $output->writeln("<comment>Error Message  :</comment> <error>{$message->exception_message}</error>");

            // Verbose mode to display the raw structure of the message
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln("\n<comment>--- Headers ---</comment>");
                $output->writeln(print_r(json_decode($message->headers, true), true));
                $output->writeln("<comment>--- Body ---</comment>");
                $output->writeln($message->body);
            } else {
                $output->writeln("\n<comment>(Add '--verbose' or '-v' to view the raw payload content and headers)</comment>");
            }

            return Command::SUCCESS;
        }

        // ── CASE 3: LIST ALL MESSAGES (messenger:failed:show) ───────────────────
        $messages = FailedMessage::all();

        if (empty($messages)) {
            $output->writeln("<info>No messages in quarantine.</info>");
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Queue', 'Exception Class', 'Error Message (Excerpt)', 'Failed At']);

        foreach ($messages as $msg) {
            // Truncate the error message if it is too long for the table display
            $shortError = strlen($msg->exception_message) > 50 
                ? substr($msg->exception_message, 0, 47) . '...' 
                : $msg->exception_message;

            $table->addRow([
                $msg->id,
                $msg->queue_name,
                $msg->exception_class ?? 'Unknown',
                $shortError,
                $msg->failed_at->format('Y-m-d H:i')
            ]);
        }

        $table->render();
        return Command::SUCCESS;
    }
}