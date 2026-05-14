<?php

$lockFile = sys_get_temp_dir() . '/tonka_messenger_worker.lock';
$fp = fopen($lockFile, 'w+');

if (!flock($fp, LOCK_EX | LOCK_NB)) {
    // Si le fichier est déjà verrouillé, on quitte immédiatement
    logger()->error("Un worker est déjà en cours d'exécution.\n");
}

 $options = getopt("", ["transport:"]);
 $transport = $options['transport'] !== 'elegant' ? $options['transport']: 'messenger.transport.elegant';

use Symfony\Component\Messenger\Worker;
use Symfony\Component\Messenger\EventListener\StopWorkerOnSigtermSigintListener;
use Symfony\Component\EventDispatcher\EventDispatcher;

$root = dirname(__DIR__, 6);
include_once $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->config->set('database', require_once config_path('/database.php'));
$app->boot();

// 1. Récupérer les services depuis le container
$transport = container()->get($transport);
$bus       = container()->get('messenger');
$receivers = ['elegant' => $transport];

// 2. Créer le Worker
// On passe le bus et le dictionnaire de receivers
$worker = new Worker($receivers, $bus, new EventDispatcher());

// 3. Lancer la boucle infinie
logger()->info("Worker en attente de messages...");
$worker->run([], function () {
    // Cette fonction est appelée après chaque message traité
    if (file_exists(root_path('temp/restart_worker.txt'))) {
        return false; // Arrête le worker proprement
    }
    return true;
});