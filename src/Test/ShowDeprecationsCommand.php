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
    name: 'show:deprecations',
    description: 'Display PHPUnit deprecations errors',
    hidden: false
)]
class ShowDeprecationsCommand extends ShowCommand
{
    protected string $option = 'display-deprecations';

    public function __construct(protected $rootPath)
    {
        parent::__construct($rootPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return parent::execute($input, $output);
    }
}