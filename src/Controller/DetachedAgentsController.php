<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Agent;
use App\Entity\Detached;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../legacy/Class/class.volants.php');

class DetachedAgentsController extends BaseController
{
    #[Route(path: '/detached/{date?}', name: 'detached.index', methods: ['GET'])]
    public function index(Request $request)
    {
        $date = $this->initDate('date', 'DetatchedAgentDate', 'monday this week', 'Y-m-d');

        $allAgents = $this->entityManager->getRepository(Agent::class)->get('Actif');
        $selectedAgents = $this->entityManager->getRepository(Detached::class)->findUserIds($date);

        $date = DateTimeImmutable::createFromInterface($date);

        $this->templateParams([
            'content_planning' => true,
            'date'             => $date,
            'start'            => $date->modify('monday this week'),
            'end'              => $date->modify('sunday this week'),
            'previousWeek'     => $date->modify('monday previous week')->format('Y-m-d'),
            'nextWeek'         => $date->modify('monday next week')->format('Y-m-d'),
            'allAgents'        => $allAgents,
            'selectedAgents'   => $selectedAgents,
        ]);

        return $this->output('detached/index.html.twig');
    }

    #[Route(path: '/detached/add', name: 'detached.add', methods: ['POST'])]
    public function add(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        if (!$this->csrf_protection($request)) {
            $session->set('AccessDeniedReason', 'CSRF');
            return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->request->get('CSRFToken');
        $date = $request->request->get('date');
        $ids = $request->request->get('ids');

        $ids = html_entity_decode($ids, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $ids = json_decode($ids, true);

        $v = new \volants();
        $v->set($date, $ids, $CSRFToken);

        if ($v->error) {
            return $this->json(array('error' => $v->error));
        }

        return $this->json('ok');
    }
}
