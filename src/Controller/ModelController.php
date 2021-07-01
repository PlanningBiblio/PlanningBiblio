<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

use App\Model\Model;
use App\Model\ModelAgent;

require_once(__DIR__ . '/../../public/include/db.php');

class ModelController extends BaseController
{
    /**
     * @Route("/model", name="model.index", methods={"GET"})
     */
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

    /**
     * @Route("/model", name="model.save", methods={"POST"})
     */
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

    /**
     * @Route("/model-add", name="model.add", methods={"POST", "GET"})
     */
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

        $existing = $this->entityManager
            ->getRepository(Model::class)
            ->findOneBy(array('nom' => $name, 'site' => $site));

        // Warn user if the model exists.
        if ($existing && !$erase) {
            $response->setContent('model exists');
            $response->setStatusCode(200);
            return $response;
        }

        // Erase model.
        if ($existing) {
            $select = new \db();
            $select->select2('pl_poste', '*', array('date' => $date, 'site' => $site));
            if ($select->result) {
                $delete = new \db();
                $delete->CSRFToken = $CSRFToken;
                $delete->delete('pl_poste_modeles', array('model_id' => $existing->id()));
                $delete = new \db();
                $delete->CSRFToken = $CSRFToken;
                $delete->delete('pl_poste_modeles_tab', array('model_id' => $existing->id()));
            }
        }

        $this->save_model($name, $date, $week, $site, $CSRFToken);

        $response->setContent('ok');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/model/{id}", name="model.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $template = $this->entityManager->getRepository(Model::class)
            ->findOneBy(array('model_id' => $id));

        $this->templateParams(array( 'template'  => $template ));

        return $this->output('admin/model/edit.html.twig');
    }


    /**
     * @Route("/model/{id}", name="model.delete", methods={"DEL"})
     */
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

  public function save_model($nom, $date, $semaine, $site, $CSRFToken)
  {
      $dbprefix=$GLOBALS['config']['dbprefix'];
      $d = new \datePl($date);

      $tab_db = null;
      $select = null;

      // Select data between monday and sunday
      // for the current week.
      if ($semaine) {
          // Select tables structures
          $tab_db = new \db();
          $tab_db->select2('pl_poste_tab_affect', '*', array(
              'date' => "BETWEEN{$d->dates[0]}AND{$d->dates[6]}",
              'site' => $site)
          );

          // Select agents put on cells.
          $select = new \db();
          $select->select2('pl_poste', '*', array(
              'date' => "BETWEEN{$d->dates[0]}AND{$d->dates[6]}",
              'site' => $site)
          );
      }
      // Select data of current day.
      else {
          // Select table's structure
          $tab_db = new \db();
          $tab_db->select2('pl_poste_tab_affect', '*', array(
              'date' => $date,
              'site' => $site)
          );

          // Select agents put on cells.
          $select = new \db();
          $select->select2('pl_poste', '*', array(
              'date' => $date,
              'site' => $site)
          );
      }

      if ($select->result and $tab_db->result) {
          // Model_id
          $db = new \db();
          $db->query('select MAX(`model_id`) AS `model` FROM `pl_poste_modeles_tab`;');
          $model = $db->result ? $db->result[0]['model'] + 1 : 1;

          $values = array();
          foreach ($select->result as $elem) {
              $jour=""; // $jour keeps null if we import only a day.
              if ($semaine) {
                  $d = new \datePl($elem['date']);
                  $jour = $d->position; // Week's day position (1=Monday , 2=Tuesday ...)
                  if ($jour == 0) {
                      $jour = 7;
                  }
              }
              $values[] = array(
                  ':model_id' => $model,
                  ':perso_id' => $elem['perso_id'],
                  ':poste' => $elem['poste'],
                  ':debut' => $elem['debut'],
                  ':fin' => $elem['fin'],
                  ':jour' => $jour,
                  ':site' => $site,
              );
          }

          $dbh = new \dbh();
          $dbh->CSRFToken = $CSRFToken;
          $dbh->prepare("INSERT INTO `{$dbprefix}pl_poste_modeles` (`model_id`, `perso_id`, `poste`, `debut`, `fin`, `jour`, `site`) VALUES (:model_id, :perso_id, :poste, :debut, :fin, :jour, :site);");
          foreach ($values as $value) {
              $dbh->execute($value);
          }

          foreach ($tab_db->result as $elem) {
              $jour = 9; // 9 means day of week is not specified.
              if ($semaine) {
                  $d = new \datePl($elem['date']);
                  $jour=$d->position; // Week's day position (1=Monday , 2=Tuesday ...)
                  if ($jour == 0) {
                      $jour = 7;
                  }
              }
              $insert = array(
                  'model_id' => $model,
                  'nom' => $nom,
                  'jour' => $jour,
                  'tableau' => $elem['tableau'],
                  'site' => $site
              );

              $db = new \db();
              $db->CSRFToken = $CSRFToken;
              $db->insert('pl_poste_modeles_tab', $insert);
          }
      }
      //echo "Modèle \"$nom\" enregistré<br/><br/>\n";
  }
}
