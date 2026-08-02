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
        $alter = $input->getOption('alter');
        $manifest = $input->getOption('manifest');
        $class = "\\App\\Models\\$model";
        
        try {
            /** @var \Clicalmani\Database\Factory\Models\Elegant */
            $model = new $class;
            $entity = $model->getEntity();
            $entity->setModel($model);

            if ($dump) $entity->migrate(false, $dump);
            else {
                if (!$alter) $entity->drop();
                $entity->migrate();

                if ($manifest = $input->getOption('manifest')) {
                    $xdt = xdt();
                    $xdt->setDirectory(database_path('/manifests'));
                    $xdt->connect($manifest, true, true);

                   // Write the alter operation to the manifest.
                    if ($alter) {
                        try {
                            foreach ($xdt->getDocumentRootElement()->children('entity') as $node) {
                                $node = $xdt->parse($node);
                                if ($node->attr('model') !== $model::class) continue;
                                $definition = base64_encode($entity->alter(new \Clicalmani\Database\Factory\AlterOption));
                                $node->append("<alter>{$definition}</alter>");
                                break;
                            }
                            $xdt->close();
                        } catch (\Exception $e) {
                            $output->writeln('<error>' . $e->getMessage() . '</error>');
                        }
                    } else {
                        $updates = $xdt->getDocumentRootElement()->children('updates');

                        if ($updates->length === 0) {
                            $updates = $xdt->getDocumentRootElement()->append('<updates></updates>')->children('updates');
                        }

                        $updates->append('<entity model="' . $model::class . '">' . $entity::class . '</entity>');
                    }

                    $xdt->close();
                }
            }
        } catch (\PDOException $e) {
            $output->writeln('Failed');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        $output->writeln('Command executed successfully!');

        return Command::SUCCESS;
    }

    protected function configure() : void
    {
        $this->setHelp('Migrate a single entity');
        $this->setDefinition([
            new InputArgument('model', InputArgument::REQUIRED, 'Model name of the entity to migrate'),
            new InputOption('manifest', 'm', InputOption::VALUE_REQUIRED, 'The current manifest'),
            new InputOption('dump', 'd', InputOption::VALUE_REQUIRED, 'Dump the generated SQL into a file'),
            new InputOption('alter', 'a', InputOption::VALUE_NONE, 'Alter the entity table instead'),
        ]);
    }
}
