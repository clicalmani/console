<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Database\DB;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Start routine functions migration
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'migrate:procedures',
    description: 'Migrate routine procedures',
    hidden: false
)]
class MigrateStoredProceduresCommand extends Command
{
    private $procedures_path;

    public function __construct(protected $root_path)
    {
        $this->procedures_path = $this->root_path . '/database/routines/procedures';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $procedures_dir = new \RecursiveDirectoryIterator($this->procedures_path);

            $output->writeln('Migration stored procedures ...');

            foreach (new \RecursiveIteratorIterator($procedures_dir) as $file) { 
                $pathname = $file->getPathname();
                $filename = $file->getFileName();

                if($file->isFile()) {
                    if(is_readable($pathname)) {
                        $function = require $pathname;
                        $output->writeln("Creating $filename ...");
                        $this->drop($filename);
                        
                        if (false == $this->create($function, $output)) {
                            $output->write('Failure');
                        } else $output->writeln('success');
                    }
                }
            }

            return Command::SUCCESS;

        } catch(\PDOException $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    private function create(?callable $function, OutputInterface $output) : bool
    {
        try {
            $sql = str_replace('%DB_TABLE_PREFIX%', $_ENV['DB_TABLE_PREFIX'], $function());
            DB::getInstance()->query($sql);
            return true;
        } catch(\PDOException $e) {
            $output->writeln($e->getMessage());
            return false;
        }
    }

    private function drop(string $name) : void
    {
        DB::getInstance()->query("DROP procedure IF EXISTS `$name`");
    }
}
