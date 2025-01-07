<?php
namespace Clicalmani\Console\Commands\Local;

use Clicalmani\Console\Commands\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'migrate:entity',
    description: 'Migrate a signle entity or dump the generated SQL command into a specified dump file.',
    hidden: false
)]
class MigrateEntityCommand extends Command
{
    public function __construct(protected $rootPath)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $model = $input->getArgument('model');
        $dump = $input->getOption('dump');
        $class = "\\App\\Models\\$model";
        
        try {
            /** @var \Clicalmani\Database\Factory\Models\Model */
            $model = new $class;
            $entity = $model->getEntity();
            $entity->setModel($model);

            if ($dump) $entity->migrate(false, $dump);
            else {
                $entity->drop();
                $entity->migrate();
            }
        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Migrate a single entity');
        $this->setDefinition([
            new InputArgument('model', InputArgument::REQUIRED, 'Model name of the entity to migrate'),
            new InputOption('dump', null, InputOption::VALUE_REQUIRED, 'Dump the generated SQL into a file')
        ]);
    }
}
