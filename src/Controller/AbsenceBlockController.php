<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AbsenceBlock;

class AbsenceBlockController extends BaseController
{
    /**
     * @Route("/absence/block", name="absence.block.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $today = date('Y-m-d');

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('a'))
            ->from(AbsenceBlock::class, 'a')
            ->orderBy('a.start', 'ASC', 'a.end', 'ASC')
            ->getQuery();

        $this->templateParams( array('block' => $query->getResult()) );

        return $this->output('absenceBlock/index.html.twig');
    }

    /**
     * @Route("/absence/block/add", name="absence.block.add", methods={"GET"})
     */
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

    /**
     * @Route("/absence/block/{id}", name="absence.block.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $block = $this->entityManager->getRepository(AbsenceBlock::class)->findOneById($id);

        $this->templateParams(array(
            'id'    => $id,
            'start' => date_format($block->start(), "d/m/Y"),
            'end'   => date_format($block->end(), "d/m/Y"),
            'text'  => $block->comment()
        ));

        return $this->output('absenceBlock/edit.html.twig');
    }

    /**
     * @Route("/absence/block", name="absence.block.update", methods={"POST"})
     */
    public function update(Request $request, Session $session)
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('csrf', $submittedToken)) {
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
            $block->start($start);
            $block->end($end);
            $block->comment($text);
            $flash = "Le blocage a bien été modifié.";
        } else {
            // Store a new block
            $block = new AbsenceBlock();
            $block->start($start);
            $block->end($end);
            $block->comment($text);
            $flash = "Le blocage a bien été enregistré.";
        }

        $this->entityManager->persist($block);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('absence.block.index');
    }

    /**
     * @Route("/absence/block", name="absence.block.delete", methods={"DELETE"})
     */
    public function delete(Request $request, Session $session)
    {

        // TODO: Pour la suppression, la spec demande d'ajouter le bouton de
        // suppression dans le tableau d'index.
        // Mais aussi de conserver le bouton de suppression lors de l'édition
        // Je ne crois pas qu'il y ait d'endroits dans Planno avec les deux
        // Lequel faut-il garder ?
        // (s'il faut garder les deux, il faut gérer une suppression en mode
        // post de formulaire, et une suppression en mode appel ajax)

        // TODO 2: différence entre csrf_protection et isCsrfTokenValid ?

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('csrf', $submittedToken)) {
            $session->getFlashBag()->add('error', 'CSRF Token Error');
            return $this->redirectToRoute('absence.block.index');
        }

        $id = $request->get('id');

        $block = $this->entityManager->getRepository(AbsenceBlock::class)->find($id);
        $this->entityManager->remove($block);
        $this->entityManager->flush();

        $flash = "Le blocage a bien été supprimé.";

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('absence.block.index');
    }
}
