<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AdminInfo;

class AdminInfoController extends BaseController
{
    #[Route(path: '/admin/info', name: 'admin.info.index', methods: ['GET'])]
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

    #[Route(path: '/admin/info/add', name: 'admin.info.addform', methods: ['GET'])]
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

    #[Route(path: '/admin/info/{id}', name: 'admin.info.editform', methods: ['GET'])]
    public function editform(Request $request)
    {
        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AdminInfo::class)->findOneById($id);

        $this->templateParams(array(
            'id'    => $id,
            'start' => date('d/m/Y', strtotime($info->getStart())),
            'end'   => date('d/m/Y', strtotime($info->getEnd())),
            'text'  => $info->getComment(),
        ));

        return $this->output('adminInfo/edit.html.twig');
    }

    #[Route(path: '/admin/info', name: 'admin.info.update', methods: ['POST'])]
    public function update(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
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
            $info->setStart($start);
            $info->setEnd($end);
            $info->setComment($text);
            $flash = "L'information a bien été modifiée.";
        } else {
            // Store a new info
            $info = new AdminInfo();
            $info->setStart($start);
            $info->setEnd($end);
            $info->setComment($text);
            $flash = "L'information a bien été enregistrée.";
        }

        $this->entityManager->persist($info);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('admin.info.index');
    }

    #[Route(path: '/admin/info', name: 'admin.info.delete', methods: ['DELETE'])]
    public function delete(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent(json_encode('CSRF error'));

            return $response;
        }

        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);
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
