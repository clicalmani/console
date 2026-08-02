<?php
namespace Clicalmani\Console\Commands\Messenger;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Scheduler;

/**
 * Consume
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'messenger:consume',
    description: 'Start the server',
    hidden: false
)]
class ConsumeCommand extends Command
{
    protected static $defaultDescription = 'Start the server';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    /**
     * Define available options
     */
    protected function configure(): void
    {
        parent::configure();
        
        $this->addOption('transport', 't', InputOption::VALUE_REQUIRED, 'The transports to be used', 'elegant');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // Before restarting the crontab, clean up old lock files
        $lockFile = sys_get_temp_dir() . '/tonka_messenger_worker.lock';

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Safely retrieve the current crontab
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        
        // Define the line to be added
        $phpBinary   = PHP_BINARY; // Dynamically retrieves the current PHP binary
        $taskScript  = __DIR__ . "/worker.php";

        $transport = $input->getOption('transport');

        $optionsString = "";
        $optionsString .= " --transport=" . escapeshellarg($transport);
        $redirect = '> /dev/null 2>&1';

        $commandLine = "* * * * * $phpBinary $taskScript $optionsString $redirect";

        // --- IMPROVED DUPLICATE MANAGEMENT ---
        // If the command is re-run with different options, the old line must be removed;
        // otherwise, strpos would prevent the new line from being added.
        if (strpos($currentCrontab, $taskScript) !== false) {
            // Remove the old line containing worker.php before adding the new one
            $currentCrontab = preg_replace('/^[^\r\n]*' . preg_quote($taskScript, '/') . '[^\r\n]*\r?\n?/m', '', $currentCrontab);
            $output->writeln('<comment>Updating consumer configuration...</comment>');
        }

        // Create a secure temporary file
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $currentCrontab . $commandLine . PHP_EOL);

        // Install the new crontab
        exec("crontab $tmpFile", $out, $resultCode);
        unlink($tmpFile); // Cleanup

        if ($resultCode === 0) {
            $output->writeln('<info>Consumer successfully installed.</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>An error occurred while installing the crontab.</error>');
        return Command::FAILURE;
    }
}