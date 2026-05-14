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
 * 
 * @package Clicalmani\Console
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
     * Définition des options disponibles
     */
    protected function configure(): void
    {
        parent::configure();
        
        $this->addOption('transport', 't', InputOption::VALUE_REQUIRED, 'The transport to be used', 'elegant');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // Avant de relancer la crontab, on nettoie les anciens fichiers de lock
        $lockFile = sys_get_temp_dir() . '/tonka_messenger_worker.lock';

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Récupération sécurisée du crontab actuel
        $currentCrontab = shell_exec('crontab -l 2>/dev/null') ?: '';
        
        // Définition de la ligne à ajouter
        $phpBinary   = PHP_BINARY; // Récupère dynamiquement le binaire PHP actuel
        $taskScript  = __DIR__ . "/worker.php";

        $transport = $input->getOption('transport');

        $optionsString = "";
        $optionsString .= " --transport=" . escapeshellarg($transport);
        $redirect = '> /dev/null 2>&1';

        $commandLine = "* * * * * $phpBinary $taskScript $optionsString $redirect";

        // --- GESTION DES DOUBLONS AMÉLIORÉE ---
        // Si on relance la commande avec des options différentes, il faut supprimer l'ancienne ligne
        // sinon le strpos bloquera l'ajout.
        if (strpos($currentCrontab, $taskScript) !== false) {
            // On supprime l'ancienne ligne contenant worker.php avant d'ajouter la nouvelle
            $currentCrontab = preg_replace('/^[^\r\n]*' . preg_quote($taskScript, '/') . '[^\r\n]*\r?\n?/m', '', $currentCrontab);
            $output->writeln('<comment>Mise à jour de la configuration du consumer...</comment>');
        }

        // Création d'un fichier temporaire sécurisé
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');
        file_put_contents($tmpFile, $currentCrontab . $commandLine . PHP_EOL);

        // Installation de la nouvelle crontab
        exec("crontab $tmpFile", $out, $resultCode);
        unlink($tmpFile); // Nettoyage

        if ($resultCode === 0) {
            $output->writeln('<info>Consumer installé avec succès.</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Erreur lors de l\'installation de la crontab.</error>');
        return Command::FAILURE;
    }
}
