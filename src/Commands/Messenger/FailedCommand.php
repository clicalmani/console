<?php
namespace Clicalmani\Console\Commands\Messenger;

use Clicalmani\Console\Commands\Command;

class FailedCommand extends Command
{
    protected string $failedTable;

    public function __construct(protected $rootPath)
    {
        if (file_exists(config_path('messenger.php'))) {
            $config = require config_path('messenger.php');
            $this->failedTable = $config['failed_table'] ?? '';

            if (!$this->failedTable) {
                throw new \RuntimeException("The 'messenger.failed_table' configuration is missing. Please ensure it is set in your configuration files.");
            }
        } else {
            throw new \RuntimeException("The 'messenger.php' configuration file is missing. Please ensure it exists in your config directory.");
        }

        parent::__construct();
    }
}