<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Console\Logic\MakeVendor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Start dev server console command
 * 
 * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'vendor:publish',
    description: 'Publish vendor configuration.',
    hidden: false
)]
class PublishCommand extends Command
{
    protected static $defaultDescription = 'Start the server';

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $vendor = new MakeVendor(__DIR__ . '/Samples');

        $vendor->setInput($input);
        $vendor->setOutput($output);

        if ($vendor->create()) {
            $output->writeln('<info>Command executed successfully</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Command failed</error>');
        
        return Command::FAILURE;
    }

    protected function configure() : void
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'Vendor name')
        ]);
    }
}
