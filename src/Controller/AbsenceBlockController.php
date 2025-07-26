<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\AbsenceBlock;

class AbsenceBlockController extends BaseController
{
    #[Route("/absence/block", name: "absence.block.index", methods: ["GET"])]
    public function index(Request $request, Session $session)
    {
        $blocks = $this->entityManager->getRepository(AbsenceBlock::class)
            ->findBy(
                array(),
                array('start' => 'ASC', 'end' => 'ASC'),
            );

        $this->templateParams( array('block' => $blocks) );

        return $this->output('absenceBlock/index.html.twig');
    }

    #[Route("/absence/block/add", name: "absence.block.add", methods: ["GET"])]
    public function add(Request $request)
    {
        $this->templateParams(array(
            'id'    => null,
            'start' => null,
            'end'   => null,
            'text'  => null,
        ));

        return $this->output('absenceBlock/edit.html.twig');
    }

    #[Route("/absence/block/{id<\d+>}", name: "absence.block.edit", methods: ["GET"])]
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $block = $this->entityManager->getRepository(AbsenceBlock::class)->find($id);

        $this->templateParams(array(
            'id'    => $id,
            'start' => date_format($block->getStart(), "d/m/Y"),
            'end'   => date_format($block->getEnd(), "d/m/Y"),
            'text'  => $block->getComment()
        ));

        return $this->output('absenceBlock/edit.html.twig');
    }

    #[Route("/absence/block", name: "absence.block.update", methods: ["POST"])]
    public function update(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return $this->redirectToRoute('absence.block.index');
        }

        $id = $request->get('id');
        $start = \DateTime::createFromFormat("d/m/Y", $request->get('start'));
        $end = \DateTime::createFromFormat("d/m/Y", $request->get('end'));

        if (empty($end)) {
          $end = $start;
        }

        $text = trim($request->get('text'));

        if ($id) {
            $block = $this->entityManager->getRepository(AbsenceBlock::class)->find($id);
            $block->setStart($start);
            $block->setEnd($end);
            $block->setComment($text);
            $flash = "Le blocage a bien été modifié.";
        } else {
            // Store a new block
            $block = new AbsenceBlock();
            $block->setStart($start);
            $block->setEnd($end);
            $block->setComment($text);
            $flash = "Le blocage a bien été enregistré.";
        }

        $this->entityManager->persist($block);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('absence.block.index');
    }

    #[Route("/absence/block", name: "absence.block.delete", methods: ["DELETE"])]
    public function delete(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            $response = new Response();
            $response->setStatusCode(403);
            $response->setContent(json_encode('CSRF error'));

            return $response;
        }

        $id = $request->get('id');

        $block = $this->entityManager->getRepository(AbsenceBlock::class)->find($id);
        $this->entityManager->remove($block);
        $this->entityManager->flush();

        $flash = "Le blocage a bien été supprimé.";
        $session->getFlashBag()->add('notice', $flash);

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent(json_encode('OK'));

        return $response;
    }
}
