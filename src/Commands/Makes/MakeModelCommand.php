<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Foundation\Misc\RecursiveFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create a model class command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:model',
    description: 'Create a model class.',
    hidden: false
)]
class MakeModelCommand extends Command
{
    private $models_path, $entities_path;

    public function __construct(protected $rootPath)
    {
        $this->models_path = $this->rootPath . '/app/Models';
        $this->entities_path = $this->rootPath . '/database/entities';
        $this->mkdir($this->models_path);
        $this->mkdir($this->entities_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $model_name   = $input->getArgument('name');
        $entity_class = $this->findEntity("{$model_name}Entity");

        if (NULL === $entity_class) {
            $output->writeln("Could not find {$model_name}Entity class");
            return Command::FAILURE;
        }

        $table_name   = $input->getArgument('table');
        $primary_keys = $input->getArgument('keys');
        $has_seeder   = $input->getOption('seed');

        if ( count($primary_keys) > 1 ) $primary_keys = json_encode($primary_keys);
        elseif ( count($primary_keys) > 0 ) $primary_keys = '"' . $primary_keys[0] . '"';
        else $primary_keys = '""';

        $filename = $this->models_path . '/' . $model_name . '.php';
        $sample = $has_seeder ? 'ModelSeed.sample': 'Model.sample';

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/$sample"), [
                'model_name'   => $model_name,
                'model_entity' => $entity_class,
                'table_name'   => $table_name,
                'primary_keys' => $primary_keys
            ]) )
        );

        if ($success) {

            $output->writeln('Command executed successfully');

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Create new model');
        $this->addArgument('name', InputArgument::REQUIRED, 'Model name');
        $this->addArgument('table', InputArgument::REQUIRED, 'Table name');
        $this->addArgument('keys', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Primary key(s)');
        $this->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Model has a seeder');
        $this->addOption('seed', null, InputOption::VALUE_NONE, 'Model has a seeder');
    }

    private function findEntity(string $name)
    {
        $dir = new \RecursiveDirectoryIterator($this->entities_path);
        $filter = new RecursiveFilter($dir);
        $filter->setPattern("\\.php$");
        $filter->setFilter(["$name.php"]);
        
        foreach (new \RecursiveIteratorIterator($filter) as $file) {
            $pathname = $file->getPathname();
            $className = substr($pathname, strlen($this->entities_path) - strlen('/database/entities') + 1);
            $className = substr($className, 0, strlen($className) - 4);

            $bindings = [
                '/' => '\\',
                'database' => 'Database',
                'entities' => 'Entities'
            ];

            foreach ($bindings as $key => $value) {
                $className = str_replace($key, $value, $className);
            }

            return $className;
        }

        return null;
    }
}
