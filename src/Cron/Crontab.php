<?php

namespace App\Cron;

use App\Entity\Cron;

class Crontab {

    private $crons_dir = __DIR__ . '/../../src/Cron/Legacy/';

    private $executable_crons = array();

    private $entityManager;

    public function __construct()
    {
        $this->entityManager = $GLOBALS['entityManager'];

        $today = date('Y-m-d');

        $crons = $this->entityManager->getRepository(Cron::class)->findBy(['disabled' => 0]);

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

    public function crons()
    {
        return $this->executable_crons;
    }

    public function execute()
    {
        if (php_sapi_name() != 'cli') {

            $crons = $this->crons();

            foreach ($crons as $cron) {
                include($this->crons_dir . $cron->getCommand());
                $this->update_cron($cron);
            }

            // Absences : Met à jour la table absences avec les événements récurrents sans date de fin (J + 2ans)
            // 1 fois par jour
            require_once __DIR__ . '/../../public/absences/class.absences.php';

            $a = new \absences();
            $a->CSRFToken = $GLOBALS['CSRFSession'];
            $a->ics_update_table();
        }
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
