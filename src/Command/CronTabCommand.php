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

require_once __DIR__ . '/../../public/include/function.php';

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
        //$crons = $entityManager->getRepository(Cron::class)->findAll();//TODO TEST

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
            $cmd = $cron->getCommand();

            if ($output->isVerbose()) {
                $io->text("Running: $cmd");
            }
            
            $cronInput = new ArrayInput([
                'command' => $cmd
            ]);

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

    private function update_cron($cron)
    {
        $last = date_create();

        $cron->setLast($last);

        $this->entityManager->persist($cron);
        $this->entityManager->flush();
    }

    private function isDue(Cron $cron): bool
    {

        $M = $cron->getM();
        if (preg_match('/^\*\/(\d+)$/', $M, $matches)) {
            $step = (int)$matches[1];
            $m = range(0, 59, $step);
        } elseif (preg_match('/^(\d+)$/', $M, $matches)) {
            $m = [(int)$matches[1]];
        } elseif ($M === '*') {
            $m = range(0, 59);
        } else {
            $m = [];
        }

        $H = $cron->getH();
        if (preg_match('/^(\d+)-(\d+)$/', $H, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $h = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $H, $matches)) {
            $h = [(int)$matches[1]];
        } elseif ($H === '*') {
            $h = range(0, 23);
        } else {
            $h = [];
        }

        $Dom = $cron->getDom();
        if (preg_match('/^(\d+)-(\d+)$/', $Dom, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $dom = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $Dom, $matches)) {
            $dom = [(int)$matches[1]];
        } elseif ($Dom === '*') {
            $dom = range(1, 31);
        } else {
            $dom = [];
        }

        $Mon = $cron->getMon();
        if (preg_match('/^(\d+)-(\d+)$/', $Mon, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $mon = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $Mon, $matches)) {
            $mon = [(int)$matches[1]];
        } elseif ($Mon === '*') {
            $mon = range(1, 12);
        } else {
            $mon = [];
        }

        $Dow = $cron->getDow();
        if (preg_match('/^(\d+)-(\d+)$/', $Dow, $matches)) {
            $start = (int)$matches[1];
            $end = (int)$matches[2];
            $dow = range($start, $end);
        } elseif (preg_match('/^(\d+)$/', $Dow, $matches)) {
            $dow = [(int)$matches[1]];
        } elseif ($Dow === '*') {
            $dow = range(1, 7);
        } else {
            $dow = [];
        }

        $now = new \DateTime();
        $hour = (int)$now->format('H');
        $month = $now->format('m');
        $day = $now->format('d');
        $minute = $now->format('i');
        $week = $now->format('N');

        if (!in_array((int)$month,$mon)) {
            return false;
        }
        if (!in_array((int)$week,$dow)) {
            return false;
        }
        if (!in_array((int)$day,$dom)) {
            return false;
        }
        if (!in_array((int)$hour,$h)) {
            return false;
        }
        if (!in_array((int)$minute,$m)) {
            return false;
        }

        return true;

    }


}
