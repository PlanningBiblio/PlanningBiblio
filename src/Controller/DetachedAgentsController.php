<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../public/planning/volants/class.volants.php');
require_once(__DIR__ . '/../../public/include/function.php');

class DetachedAgentsController extends BaseController
{
    /**
     * @Route("/detached", name="detached.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $date = $request->get('date');

        if (!$date) {
            $date = date('Y-m-d');
            if (!empty($_SESSION['oups']['volants_date'])) {
                $date = $_SESSION['oups']['volants_date'];
            }
        }

        $_SESSION['oups']['volants_date'] = $date;

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

    /**
     * @Route("/detached/add", name="detached.add", methods={"POST"})
     */
    public function add(Request $request)
    {
        $CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');
        $ids = $request->get('ids');

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