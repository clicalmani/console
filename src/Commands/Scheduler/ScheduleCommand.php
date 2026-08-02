<?php
namespace Clicalmani\Console\Commands\Scheduler;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Scheduler;

/**
 * Schedule
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'schedule:run',
    description: 'Start the server',
    hidden: false
)]
class ScheduleCommand extends Command
{
    protected static $defaultDescription = 'Start the server';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // Before running the crontab again, clean up old lock files
        $lockFile = sys_get_temp_dir() . '/tonka_scheduler_worker.lock';

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Securely retrieve the current crontab
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        
        // Define the line to be added
        $phpBinary   = PHP_BINARY; // Dynamically retrieves the current PHP binary path
        $taskScript  = __DIR__ . "/worker.php";
        $redirect = '> /dev/null 2>&1';

        $commandLine = "* * * * * $phpBinary $taskScript $redirect";

        // Check for existence to prevent duplicates
        if (strpos($currentCrontab, $taskScript) !== false) {
            $output->writeln('<info>The scheduler is already configured in the crontab.</info>');
            return Command::SUCCESS;
        }

        // Create a secure temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $currentCrontab . $commandLine . PHP_EOL);

        // Install the new crontab
        exec("crontab $tmpFile", $out, $resultCode);
        unlink($tmpFile); // Clean up

        if ($resultCode === 0) {
            $output->writeln('<info>Scheduler successfully installed.</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Error installing the crontab.</error>');
        return Command::FAILURE;
    }
}