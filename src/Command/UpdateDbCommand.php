<?php

namespace App\Command;

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
    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ob_start();
        require_once(__DIR__ . '/../../init/init.php');
        $content = ob_get_clean();

        $io = new SymfonyStyle($input, $output);

        if ($content) {
            if ($output->isVerbose()) {
                $io->writeln($content);
            }

            $this->logToFile($content);
        }

        $migrationsOutput = new BufferedOutput();

        $migrations = new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
        ]);

        $migrations->setInteractive(false);

        $migrationsReturnCode = $this->getApplication()->doRun($migrations, $migrationsOutput);

        $migrationsContent = $migrationsOutput->fetch();

        $this->logToFile($migrationsContent);

        if ($migrationsReturnCode == 0) {
            $io->success('Database updated');
            return Command::SUCCESS;
        } else {
            $io->error(preg_replace('/\s+/', ' ', $migrationsContent));
            return Command::FAILURE;
        }
    }

    private function logToFile($content)
    {
        $folder = __DIR__ . '/../../var/update/' . $_ENV['APP_ENV'];
        $file = $folder . '/updateDB-' . date('Ymd-His') . '.txt';

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        file_put_contents($file, $content, FILE_APPEND);
    }
}
