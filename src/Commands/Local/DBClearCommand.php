<?php
namespace Clicalmani\Console\Commands\Local;

use App\Models\User;
use Clicalmani\Console\Commands\Command;
use Clicalmani\Database\Factory\Index;
use Clicalmani\Database\Factory\Schema;
use Clicalmani\Flesco\Misc\RecursiveFilter;
use Clicalmani\Flesco\Models\Entity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Drop all database tables
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'db:clear',
    description: 'Clear database',
    hidden: false
)]
class DBClearCommand extends Command
{
    private $migrations_path;

    public function __construct(protected $root_path)
    {
        $this->migrations_path = $this->root_path . '/database/migrations';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Clearing database');

        $filename = $input->getArgument('name');

        $xdt = xdt();
        $xdt->setDirectory($this->migrations_path);
        $xdt->connect($filename, true, true);

        try {

            $droped = []; // Droped tables

            /**
             * Walkthrough migration
             * Drop entities
             */
            foreach ($xdt->getDocumentRootElement()->children() as $node) {
                $node = $xdt->parse($node);

                /**
                 * Drop dependences first
                 */

                $record = []; // Dependency record FLO

                // Find entities that dependent on the current one
                foreach ($xdt->select('dependences > entity[model="' . $node->attr('model') . '"]') as $dep) {
                    $dep = $xdt->parse($dep);
                    $record[] = $dep->parent()->parent()->attr('model');
                }
    
                rsort($record); // LFO
                
                // Migrate all dependences
                foreach ($record as $child) {
                    $dependency = new $child;
                    $entity = $dependency->getEntity();
                    $entity->setModel($dependency);

                    $table = $dependency->getTable();

                    if (!in_array($table, $droped)) $output->writeln($this->formatOutput('Droping table ' . $table));

                    $entity->drop();
                    if (!in_array($table, $droped)) $output->writeln($this->formatOutput('Success'));
                    $droped[] = $table;
                }

                // Migrate the entity
                $modelClass = $node->attr('model');
                $model = new $modelClass;
                $entity = $model->getEntity();
                $entity->setModel($model);

                $table = $model->getTable();

                if (!in_array($table, $droped)) $output->writeln($this->formatOutput('Droping table ' . $table));

                $entity->drop();
                if (!in_array($table, $droped)) $output->writeln($this->formatOutput('Success'));
                $droped[] = $table;
            }
        } catch (\PDOException $e) {
            $output->writeln($this->formatOutput('Failed'));
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Clear database');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Migration file name')
        ]);
    }
}
