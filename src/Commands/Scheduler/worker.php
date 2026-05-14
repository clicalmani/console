<?php

use Symfony\Component\Scheduler\Scheduler;
use Clicalmani\Foundation\Scheduler\TaskDiscovery;

$lockFile = sys_get_temp_dir() . '/tonka_scheduler_worker.lock';
$fp = fopen($lockFile, 'w+');

if (!flock($fp, LOCK_EX | LOCK_NB)) {
    // Si le fichier est déjà verrouillé, on quitte immédiatement
    logger()->error("Un worker est déjà en cours d'exécution.\n");
}

$root = dirname(__DIR__, 6);
include_once $root . '/vendor/autoload.php';

$app = require_once $root . '/bootstrap/app.php';
$app->config->set('database', require_once config_path('/database.php'));
$app->boot();

$schedule          = container()->get('scheduler.main');
$handlersDiscovery = container()->get('scheduler.handlers');

// Le Scheduler va vérifier les triggers et dispatcher les messages sur le bus
$scheduler = new Scheduler($handlersDiscovery->discover(), [$schedule]);

// Boucle infinie qui surveille le temps
$scheduler->run();