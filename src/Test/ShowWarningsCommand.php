<?php

namespace Clicalmani\Console\Test;

use Clicalmani\Console\Commands\Command as BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ShowWarnings Command
 * 
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'show:warnings',
    description: 'Display PHPUnit warnings',
    hidden: false
)]
class ShowWarningsCommand extends ShowCommand
{
    protected string $option = 'display-warnings';

    public function __construct(protected $rootPath)
    {
        parent::__construct($rootPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return parent::execute($input, $output);
    }
}