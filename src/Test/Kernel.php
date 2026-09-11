<?php
namespace Clicalmani\Console\Test;

final class Kernel
{
    public static $kernel = [
        \Clicalmani\Console\Test\Command::class,
        \Clicalmani\Console\Test\ShowDeprecationsCommand::class,
        \Clicalmani\Console\Test\ShowWarningsCommand::class,
        \Clicalmani\Console\Test\ShowRiskyCommand::class,
        \Clicalmani\Console\Test\ShowErrorsCommand::class,
        \Clicalmani\Console\Test\ShowNoticesCommand::class,
        \Clicalmani\Console\Test\ShowSkippedCommand::class,
    ];
}