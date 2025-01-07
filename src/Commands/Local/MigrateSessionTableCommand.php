<?php
namespace Clicalmani\Console\Commands\Local;

use App\Providers\SessionServiceProvider;
use Clicalmani\Console\Commands\Command;
use Clicalmani\Database\DB;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:session-table',
    description: 'Migrate session table.',
    hidden: false
)]
class MigrateSessionTableCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $session_table = env('DB_TABLE_PREFIX', '') . SessionServiceProvider::getTable();
            $sql = "CREATE TABLE IF NOT EXISTS $session_table (`sess_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, " . 
                   "`id` VARCHAR(32) NOT NULL, `access` VARCHAR(100) NOT NULL, `data` LONGTEXT, UNIQUE KEY `id_UNIQUE` (`id`))";
            DB::getPdo()->query($sql);

            $output->writeln("Session table $session_table created successfully.");

            return Command::SUCCESS;
        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function configure() : void
    {
        $this->setHelp('Migrate session table');
    }
}
