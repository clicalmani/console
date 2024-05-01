<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Database\Factory\Index as FactoryIndex;
use Clicalmani\Database\Factory\Indexes\Index;
use Clicalmani\Flesco\Misc\RecursiveFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:fresh',
    description: 'Database migration command',
    hidden: false
)]
class MigrateFreshCommand extends Command
{
    private $models_path, $migrations_path;

    public function __construct(protected $root_path)
    {
        $this->models_path = $this->root_path . '/app/Models';
        $this->migrations_path = $this->root_path . '/database/migrations';
        $this->mkdir($this->migrations_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $filename = $input->getArgument('name');

        if ( !file_exists($this->migrations_path . "/$filename.xml") ) $this->genMigrationFile($filename);

        $db_seed = new ArrayInput([
            'command' => 'db:clear',
            'name' => $filename
        ]);

        if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
            return Command::FAILURE;
        }
        
        try {
            $this->migrate($filename, $output);
        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        if (false !== $input->getOption('seed')) {
            $db_seed = new ArrayInput([
                'command' => 'db:seed'
            ]);

            if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
                return Command::FAILURE;
            }
        }

        // if (false !== $input->getOption('routines')) {
        //     $db_seed = new ArrayInput([
        //         'command' => 'migrate:functions'
        //     ]);

        //     if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
        //         return Command::FAILURE;
        //     }

        //     $db_seed = new ArrayInput([
        //         'command' => 'migrate:procedures'
        //     ]);

        //     if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
        //         return Command::FAILURE;
        //     }

        //     $db_seed = new ArrayInput([
        //         'command' => 'migrate:views'
        //     ]);

        //     if (0 !== $this->getApplication()->doRun($db_seed, $output)) {
        //         return Command::FAILURE;
        //     }
        // }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Run a fresh database migration');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Migration file name'),
            new InputOption('seed', null, InputOption::VALUE_NONE, 'Run seeds after migration'),
            new InputOption('routines', null, InputOption::VALUE_NONE, 'Migrate routines')
        ]);
    }

    private function genMigrationFile(string $filename)
    {
        $dir = new \RecursiveDirectoryIterator($this->models_path);
        $filter = new RecursiveFilter($dir);
        $filter->setPattern("\\.php$");

        $xdt = xdt();
        $xdt->setDirectory($this->migrations_path);
        $xdt->newFile("$filename.xml", '<migration></migration>');
        $xdt->connect($filename, true, true);

        $tables = [];

        /**
         * Walkthrough models
         * Keep a track of each model and its entity.
         */
        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            $modelClass = "App\\" . substr($file->getPathname(), strlen( root_path() ) + 4);
            $modelClass = substr($modelClass, 0, strlen($modelClass) - 4);

            $model = new $modelClass;
            $entity = $model->getEntity();

            $tables[$model->getTable()] = $modelClass;
            $xdt->getDocumentRootElement()->append('<entity model="' . $modelClass . '">' . get_class($entity) . '</entity>');
        }

        /**
         * Establish relationship
         * Each entity must have its dependences migrated before migrating itself.
         */
        foreach ($xdt->select('entity') as $node) {
            $node = $xdt->parse($node);
            $modelClass = $node->attr('model');
            $model = new $modelClass;
            $entity = $model->getEntity();
            $entity->setModel($model);

            if ($attributes = (new \ReflectionClass($entity))->getAttributes(FactoryIndex::class)) {
                $arguments = $attributes[0]->getArguments();
                
                if ($refs = @$arguments['references'] AND $table = @$refs['table']) {
                    if (false == $node->hasChildren('dependences')) {
                        $node->append('<dependences></dependences>');
                    }

                    $depModelClass = $tables[$table];

                    $node->children()->first()->append('<entity model="' . $depModelClass . '">' . get_class(( new $depModelClass )->getEntity()) . '</entity>');
                }
            }
        }

        return $xdt->close();
    }

    private function migrate(string $filename, OutputInterface $output)
    {
        $xdt = xdt();
        $xdt->setDirectory($this->migrations_path);
        $xdt->connect($filename, true, true);

        $migrated = []; // Migration record

        /**
         * Run migration
         * The generated migration file should guide us to establish the 
         * migration order. We will use the parent child relationship in
         * an XML structure.
         */
        foreach ($xdt->getDocumentRootElement()->children() as $node) {
            $node = $xdt->parse($node);

            /**
             * Migration all dependences.
             */

            $record = []; // Dependency record FLO

            // Walkthrough each dependency
            foreach ($node->find('entity') as $child) {
                $child = $xdt->parse($child);
                $record[] = $child->attr('model');
            }

            rsort($record); // LFO

            // Migrate all dependences
            foreach ($record as $child) {
                $dependency = new $child;
                $entity = $dependency->getEntity();
                $entity->setModel($dependency);

                $table = $dependency->getTable();

                if (!in_array($table, $migrated)) $output->writeln($this->formatOutput('Migrating ' . $table));

                $entity->migrate();
                if (!in_array($table, $migrated)) $output->writeln($this->formatOutput('Success'));
                $migrated[] = $table;
            }

            // Migrate the entity
            $modelClass = $node->attr('model');
            $model = new $modelClass;
            $entity = $model->getEntity();
            $entity->setModel($model);

            $table = $model->getTable();

            if (!in_array($table, $migrated)) $output->writeln($this->formatOutput('Migrating ' . $table));

            $entity->migrate();
            if (!in_array($table, $migrated)) $output->writeln($this->formatOutput('Success'));
            $migrated[] = $table;
        }
    }
}
