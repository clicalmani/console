<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AppKeyCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'key:generate',
    description: 'Generate a new app key',
    hidden: false
)]
class AppKeyCommand extends Command
{
    public function __construct(protected $root_path)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Generating app key');

        $generator = new \Clicalmani\Console\Logic\GenerateAppKey();

        if (FALSE === $generator->isEnvSet()) {
            $output->writeln('App key is not set in .env file');
            $output->writeln('To set a new app key, please create the .env file and set the APP_KEY variable');
            $output->writeln('Current key: ' . env('APP_KEY'));

            return Command::FAILURE;
        }

        if ($key = $generator->generate()) {
            $output->writeln('App key set');
            $output->writeln('Key: ' . $key);

            return Command::SUCCESS;
        }

        $output->writeln('Failed to set app key');

        return Command::FAILURE;
    }
}
