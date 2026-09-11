<?php
namespace Clicalmani\Console;

class Application extends \Symfony\Component\Console\Application
{
    public function __construct(private \Clicalmani\Foundation\Maker\Application $app)
    {
        parent::__construct();
    }

    public function make()
    {
        foreach ($this->app->commands() as $command) {
            $this->add(new $command($this->app->rootPath()));
        }
    }

    public function test(): void
    {
        foreach (\Clicalmani\Console\Test\Kernel::$kernel as $command) {
            $this->add(new $command($this->app->rootPath()));
        }
    }
}
