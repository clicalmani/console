<?php
namespace Clicalmani\Console;

class Kernel
{
   public static $kernel = [
      \Clicalmani\Console\Commands\Local\StartCommand::class,
      \Clicalmani\Console\Commands\Local\MigrateFreshCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeMigrationCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeModelCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeControllerCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeRequestCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeMiddlewareCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeSeederCommand::class,
      \Clicalmani\Console\Commands\Local\DBSeedCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeFactoryCommand::class,
   ];
}
