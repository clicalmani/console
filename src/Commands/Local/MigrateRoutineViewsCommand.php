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
    name: 'migrate:views',
    description: 'Migrate routine views',
    hidden: false
)]
class MigrateRoutineViewsCommand extends Command
{
    private $views_path;

    public function __construct(private $root_path)
    {
        $this->views_path = $this->root_path . '/database/routines/views';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            $views_dir = new \RecursiveDirectoryIterator($this->views_path);

            $output->writeln('Migration routine views ...');

            foreach (new \RecursiveIteratorIterator($views_dir) as $file) { 
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
        DB::getInstance()->query("DROP VIEW IF EXISTS `$name`");
    }
}
