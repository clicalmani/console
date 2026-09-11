<?php

namespace Clicalmani\Console\Test;

use Clicalmani\Console\Commands\Command as BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ShowSkipped Command
 * 
 * * @package Clicalmani\Console
 * @author clicalmani
 */
#[AsCommand(
    name: 'show:skipped',
    description: 'Display PHPUnit skipped messages',
    hidden: false
)]
class ShowSkippedCommand extends ShowCommand
{
    protected string $option = 'display-skipped';

    public function __construct(protected $rootPath)
    {
        parent::__construct($rootPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return parent::execute($input, $output);
    }
}