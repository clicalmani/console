<?php
namespace Clicalmani\Console\Commands\Scheduler;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Scheduler;

/**
 * Kill
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'scheduler:kill',
    description: 'Stop the scheduler',
    hidden: false
)]
class KillCommand extends Command
{
    protected static $defaultDescription = 'Stop the scheduler';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // 1. Récupérer la crontab actuelle
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        $taskScript = "worker.php"; // Le mot-clé pour identifier la ligne

        if (strpos($currentCrontab, $taskScript) === false) {
            $output->writeln('<comment>Le worker n\'est pas configuré dans la crontab.</comment>');
        } else {
            // 2. Filtrer pour ENLEVER la ligne du worker
            $lines = explode(PHP_EOL, trim($currentCrontab));
            $newLines = array_filter($lines, function($line) use ($taskScript) {
                return strpos($line, $taskScript) === false;
            });

            // 3. Réinstaller la crontab nettoyée
            $tmpFile = tempnam(sys_get_temp_dir(), 'cron_clean');
            file_put_contents($tmpFile, implode(PHP_EOL, $newLines) . PHP_EOL);
            exec("crontab $tmpFile");
            unlink($tmpFile);

            $output->writeln('<info>Ligne supprimée de la crontab.</info>');
        }

        // 4. MAINTENANT, on tue les processus restants
        exec("pkill -f 'worker.php'");
        $output->writeln('<info>Processus PHP tués.</info>');

        return Command::SUCCESS;
    }
}
