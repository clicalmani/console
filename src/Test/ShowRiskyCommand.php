<?php

namespace Clicalmani\Console\Test;

use Clicalmani\Console\Commands\Command as BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ShowRisky Command
 * 
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'show:risky',
    description: 'Display PHPUnit fail on risky warnings',
    hidden: false
)]
class ShowRiskyCommand extends ShowCommand
{
    protected string $option = 'fail-on-risky';

    public function __construct(protected $rootPath)
    {
        parent::__construct($rootPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return parent::execute($input, $output);
    }
}