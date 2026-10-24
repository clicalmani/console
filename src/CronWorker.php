<?php

declare(strict_types=1);

namespace Clicalmani\Console;

/**
 * Décrit un worker à planifier dans la crontab.
 */
final class CronWorker
{
    public function __construct(
        /** Identifiant unique (a-z, 0-9, _ et -). Sert de marqueur dans la crontab. */
        public readonly string $name,
        /** Chemin absolu du script PHP à exécuter. */
        public readonly string $script,
        /** Expression cron. Par défaut : toutes les minutes. */
        public readonly string $schedule = '* * * * *',
        /** Binaire PHP (CLI). Par défaut : PHP_BINARY. */
        public readonly ?string $phpBinary = null,
        /** Fichier de lock à nettoyer. Par défaut : <tmp>/<name>.lock */
        public readonly ?string $lockFile = null,
        /** Redirection de sortie. */
        public readonly string $redirect = '> /dev/null 2>&1',
    ) {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new \InvalidArgumentException(sprintf('Nom de worker invalide : "%s".', $name));
        }

        if (preg_match('/[\r\n]/', $schedule) || count(preg_split('/\s+/', trim($schedule))) < 5) {
            throw new \InvalidArgumentException(sprintf('Expression cron invalide : "%s".', $schedule));
        }
    }

    /** Marqueur ajouté en fin de ligne pour identifier le worker sans ambiguïté. */
    public function marker(): string
    {
        return '# worker:' . $this->name;
    }

    public function lockFilePath(): string
    {
        return $this->lockFile ?? sys_get_temp_dir() . '/' . $this->name . '.lock';
    }

    public function toCronLine(array $options = []): string
    {
        $optionsString = "";

        foreach ($options as $key => $value) {
            if (is_array($value)) $value = join(',', $value);
            $optionsString .= " --$key=" . escapeshellarg($value);
        }
        
        return sprintf(
            '%s %s %s %s %s %s',
            trim($this->schedule),
            escapeshellarg($this->phpBinary ?? PHP_BINARY),
            escapeshellarg($this->script),
            $optionsString,
            $this->redirect,
            $this->marker()
        );
    }
}