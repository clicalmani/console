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
    name: 'make:request',
    description: 'The make command is the main command for launching verious tasks, such as creation of models, controllers, servicies, events and migration.',
    hidden: false
)]
class MakeRequestCommand extends Command
{
    private $requests_path;
    
    public function __construct(private $root_path)
    {
        $this->requests_path = $this->root_path . '/app/http/requests';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $request  = $input->getArgument('name');
        $filename = $this->requests_path . '/' . $request . '.php';

        $success = file_put_contents(
            $filename, 
            "<?php\n" . 
            ltrim( Tools::eval(file_get_contents( __DIR__ . "/Samples/Request.sample"), [
                'request' => $request
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
        $this->setHelp('Create new request');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Request name')
        ]);
    }
}
