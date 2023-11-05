<?php
namespace Clicalmani\Console;

class Kernel
{
   public static $kernel = [
      \Clicalmani\Console\Commands\Local\StartCommand::class,
      \Clicalmani\Console\Commands\Local\AppKeyCommand::class,
      \Clicalmani\Console\Commands\Local\DBClearCommand::class,
      \Clicalmani\Console\Commands\Local\MigrateFreshCommand::class,
      \Clicalmani\Console\Commands\Local\MigrateRoutineFunctionsCommand::class,
      \Clicalmani\Console\Commands\Local\MigrateStoredProceduresCommand::class,
      \Clicalmani\Console\Commands\Local\MigrateRoutineViewsCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeMigrationCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeModelCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeControllerCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeRequestCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeMiddlewareCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeSeederCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeHelperCommand::class,
      \Clicalmani\Console\Commands\Local\DBSeedCommand::class,
      \Clicalmani\Console\Commands\Makes\MakeFactoryCommand::class,
      \Clicalmani\Console\Commands\Makes\Test\MakeTestControllerCommand::class,
      \Clicalmani\Console\Commands\Local\Test\TestControllerCommand::class,
      \Clicalmani\Console\Commands\Routines\RoutineFunctionCommand::class,
      \Clicalmani\Console\Commands\Routines\RoutineProcedureCommand::class,
      \Clicalmani\Console\Commands\Routines\RoutineViewCommand::class,
   ];
}
