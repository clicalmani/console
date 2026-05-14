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
 * 
 * @package Clicalmani\Console
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
        // Avant de relancer la crontab, on nettoie les anciens fichiers de lock
        $lockFile = sys_get_temp_dir() . '/tonka_scheduler_worker.lock';

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Récupération sécurisée du crontab actuel
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        
        // Définition de la ligne à ajouter
        $phpBinary   = PHP_BINARY; // Récupère dynamiquement le binaire PHP actuel
        $taskScript  = __DIR__ . "/worker.php";
        $redirect = '> /dev/null 2>&1';

        $commandLine = "* * * * * $phpBinary $taskScript $redirect";

        // Vérification de l'existence pour éviter les doublons
        if (strpos($currentCrontab, $taskScript) !== false) {
            $output->writeln('<info>Le scheduler est déjà configuré dans la crontab.</info>');
            return Command::SUCCESS;
        }

        // Création d'un fichier temporaire sécurisé
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $currentCrontab . $commandLine . PHP_EOL);

        // Installation de la nouvelle crontab
        exec("crontab $tmpFile", $out, $resultCode);
        unlink($tmpFile); // Nettoyage

        if ($resultCode === 0) {
            $output->writeln('<info>Scheduler installé avec succès.</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Erreur lors de l\'installation de la crontab.</error>');
        return Command::FAILURE;
    }
}
