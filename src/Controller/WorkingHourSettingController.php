<?php

namespace App\Controller;

use App\Entity\WorkingHourCycle;
use App\PlanningBiblio\Helper\HourHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class WorkingHourSettingController extends BaseController
{
    #[Route(path: '/workinghour/settings', name: 'workinghour.settings', methods: ['GET'])]
    public function index(Request $request, Session $session) {

        if (!$this->checkACL()) {
            return $this->redirectToRoute('access-denied');
        }

        $end = $request->get('end', '');
        $start = $request->get('start', '');

        $cycles = $this->entityManager->getRepository(WorkingHourCycle::class)->findBetween($start, $end);

        $this->templateParams([
            'cycles' => $cycles,
            'end' => $end,
            'start' => $start,
        ]);

        return $this->output('/workinghour/settings/index.html.twig');
    }

    #[Route(path: '/workinghour/settings/{id<\d+>}', name: 'workinghour.settings.edit', methods: ['GET'])]
    public function edit(Request $request, Session $session) {

        if (!$this->checkACL()) {
            return $this->redirectToRoute('access-denied');
        }

        if ($request->get('id')) {
            $cycle = $this->entityManager->getRepository(WorkingHourCycle::class)->find($request->get('id'));
        } else {
            $cycle = new WorkingHourCycle();
        }

        $this->templateParams([
            'cycle' => $cycle,
        ]);

        return $this->output('/workinghour/settings/edit.html.twig');
    }

    #[Route(path: '/workinghour/settings', name: 'workinghour.settings.save', methods: ['POST'])]
    public function save(Request $request, Session $session) {

        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        if (!$this->checkACL()) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');
        $date = $request->get('date');
        $week = $request->get('week');

        $date = $date ? preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3-$2-$1", $date) : date('Y-m-d');

        if ($id) {
            $cycle = $this->entityManager->getRepository(WorkingHourCycle::class)->find($id);
        } else {
            $cycle = new WorkingHourCycle();
        }

        $cycle->setDate(new \DateTime($date));
        $cycle->setWeek($week);
        $this->entityManager->persist($cycle);
        $this->entityManager->flush();

        $notice = 'Les paramètres ont été enregistrés avec succés';
        $session->getFlashBag()->add('notice', $notice);

        return $this->redirectToRoute('workinghour.settings');
    }

    #[Route(path: '/workinghour/settings/delete', name: 'workinghour.settings.delete', methods: ['POST'])]
    public function delete(Request $request, Session $session) {

        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        if (!$this->checkACL()) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');

        if ($id) {
            $cycle = $this->entityManager->getRepository(WorkingHourCycle::class)->find($id);
            $this->entityManager->remove($cycle);
            $this->entityManager->flush();
        }

        $notice = 'Les paramètres ont été supprimés avec succés';
        $session->getFlashBag()->add('notice', $notice);

        return $this->redirectToRoute('workinghour.settings');
    }

    private function checkACL() {

        $admin = (in_array(1101, $_SESSION['droits']) or in_array(1201, $_SESSION['droits']));

        if (!$this->config('PlanningHebdo-resetCycles') or !$admin) {
            return false;
        }

        return true;
    }
}
