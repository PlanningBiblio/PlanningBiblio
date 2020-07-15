<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Model;
use App\Model\StatedWeekTemplate;

class ModelController extends BaseController
{
    /**
     * @Route("/model", name="model.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $templates = $this->entityManager->getRepository(Model::class)->findAll();
        $statedweek_templates =  $this->entityManager->getRepository(StatedWeekTemplate::class)->findAll();

        $this->templateParams(array(
            'templates' => $templates,
            'statedweek_templates' => $statedweek_templates
        ));

        return $this->output('admin/model/index.html.twig');
    }

    /**
     * @Route("/model", name="model.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $id = $request->get('id');
        $name = $request->get('name');

        $template = $this->entityManager->getRepository(Model::class)->find($id);
        $template->nom($name);

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle enregistré');
        return $this->redirectToRoute('model.index');
    }

    /**
     * @Route("/statedweekmodel", name="statedweekmodel.save", methods={"POST"})
     */
    public function save_statedweek(Request $request, Session $session)
    {
        $id = $request->get('id');
        $name = $request->get('name');

        $template = $this->entityManager->getRepository(StatedWeekTemplate::class)->find($id);
        $template->name($name);

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle enregistré');
        return $this->redirectToRoute('model.index');
    }

    /**
     * @Route("/model/{id}", name="model.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(Model::class)->find($id);

        $this->templateParams(array( 'template'  => $template ));

        return $this->output('admin/model/edit.html.twig');
    }

    /**
     * @Route("/statedweekmodel/{id}", name="statedweekmodel.edit", methods={"GET"})
     */
    public function edit_statedweek(Request $request)
    {
        $id = $request->get('id');

        $template =  $this->entityManager->getRepository(StatedWeekTemplate::class)->find($id);

        $this->templateParams(array( 'template'  => $template ));

        return $this->output('admin/statedweekmodel/edit.html.twig');
    }

    /**
     * @Route("/model/{id}", name="model.delete", methods={"DEL"})
     */
    public function delete(Request $request, Session $session)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(Model::class)->find($id);

        $this->entityManager->remove($template);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle supprimé');
        return $this->json(array('id' => $id));
    }

    /**
     * @Route("/statedweekmodel/{id}", name="statedweekmodel.delete", methods={"DEL"})
     */
    public function delete_statedweek(Request $request, Session $session)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(StatedWeekTemplate::class)->find($id);

        $this->entityManager->remove($template);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle supprimé');
        return $this->json(array('id' => $id));
    }
}
