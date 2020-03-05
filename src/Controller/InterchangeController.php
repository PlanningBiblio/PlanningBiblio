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
    private $CSRFToken;

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

            $requester_column = $this->entityManager
                ->getRepository(StatedWeekColumn::class)
                ->find($interchange->requester_time());

            $asked = $this->entityManager
                ->getRepository(Agent::class)
                ->find($interchange->asked());

            $asked_column = $this->entityManager
                ->getRepository(StatedWeekColumn::class)
                ->find($interchange->asked_time());

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
        $requester = $this->entityManager
          ->getRepository(Agent::class)->find($interchange->requester());
        $asked = $this->entityManager
          ->getRepository(Agent::class)->find($interchange->asked());

        $droits = $GLOBALS['droits'];

        $i = array();
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->find($interchange->planning());

        $i['id'] = $interchange->id();
        $i['requester'] = $requester->prenom() . ' ' . $requester->nom();
        $i['asked'] = $asked->prenom() . ' ' . $asked->nom();
        $i['date'] = $planning->date()->format('Y-m-d');
        $i['status'] = $interchange->status();

        $asked_column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->find($interchange->asked_time());
        $i['asked_start'] = $asked_column->starttime()->format('H:i:s');
        $i['asked_to'] = $asked_column->endtime()->format('H:i:s');

        $requester_column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->find($interchange->requester_time());
        $i['requester_start'] = $requester_column->starttime()->format('H:i:s');
        $i['requester_to'] = $requester_column->endtime()->format('H:i:s');

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
            'CSRFSession'   => $GLOBALS['CSRFSession']
        ));

        return $this->output('interchange/add.html.twig');
    }

    /**
     * @Route("/interchange", name="interchange.save", methods={"POST"})
     */
    public function save(Request $request)
    {
        $this->CSRFToken = $request->get('CSRFToken');

        if ($request->get('id')) {
            $this->update($request);
            return $this->redirectToRoute('interchange.index');
        }

        $date = dateFr($request->get('date'));
        $planning = $this->getPlanningOn($date);

        $asked_time = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->find($request->get('agent'));

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
                $requester_time = $time;
            }
        }

        $interchange = new Interchange();
        $interchange->planning($planning->id());
        $interchange->requester($_SESSION['login_id']);
        $interchange->requester_time($requester_time->column_id());
        $interchange->asked($asked_time->agent_id());
        $interchange->asked_time($request->get('column'));
        $interchange->status('ASKED');

        $this->entityManager->persist($interchange);
        $this->entityManager->flush();

        $this->notifyRequestedAgent($interchange);

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

        $this->notifyRequestedAgent($interchange);

        if ($interchange->status() == 'VALIDATED') {
            $this->apply($interchange);
        }
    }

    private function getPlanningOn($date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);

        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        return $planning;
    }

    private function notifyRequestedAgent(Interchange $interchange)
    {
        $logged_in = $this->entityManager->getRepository(Agent::class)->find($_SESSION['login_id']);

        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->find($interchange->planning());

        $requester = $this->entityManager
            ->getRepository(Agent::class)
            ->find($interchange->requester());

        $requester_column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->find($interchange->requester_time());

        $asked = $this->entityManager
            ->getRepository(Agent::class)
            ->find($interchange->asked());

        $asked_column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->find($interchange->asked_time());

        $id = $interchange->id();
        $replacements = array(
            '<<date>>'                  => dateAlpha($planning->date()->format('Y-m-d')),
            '<<requester.firstname>>'   => $requester->prenom(),
            '<<requester.surname>>'     => $requester->nom(),
            '<<requester.from>>'        => $requester_column->starttime()->format('H:i'),
            '<<requester.to>>'          => $requester_column->endtime()->format('H:i'),
            '<<asked.firstname>>'       => $asked->prenom(),
            '<<asked.surname>>'         => $asked->nom(),
            '<<asked.from>>'            => $asked_column->starttime()->format('H:i'),
            '<<asked.to>>'              => $asked_column->endtime()->format('H:i'),
            '<<url>>'                   => $this->getRequest()->getUriForPath("/interchange/$id")
        );

        $message = $this->config('interchange_mail_' . $interchange->status());

        foreach ($replacements as $search => $replace) {
            $message['subject'] = str_replace($search, $replace, $message['subject']);
            $message['content'] = str_replace($search, $replace, $message['content']);
        }

        $recipients = array();

        if ($interchange->status() == 'ASKED') {
            $recipients[] = $asked->mail();
        }

        if ($interchange->status() == 'ACCEPTED') {
            $site = $this->config('statedweek_site_filter');
            $recipients[] = $requester->mail();
            $recipients[] = $this->config("Multisites-site$site-mail");
        }

        if ($interchange->status() == 'REJECTED') {
            $recipients[] = $requester->mail();

            // Rejected by an admin. Notify also asked agent.
            if ($logged_in->id() != $asked->id()) {
                $recipients[] = $asked->mail();
            }
        }

        if ($interchange->status() == 'VALIDATED') {
            $recipients[] = $asked->mail();
            $recipients[] = $requester->mail();
        }

        $email = new \CJMail();
        $email->subject = $message['subject'];
        $email->message = $message['content'];
        $email->to = $asked->mail();
        $email->send();
    }

    private function apply(Interchange $interchange)
    {
        $requester = $this->entityManager
            ->getRepository(Agent::class)
            ->find($interchange->requester());

        $requester_column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->find($interchange->requester_time());

        $asked = $this->entityManager
            ->getRepository(Agent::class)
            ->find($interchange->asked());

        $asked_column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->find($interchange->asked_time());

        //Move requester.
        $requester_time = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->findOneBy(array(
                'agent_id' => $requester->id(),
                'column_id' => $requester_column->id()));

        $this->entityManager->remove($requester_time);
        $new_requester_time = new StatedWeekTimes();
        $new_requester_time->agent_id($requester->id());
        $new_requester_time->column_id($asked_column->id());
        $this->entityManager->persist($new_requester_time);
        $this->entityManager->flush();

        //Move asked agent.
        $asked_time = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->findOneBy(array(
                'agent_id' => $asked->id(),
                'column_id' => $asked_column->id()));

        $this->entityManager->remove($asked_time);
        $new_asked_time = new StatedWeekTimes();
        $new_asked_time->agent_id($asked->id());
        $new_asked_time->column_id($requester_column->id());
        $this->entityManager->persist($new_asked_time);
        $this->entityManager->flush();

        // Update working hours.
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->find($interchange->planning());
        if ($planning->locked()) {
            $date = $planning->date()->format('Y-m-d');
            $date_pl = new \datePl($date);
            $day_index = $date_pl->position - 1;

            $requester_working_hours = $requester->getWorkingHoursOn($date);
            $asked_working_hours = $asked->getWorkingHoursOn($date);
            $asked_time = $asked_working_hours['temps'][$day_index];
            $asked_breaktime = $asked_working_hours['breaktime'][$day_index];

            $asked_working_hours['temps'][$day_index]
                = $requester_working_hours['temps'][$day_index];
            $asked_working_hours['breaktime'][$day_index]
                = $requester_working_hours['breaktime'][$day_index];

            $requester_working_hours['temps'][$day_index]
                = $asked_time;
            $requester_working_hours['breaktime'][$day_index]
                = $asked_breaktime;

            if ($this->config('nb_semaine') > 1) {
                $asked_time = $asked_working_hours['temps'][$day_index + 7];
                $asked_breaktime = $asked_working_hours['breaktime'][$day_index + 7];

                $asked_working_hours['temps'][$day_index + 7]
                    = $requester_working_hours['temps'][$day_index + 7];
                $asked_working_hours['breaktime'][$day_index + 7]
                    = $requester_working_hours['breaktime'][$day_index + 7];

                $requester_working_hours['temps'][$day_index + 7]
                    = $asked_time;
                $requester_working_hours['breaktime'][$day_index + 7]
                    = $asked_breaktime;
            }

            if ($this->config('nb_semaine') > 2) {
                $asked_time = $asked_working_hours['temps'][$day_index + 14];
                $asked_breaktime = $asked_working_hours['breaktime'][$day_index + 14];

                $asked_working_hours['temps'][$day_index + 14]
                    = $requester_working_hours['temps'][$day_index + 14];
                $asked_working_hours['breaktime'][$day_index + 14]
                    = $requester_working_hours['breaktime'][$day_index + 14];

                $requester_working_hours['temps'][$day_index + 14]
                    = $asked_time;
                $requester_working_hours['breaktime'][$day_index + 14]
                    = $asked_breaktime;
            }

            $asked_working_hours['CSRFToken'] = $this->CSRFToken;
            $requester_working_hours['CSRFToken'] = $this->CSRFToken;

            $p = new \planningHebdo();
            $p->update($asked_working_hours);
            $p->update($requester_working_hours);
        }
    }
}
