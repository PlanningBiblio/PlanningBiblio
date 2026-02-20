<?php

namespace App\Command;

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

        $crons = $entityManager->getRepository(Cron::class)->findBy(['disabled' => 0]);

        foreach ($crons as $cron) {
            if ($this->isDue($cron)) {
                $this->executable_crons[] = $cron;
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
        require_once __DIR__ . '/../../legacy/Class/class.absences.php';

        $a = new \absences();
        $a->CSRFToken = $CSRFToken;
        $a->ics_update_table();
  
        if ($output->isVerbose()) {
            $io->success('All scheduled cron jobs have been executed successfully.');
        }

        return Command::SUCCESS;
    }

    private function update_cron($cron): void
    {
        $last = date_create();

        $cron->setLast($last);

        $this->entityManager->persist($cron);
        $this->entityManager->flush();
    }

    private function isDue(Cron $cron): bool
    {
        $now = new \DateTime();

        // Minutes
        $minutes = $cron->getM();
        if ($minutes === '*') {
            $m = range(0, 59);
        } elseif (preg_match('/^\*\/(\d+)$/', $minutes, $matches)) {
            $step = (int)$matches[1];
            $m = range(0, 59, $step);
        } elseif (preg_match('/^(\d+)$/', $minutes, $matches)) {
            $m = [(int)$matches[1]];
        }

        $minutes = 0;
        if (isset($m)) {
            foreach ($m as $elem) {
                $minutes = $elem;
                if ($elem >= (int) $now->format('i')) {
                    break;
                }
            }
        }
        $minutes = sprintf('%02d', $minutes);

        // Hours
        $hours = $cron->getH();
        if ($hours === '*') {
            $h = range(0, 23);
        } elseif (preg_match('/^(\d+)-(\d+)$/', $hours, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $h = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $hours, $matches)) {
            $h = [(int)$matches[1]];
        }

        $hours = 0;
        if (isset($h)) {
            foreach ($h as $elem) {
                $hours = $elem;
                if ($elem >= $now->format('G')) {
                    break;
                }
            }
        }

        // Day of Month
        $dayOfMonth = $cron->getDom();
        if ($dayOfMonth === '*') {
            $dom = range(1, 31);
        } elseif (preg_match('/^(\d+)-(\d+)$/', $dayOfMonth, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $dom = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $dayOfMonth, $matches)) {
            $dom = [(int)$matches[1]];
        }

        $dayOfMonth = null;
        if (isset($dom)) {
            foreach ($dom as $elem) {
                $dayOfMonth = $elem;
                if ($elem >= $now->format('j')) {
                    break;
                }
            }
        }

        // Month
        $month = $cron->getMon();
        if ($month === '*') {
            $mon = range(1, 12);
        } elseif (preg_match('/^(\d+)-(\d+)$/', $month, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $mon = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $month, $matches)) {
            $mon = [(int)$matches[1]];
        }

        $month = null;
        if (isset($mon)) {
            foreach ($mon as $elem) {
                $month = $elem;
                if ($elem >= $now->format('n')) {
                    break;
                }
            }
        }

        // Due date
        $dueDate = \DateTime::createFromFormat('m-d G:i', $month . '-' . $dayOfMonth . ' ' . $hours . ':' . $minutes);

        // Day of Week, modify Due Date if needed
        $week = $now->format('N') + 1;
        if (is_numeric($cron->getDow()) and $week != $cron->getDow()) {
            $dow = match((int) $cron->getDow()) {
                0 => 'Sunday',
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thrusday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday',
            };

            $dueDate->modify('last ' .$dow);
        }

        $isDue = ($dueDate and $dueDate <= $now and $dueDate > $cron->getLast());

        return $isDue;
    }

}
