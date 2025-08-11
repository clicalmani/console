<?php
namespace Clicalmani\Console\Commands\Tasks;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DebugCommand Class
 * 
 * @package clicalmani/console
 * @author clicalmani
 */
#[\Symfony\Component\Console\Attribute\AsCommand(
    name: 'schedule:consume',
    description: 'Consume schedules',
    hidden: false
)]
class ConsumeCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output = `crontab -l`;
        $find="' https";
        $replace="'https";
        $output =str_replace($find, $replace,$output);
        $file="/var/www/html/crontab.txt";
        file_put_contents($file, $output . "\n* * * * * php {$this->rootPath}/vendor/clicalmani/console/src/Commands/Tasks/bin/index.php\n");
        `crontab -r`;
        `crontab $file`;
        
        return Command::SUCCESS;
    }
}
