<?php
namespace Clicalmani\Console\Commands\Messenger;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Core\Support\Facades\DB;
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
    name: 'messenger:migrate',
    description: 'Migrate messages and failed tables',
    hidden: false
)]
class MigrateCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $connections = config('database.connections');
        $default_connection = config('database.default');
        $message_table = config('messenger.message_table');
        $failed_table = config('messenger.failed_table');

        if ($connections && $default_connection && $message_table && $failed_table) {
            $collation = $connections[$default_connection]['collation'];
            $charset = $connections[$default_connection]['charset'];

            try {
                $message_table = env('DB_TABLE_PREFIX', '') . $message_table;
                $messageSQL = <<<SQL
                CREATE TABLE IF NOT EXISTS $message_table (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `body` LONGTEXT NOT NULL,
                    `headers` LONGTEXT NOT NULL,
                    `queue_name` VARCHAR(191) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `available_at` TIMESTAMP NOT NULL,
                    `delivered_at` TIMESTAMP NULL,
                    `completed_at` TIMESTAMP NULL
                ) DEFAULT COLLATE = $collation DEFAULT CHARACTER SET = $charset;
                SQL;
                $failedSQL = <<<SQL
                CREATE TABLE IF NOT EXISTS $failed_table (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `body` LONGTEXT NOT NULL,
                    `headers` LONGTEXT NOT NULL,
                    `queue_name` VARCHAR(191) NOT NULL,
                    `exception_class` VARCHAR(255) NOT NULL,
                    `exception_message` TEXT NOT NULL,
                    `failed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) DEFAULT COLLATE = $collation DEFAULT CHARACTER SET = $charset;
                SQL;
                DB::getPdo()->exec($message_table . $failed_table);

                $output->writeln("<info>Migration executed successfully.</info>");

                return Command::SUCCESS;
            } catch (\PDOException $e) {
                $output->writeln('<error>Unable to migrate messenger tables.</error>');
                $output->writeln($e->getMessage());
            }
        }

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Migrate messages and failed tables');
    }
}