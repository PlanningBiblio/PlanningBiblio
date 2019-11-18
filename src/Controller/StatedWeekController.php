<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/include/feries.php');

class StatedWeekController extends BaseController
{
    /**
     * @Route("/statedweek", name="statedweek.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $date = $request->get('date');
        if (!$date) {
            $date = date('Y-m-d');
        }

        $date_pl = new \datePl($date);

        $this->templateParams(array(
            'sunday_enabled'    => $this->config('Dimanche'),
            'date'              => $date,
            'week_number'       => $date_pl->semaine,
            'week_days'         => $date_pl->dates,
            'date_text'         => dateAlpha($date),
            'public_holiday'    => jour_ferie($date),
        ));

        return $this->output('statedweek/index.html.twig');
    }
}