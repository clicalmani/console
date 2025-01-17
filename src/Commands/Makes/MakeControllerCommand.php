<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Console\Logic\MakeController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Foundation\Sandbox\Sandbox;

/**
 * Create a new controller
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'make:controller',
    description: 'Create a new controller in the controllers directory.',
    hidden: false
)]
class MakeControllerCommand extends Command
{
    private $controllers_path;
    private $requests_path;

    public function __construct(protected $rootPath)
    {
        parent::__construct($rootPath);
        $this->controllers_path = $this->rootPath . '/app/Http/Controllers';
        $this->requests_path = $this->rootPath . '/app/Http/Requests';
        $this->mkdir($this->controllers_path);
        $this->mkdir($this->requests_path);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $controller = new MakeController( __DIR__ );

        $controller->setInput($input);
        $controller->setOutput($output);
        
        $resource   = $controller->getResourceModel();
        $name       = $input->getArgument('name');
        $namespace  = "App\\Http\\Controllers" . ($this->getPath($name) ? "\\" . $this->getPath($name): '');
        $file_path  = $this->controllers_path . ($this->getPath($name) ? '/' . $this->getPath($name): '');

        $this->mkdir($file_path);

        $filename = $file_path . '/' . $this->getClass($name) . '.php';

        if ($controller->create($filename, $namespace, $this->getClass($name), $this->getClass((string) $resource))) {

            $controller->maybeGenerateFormRequests($this->requests_path);

            $output->writeln('Command executed successfully');

            return Command::SUCCESS;
        }

        $output->writeln('Failed to execute the command');

        return Command::FAILURE;
    }

    /**
     * 
     */
    protected function configure() : void
    {
        $this->setHelp('Create new controller');
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Controller name'),
            new InputOption('api', null, InputOption::VALUE_NONE, 'Api Controller'),
            new InputOption('resource', null, InputOption::VALUE_NONE, 'Resource Controller'),
            new InputOption('invokable', null, InputOption::VALUE_NONE, 'Invokable Controller'),
            new InputOption('model', null, InputOption::VALUE_REQUIRED, 'Resource Model'),
            new InputOption('request', null, InputOption::VALUE_NONE, 'Generate form requests'),
            new InputOption('singleton', null, InputOption::VALUE_NONE, 'Create a singleton resource controller'),
        ]);
    }
}
