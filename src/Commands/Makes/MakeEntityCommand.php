<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Sandbox\Sandbox;

/**
 * Create a model class command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'db:entity',
    description: 'Create a database entity class.',
    hidden: false
)]
class MakeEntityCommand extends Command
{
    private $entities_path;

    public function __construct(protected $root_path)
    {
        $this->entities_path = $this->root_path . '/database/entities';
        $this->mkdir($this->entities_path);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name   = $input->getArgument('name');

        $filename = $this->entities_path . '/' . $name . '.php';

        $success = file_put_contents(
            $filename, 
            ltrim( Sandbox::eval(file_get_contents( __DIR__ . "/Samples/Entity.sample"), [
                'name'   => $name
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
        $this->setHelp('Create a new database entity');
        $this->addArgument('name', InputArgument::REQUIRED, 'Entity name');
    }
}
