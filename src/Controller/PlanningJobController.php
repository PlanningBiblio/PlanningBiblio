<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/include/function.php');

class PlanningJobController extends BaseController
{
    /**
     * @Route("/ajax/planningjob/checkcopy", name="ajax.planningjobcheckcopy", methods={"GET"})
     */
    public function checkCopy(Request $request)
    {
        // Initilisation des variables
        $date = $request->get('date');
        $start = $request->get('from');
        $end = $request->get('to');
        $agent_id = $request->get('agent');

        $result = array('error' => false);
        $agent = $this->entityManager->find(Agent::class, $agent_id);
        $fullname = $agent->prenom() . ' ' . $agent->nom();
        if ($agent->isAbsentOn("$date $start", "$date $end")) {
            $result['error'] = $fullname;
        }

        if ($agent->isOnVacationOn("$date $start", "$date $end")) {
            $result['error'] = $fullname;
        }

        $d = new \datePl($date);
        $day = $d->planning_day_index_for($agent_id);
        $working_hours = $agent->getWorkingHoursOn($date);

        if (!calculSiPresent($start, $end, $working_hours['temps'], $day)) {
            $result['error'] = $fullname;
        }

        return $this->json($result);
    }
}