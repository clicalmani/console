<?php
namespace Clicalmani\Console\Commands\Messenger;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Scheduler;

/**
 * Kill
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'messenger:kill',
    description: 'Stop the consumer',
    hidden: false
)]
class KillCommand extends Command
{
    protected static $defaultDescription = 'Stop the consumer';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // 1. Retrieve the current crontab
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        $taskScript = "worker.php"; // The keyword used to identify the line

        if (strpos($currentCrontab, $taskScript) === false) {
            $output->writeln('<comment>The worker is not configured in the crontab.</comment>');
        } else {
            // 2. Filter out the worker line
            $lines = explode(PHP_EOL, trim($currentCrontab));
            $newLines = array_filter($lines, function($line) use ($taskScript) {
                return strpos($line, $taskScript) === false;
            });

            // 3. Reinstall the cleaned crontab
            $tmpFile = tempnam(sys_get_temp_dir(), 'cron_clean');
            file_put_contents($tmpFile, implode(PHP_EOL, $newLines) . PHP_EOL);
            exec("crontab $tmpFile");
            unlink($tmpFile);

            $output->writeln('<info>Line removed from crontab.</info>');
        }

        // 4. NOW, kill any remaining active processes
        exec("pkill -f 'worker.php'");
        $output->writeln('<info>PHP processes killed.</info>');

        return Command::SUCCESS;
    }
}