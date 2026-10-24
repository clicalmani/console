<?php
namespace Clicalmani\Console\Commands\Makes;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Core\Providers\ValidationServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Clicalmani\Core\Sandbox\Sandbox;
use Clicalmani\Validation\Validator;
use Symfony\Component\Console\Input\InputOption;

/**
 * MakeServiceProviderCommand class
 * 
 * @package Clicalmani\Console
 * @author @clicalmani
 */
#[AsCommand(
    name: 'make:provider',
    description: 'Create a custom service provider.',
    hidden: false
)]
class MakeServiceProviderCommand extends Command
{
    private $providersPath;

    public function __construct(protected $rootPath)
    {
        $this->providersPath = $this->rootPath . '/app/Providers';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->mkdir($this->providersPath);
        
        $provider = $input->getArgument('name');

        $filename  = $this->providersPath . '/' . $provider . '.php';
        $sample    = __DIR__ . '/Samples/ServiceProvider.sample';
        $className = basename($provider);

        $success = file_put_contents(
            $filename, 
            ltrim( 
                Sandbox::eval(file_get_contents($sample), compact('className'))
            )
        );

        if ($success) {
            $output->writeln('<info>Command executed successfully</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Failed to execute the command</error>');

        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Service provider class name'),
        ]);
    }
}
