<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;



use App\Entity\AbsenceInfo;

class AbsenceInfoController extends Controller
{
    /**
     * @Route("/absences/info", name="absences.info.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $today = date('Ymd');

        $entityManager = $GLOBALS['entityManager'];

        $queryBuilder = $entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('a'))
            ->from(AbsenceInfo::class, 'a')
            ->where($queryBuilder->expr()->gte('a.fin', $today))
            ->orderBy('a.debut', 'ASC', 'a.fin', 'ASC')
            ->getQuery();

        $info = $query->getResult();

        $templates_params = array_merge(array('info' => $info), $GLOBALS['templates_params']);

        return $this->render('absenceInfo/index.html.twig', $templates_params);
    }

    /**
     * @Route("/absences/info/add", name="absences.info.add", methods={"GET"})
     */
    public function add(Request $request)
    {
        $templates_params['id'] = null;
        $templates_params['start'] = null;
        $templates_params['end'] = null;
        $templates_params['text'] = null;

        $templates_params = array_merge($templates_params, $GLOBALS['templates_params']);

        return $this->render('absenceInfo/edit.html.twig', $templates_params);
    }

    /**
     * @Route("/absences/info/{id}", name="absences.info.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $entityManager = $GLOBALS['entityManager'];

        $info = $entityManager->getRepository(AbsenceInfo::class)->findOneById($id);

        $templates_params['id'] = $id;
        $templates_params['start'] = date('d/m/Y', strtotime($info->debut()));
        $templates_params['end'] = date('d/m/Y', strtotime($info->fin()));
        $templates_params['text'] = $info->texte();

        $templates_params = array_merge($templates_params, $GLOBALS['templates_params']);

        return $this->render('absenceInfo/edit.html.twig', $templates_params);
    }

    /**
     * @Route("/absences/info", name="absences.info.update", methods={"POST"})
     */
    public function update(Request $request, Session $session)
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('csrf', $submittedToken)) {
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return $this->redirectToRoute('absences.info.index');
        }

        $id = $request->get('id');
        $start = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $request->get('start'));
        $end = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $request->get('end'));
        $text = trim($request->get('text'));

        $entityManager = $GLOBALS['entityManager'];

        if ($id) {
            $info = $entityManager->getRepository(AbsenceInfo::class)->find($id);
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

        $entityManager->persist($info);
        $entityManager->flush();

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('absences.info.index');
    }

    /**
     * @Route("/absences/info", name="absences.info.delete", methods={"DEL"})
     */
    public function delete(Request $request, Session $session)
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('csrf', $submittedToken)) {
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return $this->redirectToRoute('absences.info.index');
        }

        $id = $request->get('id');

        $entityManager = $GLOBALS['entityManager'];
        $info = $entityManager->getRepository(AbsenceInfo::class)->find($id);
        $entityManager->remove($info);
        $entityManager->flush();

        $flash = "L'information a bien été supprimée.";

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('absences.info.index');
    }

}
