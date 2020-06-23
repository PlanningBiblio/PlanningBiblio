<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AdminTemplate;
use App\Model\StatedWeekTemplate;

class AdminTemplateController extends BaseController
{
    /**
     * @Route("/admin/template", name="admin.template.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $templates = $this->entityManager->getRepository(AdminTemplate::class)->findAll();
        $statedweek_templates =  $this->entityManager->getRepository(StatedWeekTemplate::class)->findAll();

        $this->templateParams(array(
            'templates' => $templates,
            'statedweek_templates' => $statedweek_templates
        ));

        return $this->output('admin/template/index.html.twig');
    }

    /**
     * @Route("/admin/template", name="admin.template.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $id = $request->get('id');
        $name = $request->get('name');

        $template = $this->entityManager->getRepository(AdminTemplate::class)->find($id);
        $template->nom($name);

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle enregistré');
        return $this->redirectToRoute('admin.template.index');
    }

    /**
     * @Route("/admin/statedweektemplate", name="admin.statedweektemplate.save", methods={"POST"})
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
        return $this->redirectToRoute('admin.template.index');
    }

    /**
     * @Route("/admin/template/{id}", name="admin.template.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(AdminTemplate::class)->find($id);

        $this->templateParams(array( 'template'  => $template ));

        return $this->output('admin/template/edit.html.twig');
    }

    /**
     * @Route("/admin/statedweektemplate/{id}", name="admin.statedweektemplate.edit", methods={"GET"})
     */
    public function edit_statedweek(Request $request)
    {
        $id = $request->get('id');

        $template =  $this->entityManager->getRepository(StatedWeekTemplate::class)->find($id);

        $this->templateParams(array( 'template'  => $template ));

        return $this->output('admin/statedweektemplate/edit.html.twig');
    }

    /**
     * @Route("/admin/template/{id}", name="admin.template.delete", methods={"DEL"})
     */
    public function delete(Request $request, Session $session)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(AdminTemplate::class)->find($id);

        $this->entityManager->remove($template);
        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle supprimé');
        return $this->json(array('id' => $id));
    }

    /**
     * @Route("/admin/statedweektemplate/{id}", name="admin.statedweektemplate.delete", methods={"DEL"})
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
