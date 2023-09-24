<?php
namespace Clicalmani\Flesco\Console\Commands\Makes;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Flesco\Misc\Tools;

#[AsCommand(
    name: 'make:model',
    description: 'The make command is the main command for launching verious tasks, such as creation of models, controllers, servicies, events and migration.',
    hidden: false
)]
class MakeModelCommand extends Command
{
    public function __construct(private $root_path)
    {
        $this->models_path = $this->root_path . '/app/models';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $model_name   = $input->getArgument('name');
        $table_name   = $input->getArgument('table');
        $primary_keys = $input->getArgument('keys');

        if ( count($primary_keys) > 1 ) $primary_keys = json_encode($primary_keys);
        elseif ( count($primary_keys) > 0 ) $primary_keys = '"' . $primary_keys[0] . '"';
        else $primary_keys = '""';

        $filename = $this->models_path . '/' . $model_name . '.php';

        $success = file_put_contents(
            $filename, 
            "<?php\n" . 
            ltrim( Tools::eval(file_get_contents( __DIR__ . '/Samples/Model.sample'), [
                'model_name'   => $model_name,
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
    }
}
