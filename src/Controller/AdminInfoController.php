<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AdminInfo;

class AdminInfoController extends BaseController
{
    /**
     * @Route("/admin/info", name="admin.info.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $today = date('Ymd');

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('a'))
            ->from(AdminInfo::class, 'a')
            ->where($queryBuilder->expr()->gte('a.fin', $today))
            ->orderBy('a.debut', 'ASC', 'a.fin', 'ASC')
            ->getQuery();

        $this->templateParams( array('info' => $query->getResult()) );


        return $this->output('adminInfo/index.html.twig');
    }

    /**
     * @Route("/admin/info/add", name="admin.info.add", methods={"POST"})
     */
    public function add(Request $request, Session $session)
    {
        return $this->update($request, $session);
    }

    /**
     * @Route("/admin/info/add", name="admin.info.addform", methods={"GET"})
     */
    public function addform(Request $request)
    {
        $this->templateParams(array(
            'id'    => null,
            'start' => null,
            'end'   => null,
            'text'  => null,
        ));

        return $this->output('adminInfo/edit.html.twig');
    }

    /**
     * @Route("/admin/info/{id}", name="admin.info.editform", methods={"GET"})
     */
    public function editform(Request $request)
    {
        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AdminInfo::class)->findOneById($id);

        $this->templateParams(array(
            'id'    => $id,
            'start' => date('d/m/Y', strtotime($info->debut())),
            'end'   => date('d/m/Y', strtotime($info->fin())),
            'text'  => $info->texte()
        ));

        return $this->output('adminInfo/edit.html.twig');
    }

    /**
     * @Route("/admin/info/{id}", name="admin.info.update", methods={"POST"})
     */
    public function update(Request $request, Session $session)
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('csrf', $submittedToken)) {
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return $this->redirectToRoute('admin.info.index');
        }

        $id = $request->get('id');
        $start = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $request->get('start'));
        $end = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $request->get('end'));
        $text = trim($request->get('text'));

        if (empty($end)) {
          $end = $start;
        }

        if ($id) {
            $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
            $info->debut($start);
            $info->fin($end);
            $info->texte($text);
            $flash = "L'information a bien été modifiée.";
        } else {
            // Store a new info
            $info = new AdminInfo();
            $info->debut($start);
            $info->fin($end);
            $info->texte($text);
            $flash = "L'information a bien été enregistrée.";
        }

        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('admin.info.index');
    }

    /**
     * @Route("/admin/info/{id}", name="admin.info.delete", methods={"DEL"})
     */
    public function delete(Request $request, Session $session)
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('csrf', $submittedToken)) {
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return $this->redirectToRoute('admin.info.index');
        }

        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
        $this->entityManager->remove($info);
        $this->entityManager->flush();

        $flash = "L'information a bien été supprimée.";

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('admin.info.index');
    }
}
