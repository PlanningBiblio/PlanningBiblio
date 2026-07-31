<?php

namespace App\Command;

use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Version\Version;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-db',
    description: 'Update database',
)]
class UpdateDbCommand extends Command
{
    private $io;
    private string $folder;
    private $output;
    private string $time;

    public function __construct(
        private DependencyFactory $dependencyFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->folder = __DIR__ . '/../../var/update/' . $_ENV['APP_ENV'];
        $this->time = date('Ymd-His');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->output = $output;

        // Run priority migrations (e.g.: "Add JSON roles column to the personnel table")
        if (!$this->migrate(['App\Migrations\Version20260731161022'])) {
            return Command::FAILURE;
        }

        ob_start();
        require_once(__DIR__ . '/../../init/init.php');
        $content = ob_get_clean();

        if ($content) {
            if ($output->isVerbose()) {
                $this->io->writeln($content);
            }

            $this->logToFile($content);
        }

        if (!$this->migrate()) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function isExecuted(string $versionClassName): bool
    {
        // Example of $versionClassName: 'DoctrineMigrations\Version20260821123456'
        $version = new Version($versionClassName);

        // Retrieve the Doctrine metadata storage manager
        $storage = $this->dependencyFactory->getMetadataStorage();

        // Check if the version exists in the executed migrations table
        return $storage->getExecutedMigrations()->hasMigration($version);
    }

    private function logToFile($content): void
    {
        $file = $this->folder . '/updateDB-' . $this->time . '.txt';

        if (!file_exists($this->folder)) {
            mkdir($this->folder, 0755, true);
        }

        file_put_contents($file, $content, FILE_APPEND);
    }

    private function migrate(Array $migrationList = []): bool
    {
        $migrationsOutput = new BufferedOutput();
        $migrationsOutput->setVerbosity(128);

        if (empty($migrationList)) {
            $migrations = new ArrayInput([
                'command' => 'doctrine:migrations:migrate',
            ]);
        } else {
            foreach($migrationList as $key => $value) {
                if ($this->isExecuted($value)) {
                    unset($migrationList[$key]);
                }
            }

            if (empty($migrationList)) {
                return true;
            }

            $migrations = new ArrayInput([
                'command' => 'doctrine:migrations:execute',
                'versions' => $migrationList,
                '--up' => true,
            ]);
        }

        $migrations->setInteractive(false);

        $migrationsReturnCode = $this->getApplication()->doRun($migrations, $migrationsOutput);

        $migrationsContent = $migrationsOutput->fetch();

        $this->logToFile($migrationsContent);

        if ($this->output->isVerbose()) {
            $this->io->writeln($migrationsContent);
        }

        if ($migrationsReturnCode == 0) {
            $this->io->success('Database updated');
            return true;
        } else {
            $this->logToFile('[ERROR] One or more migration failed !');
            $this->io->error(preg_replace('/\s+/', ' ', $migrationsContent));
            return false;
        }        
    }
}
