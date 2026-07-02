<?php

namespace App\Controller;

use App\Controller\BaseController;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\AdminInfo;

class AdminInfoController extends BaseController
{
    #[Route(path: '/admin/info', name: 'admin.info.index', methods: ['GET'])]
    public function index(Request $request, Session $session, EntityManagerInterface $em)
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $start_dt = $start ? DateTime::createFromFormat('d/m/Y', $start) : null;
        $end_dt = $end ? DateTime::createFromFormat('d/m/Y', $end) : null;

        if (!$start_dt && !$end_dt) {
            $start_dt = new DateTime('now');
        }

        /** @var \App\Repository\AdminInfoRepository */
        $repository = $em->getRepository(AdminInfo::class);

        $qb = $repository->createQueryBuilder('info');
        $repository->filterByDateRange($qb, $start_dt, $end_dt);

        $qb->orderBy('info.debut', 'ASC');
        $qb->addOrderBy('info.fin', 'ASC');

        $info = $qb->getQuery()->getResult();

        $this->templateParams([
            'info' => $info,
            'start' => $start_dt,
            'end' => $end_dt,
        ]);

        return $this->output('adminInfo/index.html.twig');
    }

    #[Route(path: '/admin/info/add', name: 'admin.info.add', methods: ['GET'])]
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

    #[Route(path: '/admin/info/{id<\d+>}', name: 'admin.info.edit', methods: ['GET'])]
    public function editform(Request $request)
    {
        $id = $request->get('id');

        $info = $this->entityManager->getRepository(AdminInfo::class)->find($id);

        $this->templateParams(array(
            'id'    => $id,
            'start' => date('d/m/Y', strtotime($info->getStart())),
            'end'   => date('d/m/Y', strtotime($info->getEnd())),
            'text'  => $info->getComment(),
        ));

        return $this->output('adminInfo/edit.html.twig');
    }

    #[Route(path: '/admin/info', name: 'admin.info.update', methods: ['POST'])]
    public function update(Request $request, Session $session): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');
        $start = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $request->get('start'));
        $end = preg_replace('/(\d+)\/(\d+)\/(\d+)/', "$3$2$1", $request->get('end'));
        $text = trim($request->get('text'));

        $htmlSanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())->allowSafeElements()
        );

        $text = $htmlSanitizer->sanitize($text);
        $text = html_entity_decode($text);

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
    public function delete(Request $request, Session $session): \Symfony\Component\HttpFoundation\Response
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
