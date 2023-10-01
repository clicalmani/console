<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Database\DB;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
    name: 'migrate:functions',
    description: 'Migrate routine functions',
    hidden: false
)]
class MigrateRoutineFunctionsCommand extends Command
{
    private $functions_path;

    public function __construct(private $root_path)
    {
        $this->functions_path = $this->root_path . '/database/routines/functions';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $functions_dir = new \RecursiveDirectoryIterator($this->functions_path);

            $output->writeln('Migration routine functions ...');

            foreach (new \RecursiveIteratorIterator($functions_dir) as $file) { 
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
        DB::getInstance()->query("DROP function IF EXISTS `$name`");
    }
}
