<?php

namespace App\Command;

use App\Entity\Absence;
use App\Entity\Cron;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

require_once __DIR__ . '/../../legacy/Common/function.php';

#[AsCommand(
    name: 'app:crontab',
    description: 'Execute enabled scheduled cron jobs defined in the database and updates their last run time',
)]
class CronTabCommand extends Command
{
    private $entityManager;
    private $executable_crons;
    private $kernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->executable_crons = [];
        $this->kernel = $kernel;

        $today = date('Y-m-d 00:00:00');

        $crons = $entityManager->getRepository(Cron::class)->findBy(['disabled' => 0]);

        foreach ($crons as $cron) {
            $lastRun = $cron->getLast();
            $date_cron = $lastRun ? $lastRun->format('Y-m-d H:i:s') : '1970-01-01 00:00:00';

            // Daily crons.
            if ($cron->getDom() == '*' and $cron->getMon() == '*' and $cron->getDow() == '*') {
                if ($date_cron < $today) {
                    $this->executable_crons[] = $cron;
                }
                continue;
            }

            // Yearly Cron
            if ($cron->getDom() != '*' and $cron->getMon() != '*') {
                $command_date = strtotime("{$cron->getMon()}/{$cron->getDom()}");
                if ($command_date > time()) {
                    $command_date = strtotime('-1 year', $command_date);
                }

                $command_date = date('Y-m-d 00:00:00', $command_date);

                if ($date_cron < $command_date) {
                    $this->executable_crons[] = $cron;
                }
            }
        }

        parent::__construct();
    }

    protected function configure(): void
    {
    }

    public function crons()
    {
        return $this->executable_crons;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $CSRFToken = CSRFToken();

        $app = new Application($this->kernel);
        $app->setAutoExit(false);

        $crons = $this->crons();

        foreach ($crons as $cron) {
            $fullCommand = explode(' ', $cron->getCommand());
            $command = $fullCommand[0];
            $option = $fullCommand[1] ?? null;

            if ($output->isVerbose()) {
                $io->text("Running: $command");
            }

            if ($option == '--force') {
                $cronInput = new ArrayInput([
                    'command' => $command,
                    '--force' => true,
                ]);
            } else {
                $cronInput = new ArrayInput([
                    'command' => $command,
                ]);
            }

            // disable interactive behavior for the greet command
            $cronInput->setInteractive(false);

            $returnCode = $app->doRun($cronInput, $output);

            $this->update_cron($cron);
        }

        // Absences : Met à jour la table absences avec les événements récurrents sans date de fin (J + 2ans)
        // 1 fois par jour

        $this->entityManager->getRepository(Absence::class)->icsUpdateTable($CSRFToken);
  //test unitaire test last, create une absence_recurrente (ancienne,termine, sans date de fin)
        if ($output->isVerbose()) {
            $io->success('All scheduled cron jobs have been executed successfully.');
        }

        return Command::SUCCESS;
    }

    private function update_cron($cron)
    {
        $last = date_create();

        $cron->setLast($last);

        $this->entityManager->persist($cron);
        $this->entityManager->flush();
    }
}
