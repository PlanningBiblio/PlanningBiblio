<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AbsenceInfo;

class AbsenceInfoController extends BaseController
{
    #[Route(path: '/absences/info', name: 'absences.info.index', methods: ['GET'])]
    public function index(Request $request, Session $session)
    {
        $today = date('Y-m-d');

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('a'))
            ->from(AbsenceInfo::class, 'a')
            ->where('a.fin >= :today')
            ->setParameter('today', $today)
            ->orderBy('a.debut', 'ASC', 'a.fin', 'ASC')
            ->getQuery();

        $this->templateParams( array('info' => $query->getResult()) );

        return $this->output('absenceInfo/index.html.twig');
    }

    #[Route(path: '/absences/info/add', name: 'absences.info.add', methods: ['GET'])]
    public function add(Request $request)
    {
        $this->templateParams(array(
            'id'    => null,
            'start' => null,
            'end'   => null,
            'text'  => null,
        ));

        return $this->output('absenceInfo/edit.html.twig');
    }

    #[Route(path: '/absences/info/{id}', name: 'absences.info.edit', methods: ['GET'])]
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AbsenceInfo::class)->findOneById($id);

        $this->templateParams(array(
            'id'    => $id,
            'start' => date_format($info->debut(), "d/m/Y"),
            'end'   => date_format($info->fin(), "d/m/Y"),
            'text'  => $info->texte()
        ));

        return $this->output('absenceInfo/edit.html.twig');
    }

    #[Route(path: '/absences/info', name: 'absences.info.update', methods: ['POST'])]
    public function update(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');
        $start = \DateTime::createFromFormat("d/m/Y", $request->get('start'));
        $end = \DateTime::createFromFormat("d/m/Y", $request->get('end'));

        if (empty($end)) {
          $end = $start;
        }

        $text = trim($request->get('text'));

        if ($id) {
            $info = $this->entityManager->getRepository(AbsenceInfo::class)->find($id);
            $info->debut($start);
            $info->fin($end);
            $info->texte($text);
            $flash = "L'information a bien été modifiée.";
        } else {
            // Store a new info
            $info = new AbsenceInfo();
            $info->debut($start);
            $info->fin($end);
            $info->texte($text);
            $flash = "L'information a bien été enregistrée.";
        }

        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('absences.info.index');
    }

    #[Route(path: '/absences/info', name: 'absences.info.delete', methods: ['DELETE'])]
    public function delete(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent(json_encode('CSRF error'));

            return $response;
        }

        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AbsenceInfo::class)->find($id);
        $this->entityManager->remove($info);
        $this->entityManager->flush();

        $flash = "L'information a bien été supprimée.";
        $session->getFlashBag()->add('notice', $flash);

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode('OK'));

        return $response;
    }

}
