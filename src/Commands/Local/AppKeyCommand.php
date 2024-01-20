<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate app key
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'app:key',
    description: 'Generate app key',
    hidden: false
)]
class AppKeyCommand extends Command
{
    private $database_path;

    public function __construct(protected $root_path)
    {
        $this->database_path = $this->root_path . '/database';
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $env_file = root_path('/.env');
        $key = password( faker()->alphaNum(100) );

        if ( file_exists($env_file) ) {
            $fh = \fopen($env_file, 'r');

            while (!feof($fh)) {
                $line = \fgets($fh, 1024);

                if (preg_match("/APP_KEY/", $line)) {
                    file_put_contents(
                        $env_file,
                        str_replace(trim($line), "APP_KEY = $key", file_get_contents($env_file))
                    );

                    $output->writeln('success');

                    return Command::SUCCESS;
                }
            }
        }

        $output->writeln('Failure');

        return Command::FAILURE;
    }
}
