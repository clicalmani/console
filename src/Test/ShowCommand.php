<?php

namespace Clicalmani\Console\Test;

use Clicalmani\Console\Commands\Command as BaseCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends BaseCommand
{
    protected string $option;

    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        exec("php vendor/bin/phpunit --{$this->option}", $out, $resultCode);

        if ($resultCode === 0) {
            $output->write($out, true);
            return Command::SUCCESS;
        }
        
        $output->write($out, true);

        return Command::FAILURE;
    }
}