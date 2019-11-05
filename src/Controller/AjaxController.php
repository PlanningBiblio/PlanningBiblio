<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/conges/class.conges.php');

class AjaxController extends BaseController
{
    /**
     * @Route("/ajax/holiday-credit", name="ajax.holidaycredit", methods={"GET"})
     */
    public function checkCredit(Request $request)
    {
        // Initilisation des variables
        $debut =dateSQL($request->get('debut'));
        $fin =dateSQL($request->get('fin'));
        $hre_debut = $request->get('hre_debut');
        $hre_fin = $request->get('hre_fin');
        $perso_id = $request->get('perso_id');

        $c = new \conges();
        $recover = $c->calculCreditRecup($perso_id, $debut);

        $holidayHlper = new HolidayHelper(array(
            'start' => $debut,
            'hour_start' => $hre_debut,
            'end' => $fin,
            'hour_end' => $hre_fin,
            'perso_id' => $perso_id
        ));
        $result = $holidayHlper->getCountedHours();

        $result['recover'] = $recover;

        return $this->json($result);
    }
}