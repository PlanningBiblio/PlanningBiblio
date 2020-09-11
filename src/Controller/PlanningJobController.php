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
        $agents = json_decode($request->get('agents'));

        $availables = array();
        $unavailables = array();
        $errors = array();

        foreach ($agents as $agent_id) {

            try {
                $agent = $this->entityManager->find(Agent::class, $agent_id);
                $fullname = $agent->prenom() . ' ' . $agent->nom();
                $available = true;

                if ($agent->isAbsentOn("$date $start", "$date $end")) {
                    $available = false;
                }

                if ($available and $agent->isOnVacationOn("$date $start", "$date $end")) {
                    $result['unavailable'][] = $fullname;
                    $available = false;
                }

                if ($available) {
                    $d = new \datePl($date);
                    $day = $d->planning_day_index_for($agent_id);
                    $working_hours = $agent->getWorkingHoursOn($date);

                    if (!calculSiPresent($start, $end, $working_hours['temps'], $day)) {
                        $result['unavailable'][] = $fullname;
                        $available = false;
                    }
                }

                if ($available) {
                    $availables[] = $agent_id;
                } else {
                    $unavailables[] = $fullname;
                }
            } catch(Exception $e) {
                $errors[] = $e;
            }
        }

        $unavailables_string = !empty($unavailables) ? "\n - " . implode("\n - ", $unavailables) : null;

        $result = array(
            'availables' => $availables,
            'unavailables' => $unavailables_string,
            'errors' => $errors,
        );

        return $this->json($result);
    }
}