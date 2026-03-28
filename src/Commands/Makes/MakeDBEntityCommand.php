<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create a model class command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:entity',
    description: 'Create a database entity class.',
    hidden: false
)]
class MakeDBEntityCommand extends Command
{
    private $entities_path;

    public function __construct(protected $rootPath)
    {
        $this->entities_path = $this->rootPath . '/database/entities';
        $this->mkdir($this->entities_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name   = $input->getArgument('model');

        $db_entity = new ArrayInput([
            'command' => 'db:entity',
            'name' => "{$name}Entity"
        ]);

        if (0 === $this->getApplication()->doRun($db_entity, $output)) {

            $model = new ArrayInput([
                'command' => 'make:model',
                'name' => $name,
                'table' => $input->getArgument('table'),
                'keys' => $input->getArgument('keys'),
                '--seed' => $input->getOption('seed')
            ]);

            if (0 !== $this->getApplication()->doRun($model, $output)) {
                $output->writeln("Could not create the database entity model $name");
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Create a new database entity');
        $this->setDefinition([
            new InputArgument('model', InputArgument::REQUIRED, 'Entity model name'),
            new InputArgument('table', InputArgument::REQUIRED, 'Database table name'),
            new InputArgument('keys', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Table keys'),
            new InputOption('seed', null, InputOption::VALUE_NONE, 'Seed entity model'),
        ]);
    }
}
