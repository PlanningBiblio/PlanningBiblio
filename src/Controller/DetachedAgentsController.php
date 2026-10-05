<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Agent;
use App\Entity\Detached;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DetachedAgentsController extends BaseController
{
    #[Route(path: '/detached/{date?}', name: 'detached.index', methods: ['GET'])]
    public function index(Request $request): Response
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

    #[Route(path: '/detached', name: 'detached.add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->json('CSRF');
        }

        $date = $request->request->get('date');
        $dateTime = new DateTimeImmutable($date);
        $ids = $request->request->get('ids');
        $ids = json_decode($ids, true);

        try {
            $detached = $this->entityManager->getRepository(Detached::class)->findByDate($dateTime);
            foreach($detached as $d) {
                $this->entityManager->remove($d);
            }
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()]);
        }

        try {
            foreach ($ids as $id) {
                $detached = new Detached();
                $detached->setDate($dateTime);
                $detached->setUserId($id);
                $this->entityManager->persist($detached);
            }
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()]);
        }

        return $this->json('ok');
    }
}
