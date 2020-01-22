<?php

namespace App\Controller;

use App\Controller\BaseController;

use App\Model\Agent;
use App\Model\StatedWeek;
use App\Model\StatedWeekTimes;
use App\Model\StatedWeekColumn;
use App\Model\Interchange;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

include_once(__DIR__ . '/../../public/include/function.php');

class InterchangeController extends BaseController
{
    /**
     * @Route("/interchange", name="interchange.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $interchanges = array();
        foreach ($this->entityManager
            ->getRepository(Interchange::class)
            ->findAll() as $interchange) {

            $planning = $this->entityManager
                ->getRepository(StatedWeek::class)
                ->find($interchange->planning());

            $requester = $this->entityManager
                ->getRepository(Agent::class)
                ->find($interchange->requester());

            $requester_time = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->find($interchange->requester_time());

            $requester_column = $this->entityManager
                ->getRepository(StatedWeekColumn::class)
                ->find($requester_time->column_id());

            $asked = $this->entityManager
                ->getRepository(Agent::class)
                ->find($interchange->requester());

            $asked_time = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->find($interchange->asked_time());

            $asked_column = $this->entityManager
                ->getRepository(StatedWeekColumn::class)
                ->find($asked_time->column_id());


            $interchanges[] = array(
                'id' => $interchange->id(),
                'date' => dateAlpha($planning->date()->format('Y-m-d')),
                'requester' => $requester->nom() . ' ' . $requester->prenom(),
                'from' => $requester_column->starttime()->format('H:i:s'),
                'to' => $requester_column->endtime()->format('H:i:s'),
                'asked' => $asked->nom() . ' ' . $asked->prenom(),
                'asked_from' => $asked_column->starttime()->format('H:i:s'),
                'asked_to' => $asked_column->endtime()->format('H:i:s'),
                'status' => $interchange->status()
            );
        }

        $this->templateParams(array(
            'interchanges' => $interchanges
        ));

        return $this->output('interchange/index.html.twig');
    }

    /**
     * @Route("/interchange/add", name="interchange.add", methods={"GET"})
     */
    public function addForm(Request $request)
    {
        return $this->output('interchange/add.html.twig');
    }

    /**
     * @Route("/interchange/{id}", name="interchange.edit", methods={"GET"})
     */
    public function editForm(Request $request)
    {
        $id = $request->get('id');
        $interchange = $this->entityManager->getRepository(Interchange::class)->find($id);

        $droits = $GLOBALS['droits'];

        $i = array();
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->find($interchange->planning());

        $i['id'] = $interchange->id();
        $i['date'] = $planning->date()->format('d/m/Y');
        $i['asked_time'] = $interchange->asked_time();
        $i['status'] = $interchange->status();
        $asked_time = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->find($interchange->asked_time());
        $i['asked_column'] = $asked_time->column_id();

        $columns = $planning->columns();
        $i['columns'] = array();
        $i['agents'] = array();
        foreach ($columns as $column) {
            $time = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->findBy(array(
                    'agent_id'  => $interchange->requester(),
                    'column_id' => $column->id()
                ));

            if (empty($time)) {
                $i['columns'][] = array(
                    'id' => $column->id(),
                    'from' => $column->starttime()->format('H:i:s'),
                    'to' => $column->endtime()->format('H:i:s'),
                );
            }

            if ($column->id() == $asked_time->column_id()){
                $times = $this->entityManager
                    ->getRepository(StatedWeekTimes::class)
                    ->findBy(array(
                        'column_id' => $column->id()
                    ));

                foreach ($times as $t) {
                    $agent = $this->entityManager->getRepository(Agent::class)->find($t->agent_id());
                    $name = $agent->nom() . ' ' . $agent->prenom();
                    if ($agent->statut() || $agent->service()) {
                        $name .= ' (';
                        if ($agent->statut()) {
                            $name .= $agent->statut();
                        }
                        if ($agent->service()) {
                            $name .= ' - ' . $agent->service();
                        }
                        $name .= ')';
                    }
                    $i['agents'][] = array(
                        'time' => $t->id(),
                        'name' => $name
                    );
                }
            }
        }

        $logged_in = $this->entityManager->getRepository(Agent::class)->find($_SESSION['login_id']);
        $can_accept = false;
        if ($logged_in->id() == $interchange->asked()) {
            $can_accept = true;
        }

        $can_validate = in_array(1301, $droits) ? true : false;


        $this->templateParams(array(
            'i'             => $i,
            'can_accept'    => $can_accept,
            'can_validate'  => $can_validate,
        ));

        return $this->output('interchange/add.html.twig');
    }

    /**
     * @Route("/interchange", name="interchange.save", methods={"POST"})
     */
    public function save(Request $request)
    {
        if ($request->get('id')) {
            $this->update($request);
            return $this->redirectToRoute('interchange.index');
        }

        $date = dateFr($request->get('date'));
        $planning = $this->getPlanningOn($date);

        $asked_time = $request->get('agent');

        $columns = $planning->columns();
        $requester_time;
        foreach ($columns as $column) {
            $time = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->findOneBy(array(
                    'agent_id'  => $_SESSION['login_id'],
                    'column_id' => $column->id()
                ));

            if (!empty($time)) {
                $requester_time = $time->id();
            }
        }

        $time = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->find($asked_time);

        $interchange = new Interchange();
        $interchange->planning($planning->id());
        $interchange->requester($_SESSION['login_id']);
        $interchange->requester_time($requester_time);
        $interchange->asked($time->agent_id());
        $interchange->asked_time($asked_time);
        $interchange->status('ASKED');

        $this->entityManager->persist($interchange);
        $this->entityManager->flush();

        return $this->redirectToRoute('interchange.index');
    }

    /**
     * @Route("/ajax/interchange/slots", name="interchange.slots", methods={"GET"})
     */
    public function getSlots(Request $request)
    {
        $response = new Response();

        $date = $request->get('date');
        $agent_id = $_SESSION['login_id'];

        $planning = $this->getPlanningOn($date);
        if (empty($planning)) {
            $response->setContent('no planning');
            $response->setStatusCode(404);
            return $response;
        }

        $columns = $planning->columns();
        $agent_found;
        $available_columns = array();
        foreach ($columns as $column) {

            $time = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->findOneBy(array(
                    'agent_id'  => $agent_id,
                    'column_id' => $column->id()
                ));

            if (!empty($time)) {
                $agent_found = $time->agent_id();
            } else {
                $available_columns[] = array(
                    'id' => $column->id(),
                    'from' => heure3($column->starttime()->format('H:i:s')),
                    'to' => heure3($column->endtime()->format('H:i:s')),
                );
            }
        }

        if (empty($agent_found)) {
            $response->setContent('no time');
            $response->setStatusCode(404);
            return $response;
        }

        return $this->json(array('agent' => $agent_found, 'available_times' => $available_columns));
    }

    /**
     * @Route("/ajax/interchange/agents", name="interchange.agents", methods={"GET"})
     */
    public function getAgents(Request $request)
    {
        $response = new Response();

        $column_id = $request->get('column_id');

        $times = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->findBy(array(
                'column_id' => $column_id
            ));

        if (empty($times)) {
            $response->setContent('no agent');
            $response->setStatusCode(404);
            return $response;
        }

        $agents = array();
        foreach ($times as $time) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($time->agent_id());
            $agents[] = array(
                'time_id' => $time->id(),
                'name' => $agent->prenom() . ' ' . $agent->nom(),
                'statut' => $agent->statut(),
                'service' => $agent->service()
            );
        }

        return $this->json($agents);
    }

    public function update(Request $request)
    {
        $id = $request->get('id');
        $action = $request->get('action');

        $interchange = $this->entityManager->getRepository(Interchange::class)->find($id);

        if ($action == 'Accepter') {
            $interchange->status('ACCEPTED');
        }

        if ($action == 'Refuser') {
            $interchange->status('REJECTED');
        }

        if ($action == 'Valider') {
            $interchange->status('VALIDATED');
        }

        $this->entityManager->persist($interchange);
        $this->entityManager->flush();
    }

    private function getPlanningOn($date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);

        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        return $planning;
    }
}
