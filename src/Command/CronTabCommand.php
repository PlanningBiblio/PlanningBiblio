<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cron:tab',
    description: 'Add a short description for your command',
)]
class CronTabCommand extends Command
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();

        $today = date('Y-m-d');

        $crons = $entityManager->getRepository(Cron::class)->findBy(['disabled' => 0]);

        foreach ($crons as $cron) {

            $date_cron = $cron->getLast()->format('Y-m-d H:m:s');

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

        if (php_sapi_name() != 'cli') {

            $crons = $this->crons();

            foreach ($crons as $cron) {
                include($this->crons_dir . $cron->getCommand());
                bin/console $cron->getCommand()
                $this->update_cron($cron);
            }

            // Absences : Met à jour la table absences avec les événements récurrents sans date de fin (J + 2ans)
            // 1 fois par jour
            require_once __DIR__ . '/../../legacy/Class/class.absences.php';

            $a = new \absences();
            $a->CSRFToken = $GLOBALS['CSRFSession'];
            $a->ics_update_table();
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }

    public static function update_cron($cron)
    {
        $last = date_create();

        $cron->setLast($last);

        $entityManager = $GLOBALS['entityManager'];
        $entityManager->persist($cron);
        $entityManager->flush();
    }
}
