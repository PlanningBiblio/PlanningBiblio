<?php

namespace App\Cron;

use App\Model\Cron;

class Crontab {

    public static $crons_dir = __DIR__ . '/../../src/Cron/Legacy/';

    private $executable_crons = array();

    private $entityManager;

    public function __construct()
    {
        $this->entityManager = $GLOBALS['entityManager'];

        $today = date('Y-m-d');

        $crons = $this->entityManager->getRepository(Cron::class)->findAll();

        foreach ($crons as $cron) {
            if ($cron->isDisabled()) {
                continue;
            }

            $date_cron = $cron->last()->format('Y-m-d H:m:s');

            // Daily crons.
            if ($cron->dom() == '*' and $cron->mon() == '*' and $cron->dow() == '*') {
                if ($date_cron < $today) {
                    $this->executable_crons[] = $cron;
                }
                continue;
            }

            // Yearly Cron
            if ($cron->dom() != '*' and $cron->mon() != '*') {
                $command_date = strtotime("{$cron->mon()}/{$cron->dom()}");
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

    public static function update_cron($cron)
    {
        $last = date_create();

        $cron->last($last);

        $entityManager = $GLOBALS['entityManager'];
        $entityManager->persist($cron);
        $entityManager->flush();
    }
}
