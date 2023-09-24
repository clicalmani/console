<?php
namespace Clicalmani\Flesco\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:migration',
    description: 'The make command is the main command for launching verious tasks, such as creation of models, controllers, servicies, events and migration.',
    hidden: false
)]
class MakeMigrationCommand extends Command
{
    protected static $defaultDescription = 'The make command is the command main for lunching verious tasks, such as creation of models, controllers, servicies, events and migration. Enter --help for more informations.';

    private $filename;

    public function __construct(private $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->filename = $this->database_path . '/migrations/' . date('Y_m_d') . '_' . time() . '_' . $input->getArgument('filename') . '.php';

        $success = file_put_contents($this->filename, file_get_contents( __DIR__ . '/Samples/Migration.sample'));

        if ($success) {

            $output->writeln('Command executed successfully');

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setHelp('Database migration command. It allows tables creation, manipulation and deletion');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The file name for migration');
    }
}
