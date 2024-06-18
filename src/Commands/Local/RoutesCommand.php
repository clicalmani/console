<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Clicalmani\Fundation\Routing\Route;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RoutesCommand Class
 * 
 * @package Clcialmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'routes:all',
    description: 'Show stored routes.',
    hidden: false
)]
class RoutesCommand extends Command
{
    public function __construct(protected $root_path)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        print_r(Route::all());
        return Command::SUCCESS;
    }
}
