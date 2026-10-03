<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../legacy/Class/class.volants.php');
require_once(__DIR__ . '/../../legacy/Common/function.php');

class DetachedAgentsController extends BaseController
{
    #[Route(path: '/detached/{date?}', name: 'detached.index', methods: ['GET'])]
    public function index(Request $request)
    {
        $date = $this->initDate('date', 'DetatchedAgentDate', 'last monday', 'Y-m-d');

        $date = $date->format('Y-m-d');
        $d = new \datePl($date);
        $date = $d->dates[0];
        $w = $d->semaine;
        $week = dateFr($d->dates[0])." au ".dateFr($d->dates[6]);

        // Previous week.
        $date1 = date('Y-m-d', strtotime($date.' -1 week'));

        // Next week
        $date2 = date('Y-m-d', strtotime($date.' +1 week'));

        // Agents disponibles et sélectionnés
        $v = new \volants();
        $v->fetch($date);
        $selected = $v->selected;
        $tous = $v->tous;

        $this->templateParams(array(
            'content_planning'  => true,
            'week_number'       => $w,
            'week'              => $week,
            'date'              => $date,
            'previous_week'     => $date1,
            'next_week'         => $date2,
            'detached_agents'   => $selected,
            'all_agents'        => $tous
        ));

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
