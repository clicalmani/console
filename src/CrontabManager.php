<?php

declare(strict_types=1);

namespace Clicalmani\Console;

/**
 * Installe / désinstalle des workers dans la crontab de l'utilisateur courant.
 */
final class CrontabManager
{
    /**
     * @return bool true si le worker a été installé, false s'il l'était déjà
     * @throws \RuntimeException en cas d'échec de crontab
     */
    public function install(CronWorker $worker, array $options = []): bool
    {
        if (!is_file($worker->script)) {
            throw new \RuntimeException(sprintf('Script introuvable : %s', $worker->script));
        }

        $this->removeLockFile($worker);

        $lines = $this->readLines();

        if ($this->findIndex($lines, $worker) !== null) {
            return false;
        }

        $lines[] = $worker->toCronLine($options);
        $this->writeLines($lines);

        return true;
    }

    /**
     * @return bool true si le worker a été retiré, false s'il n'était pas présent
     */
    public function uninstall(CronWorker $worker): bool
    {
        $this->removeLockFile($worker);

        $lines = $this->readLines();
        $index = $this->findIndex($lines, $worker);

        if ($index === null) {
            return false;
        }

        unset($lines[$index]);
        $this->writeLines(array_values($lines));

        return true;
    }

    public function isInstalled(CronWorker $worker): bool
    {
        return $this->findIndex($this->readLines(), $worker) !== null;
    }

    /**
     * Installe plusieurs workers avec une seule écriture de crontab.
     *
     * @param CronWorker[] $workers
     * @return array<string, bool> nom du worker => true (installé) / false (déjà présent)
     */
    public function installAll(array $workers): array
    {
        $lines = $this->readLines();
        $result = [];
        $changed = false;

        foreach ($workers as $worker) {
            if (!is_file($worker->script)) {
                throw new \RuntimeException(sprintf('Script introuvable : %s', $worker->script));
            }

            if ($this->findIndex($lines, $worker) !== null) {
                $result[$worker->name] = false;
                continue;
            }

            $lines[] = $worker->toCronLine();
            $result[$worker->name] = $changed = true;
        }

        if ($changed) {
            $this->writeLines($lines);
        }

        return $result;
    }

    /**
     * Arrête proprement les processus du worker : SIGTERM, attente, puis SIGKILL
     * pour ceux qui n'ont pas terminé dans le délai imparti.
     *
     * @return array{terminated:int[], killed:int[]}
     */
    public function stop(CronWorker $worker, int $timeoutSeconds = 10): array
    {
        $initial = $this->findPids($worker);
        $pids = $initial;

        if ($pids !== []) {
            $this->signal('TERM', $pids);

            $deadline = microtime(true) + $timeoutSeconds;
            while ($pids !== [] && microtime(true) < $deadline) {
                usleep(200_000);
                $pids = $this->findPids($worker);
            }
        }

        $killed = [];
        if ($pids !== []) {
            $this->signal('KILL', $pids);
            $killed = $pids;
        }

        $this->removeLockFile($worker);

        return [
            'terminated' => array_values(array_diff($initial, $killed)),
            'killed'     => $killed,
        ];
    }

    /** @return int[] PIDs des processus dont la ligne de commande contient le script */
    public function findPids(CronWorker $worker): array
    {
        // pgrep -f interprète le motif comme une regex : on échappe le chemin
        $pattern = preg_replace('/[.^$*+?()\[\]{}|\\\\]/', '\\\\$0', $worker->script);

        [$code, $stdout, $stderr] = $this->run(['pgrep', '-f', '--', $pattern]);

        if ($code === 1) {
            return []; // aucun processus
        }

        if ($code !== 0) {
            throw new \RuntimeException('pgrep a échoué : ' . trim($stderr));
        }

        $self = getmypid();

        return array_values(array_filter(
            array_map('intval', preg_split('/\s+/', trim($stdout)) ?: []),
            static fn (int $pid): bool => $pid > 0 && $pid !== $self
        ));
    }

    // ------------------------------------------------------------------

    /** @param int[] $pids */
    private function signal(string $signal, array $pids): void
    {
        // Code de retour ignoré : un processus déjà terminé n'est pas une erreur
        $this->run(array_merge(['kill', '-' . $signal], array_map('strval', $pids)));
    }

    private function removeLockFile(CronWorker $worker): void
    {
        $lock = $worker->lockFilePath();

        if (is_file($lock)) {
            @unlink($lock);
        }
    }

    /** @param string[] $lines */
    private function findIndex(array $lines, CronWorker $worker): ?int
    {
        $marker = $worker->marker();

        foreach ($lines as $i => $line) {
            if (str_ends_with(rtrim($line), $marker)) {
                return $i;
            }
        }

        return null;
    }

    /** @return string[] */
    private function readLines(): array
    {
        [$code, $stdout, $stderr] = $this->run(['crontab', '-l']);

        if ($code !== 0) {
            // "no crontab for user" : crontab vide, pas une erreur
            if (stripos($stderr, 'no crontab') !== false) {
                return [];
            }

            throw new \RuntimeException('Impossible de lire la crontab : ' . trim($stderr));
        }

        return array_values(array_filter(
            preg_split('/\R/', $stdout) ?: [],
            static fn (string $l): bool => $l !== ''
        ));
    }

    /** @param string[] $lines */
    private function writeLines(array $lines): void
    {
        $content = $lines === [] ? '' : implode(PHP_EOL, $lines) . PHP_EOL;

        // "crontab -" lit la crontab sur stdin : pas de fichier temporaire nécessaire
        [$code, , $stderr] = $this->run(['crontab', '-'], $content);

        if ($code !== 0) {
            throw new \RuntimeException('Impossible d\'écrire la crontab : ' . trim($stderr));
        }
    }

    /**
     * @param string[] $command
     * @return array{0:int, 1:string, 2:string} [code, stdout, stderr]
     */
    private function run(array $command, ?string $stdin = null): array
    {
        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($process)) {
            throw new \RuntimeException('Impossible de lancer : ' . implode(' ', $command));
        }

        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }
        fclose($pipes[0]);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stdout, $stderr];
    }
}