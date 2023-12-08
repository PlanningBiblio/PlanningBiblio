<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use App\Model\Model;
use App\Model\ModelAgent;
use App\Model\PlanningPositionTab;

require_once(__DIR__ . '/../../public/include/db.php');

class ModelController extends BaseController
{

    use \App\Trait\FrameworkTrait;
    use \App\Trait\ModelTrait;

    #[Route(path: '/model', name: 'model.index', methods: ['GET'])]
    public function index(Request $request, Session $session)
    {
        $all_models = $this->entityManager->getRepository(Model::class)->findAll();

        $models = array();
        foreach ($all_models as $model) {
            if (!isset($models[$model->site() . $model->nom()])) {
                $models[$model->site() . $model->nom()] = array(
                    'name' => $model->nom(),
                    'week' => $model->jour() == 9 ? 0 : 1,
                    'id' => $model->model_id(),
                    'site' => $model->site()
                );
            }
        }

        $multi_sites = $this->config('Multisites-nombre') > 1 ? 1 : 0;
        $sites = array();
        if ($multi_sites) {
            for ($i=1; $i < $this->config('Multisites-nombre')+1; $i++) {
                $sites[$i] = $this->config("Multisites-site$i");
            }
        }

        $this->templateParams(array(
            'models' => $models,
            'multi_sites' => $multi_sites,
            'sites' => $sites,
            ));

        return $this->output('admin/model/index.html.twig');
    }

    #[Route(path: '/model', name: 'model.save', methods: ['POST'])]
    public function save(Request $request, Session $session)
    {
        $id = $request->get('id');
        $name = $request->get('name');

        $existing_name = $this->entityManager->getRepository(Model::class)
            ->findBy(array('nom' => $name));
        if ($existing_name) {
            $session->getFlashBag()->add('error', 'Ce nom est utilisé par un autre modèle');
            return $this->redirectToRoute('model.edit', array('id' => $id));
        }

        $models = $this->entityManager->getRepository(Model::class)
            ->findBy(array('model_id' => $id));

        foreach ($models as $model) {
            $model->nom($name);
            $this->entityManager->persist($model);
        }

        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle enregistré');
        return $this->redirectToRoute('model.index');
    }

    #[Route(path: '/model-add', name: 'model.add', methods: ['POST', 'GET'])]
    public function add(Request $request, Session $session)
    {
        $name = $request->get('name');
        $site = $request->get('site');
        $date = $request->get('date');
        $week = $request->get('week');
        $CSRFToken = $request->get('CSRFToken');
        $erase = $request->get('erase');

        $response = new Response();

        $droits = $GLOBALS['droits'];
        if (!in_array((300 + $site), $droits)) {
            $response->setContent('Forbidden');
            $response->setStatusCode(403);
        }

        $existing_models = $this->entityManager
            ->getRepository(Model::class)
            ->findBy(array('nom' => $name, 'site' => $site));

        // Warn user if the model exists.
        if ($existing_models && !$erase) {
            $response->setContent('model exists');
            $response->setStatusCode(200);
            return $response;
        }

        // Delete existing models
        $this->delete_model($existing_models, $CSRFToken);

        // Save the model
        $this->save_model($name, $date, $week, $site, $CSRFToken);

        $response->setContent('ok');
        $response->setStatusCode(200);
        return $response;
    }

    #[Route(path: '/model/{id<\d+>}', name: 'model.edit', methods: ['GET'])]
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(Model::class)
            ->findOneBy(array('model_id' => $id));

        $this->templateParams(array( 'template'  => $template ));

        return $this->output('admin/model/edit.html.twig');
    }


    #[Route(path: '/model/{id<\d+>}', name: 'model.delete', methods: ['DELETE'])]
    public function delete(Request $request, Session $session)
    {
        $id = $request->get('id');

        $models = $this->entityManager->getRepository(Model::class)
            ->findBy(array('model_id' => $id));

        $modelAgents = $this->entityManager->getRepository(ModelAgent::class)
            ->findBy(array('model_id' => $id));

        foreach ($models as $model) {
            $this->entityManager->remove($model);
        }
        foreach ($modelAgents as $modelAgent) {
            $this->entityManager->remove($modelAgent);
        }

        $this->entityManager->flush();

        $session->getFlashBag()->add('notice', 'Modèle supprimé');
        return $this->json(array('id' => $id));
    }

    /**
     * @Route("/model/{id<\d+>}/frameworks", name="model.frameworks", methods={"GET"})
     */
    public function frameworks(Request $request, Session $session)
    {
        $id = $request->get('id');

        // Do not offer to adapt the model if it has already been adapted
        $adaptationExists = $this->entityManager->getRepository(Model::class)->findOneBy(array('origin' => $id));

        if ($adaptationExists) {
            $content = array('copy' => 0);
        } else {
            $lastCopy = $this->getLatestFrameworkCopy($request->get('id'));
            $content = array('copy' => $lastCopy->copiesExist);
        }

        $response = new Response();
        $response->setContent(json_encode($content));
        $response->setStatusCode(200);

        return $response;
    }

}
