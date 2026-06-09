<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Position;
use App\Entity\SelectFloor;
use App\Entity\SelectGroup;
use App\Entity\Skill;

require_once(__DIR__ . '/../../legacy/Class/class.postes.php');
require_once(__DIR__ . '/../../legacy/Class/class.activites.php');

class PositionController extends BaseController
{
    #[Route(path: '/position', name: 'position.index', methods: ['GET'])]
    public function index(Request $request)
    {
        //            Affichage de la liste des postes
        $groupe = "Tous";
        $this->templateParams(array('groupe' =>$groupe));

        $nom = $request->get('nom');

        // Contrôle si le poste est utilisé dans un tableau non-supprimé (tables pl_poste_lignes et pl_poste_tab)
        $postes_utilises = array();

        $db = new \db();
        $db->selectInnerJoin(array("pl_poste_lignes","numero"), array("pl_poste_tab","tableau"), array(array("name"=>"poste", "as"=>"poste")), array(), array("type"=>"poste"), array("supprime"=>null));
        if ($db->result) {
            foreach ($db->result as $elem) {
                $postes_utilises[] = $elem['poste'];
              }
        }
        // Sélection des activités
        $activitesTab = array();
        $db = new \db();
        $db->select("activites");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $activitesTab[$elem["id"]] = $elem["nom"];
            }
        }

        $p = $this->entityManager->getRepository(Position::class)->findBy(array('supprime' => NULL), array('nom'=>'ASC'));
        $postes = array();
        foreach($p as $poste){
            $postes[] = $poste;
        }

        $nbMultisite = $this->config('Multisites-nombre');
        $this->templateParams(array(
            'multisite'     => $nbMultisite,
            'usedPositions' => $postes_utilises,
            'CSRFSession'   => $GLOBALS['CSRFSession']
        ));

        // Floors and groups
        $floors = $this->entityManager->getRepository(SelectFloor::class);
        $groups = $this->entityManager->getRepository(SelectGroup::class);

        $positions = array();

        foreach ($postes as $value) {
            // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle

            $activites = array();
            $activitesAffichees = array();
            $activitesPoste = $value->getActivities();

            if (is_array($activitesPoste)) {
                foreach ($activitesPoste as $act) {
                    if (array_key_exists($act, $activitesTab)) {
                        $activites[] = $activitesTab[$act];
                        if (count($activitesAffichees)<3) {
                            $activitesAffichees[] = $activitesTab[$act];
                        }
                    }
                }
            }
            $activites = implode(", ", $activites);
            $activitesAffichees = implode(", ", $activitesAffichees);
            if (count($activitesPoste)>3) {
                $activitesAffichees.=" ...";
            }

            if ($nbMultisite>1) {
                $site = $this->config("Multisites-site{$value->getSite()}") ? $this->config("Multisites-site{$value->getSite()}") :"-";
                $new['site'] = $site;
            }
            $new['nom'] =  $value->getName();
            $new['activites'] = $activites;
            $new['activitesAffichees'] = $activitesAffichees;
            $new['id'] = $value->getId();
            $new['groupe'] = $groups->find($value->getGroup()) ? $groups->find($value->getGroup())->getValue() : null;
            $new['etage'] = $floors->find($value->getFloor()) ? $floors->find($value->getFloor())->getValue() : null;
            $new['statistiques'] = $value->isStatistics();
            $new['bloquant'] = $value->isBlocking();
            $new['obligatoire'] = $value->getMandatory();
            $positions[] = $new;
        }

        $this->templateParams(array('positions'=> $positions));

        return $this->output('position/index.html.twig');
    }

    #[Route(path: '/position/add', name: 'position.add', methods: ['GET'])]
    #[Route(path: '/position/{id}', name: 'position.edit', methods: ['GET'])]
    public function edit(Request $request)
    {
        // Initialisation des variables
        $id = $request->get('id');

        if (is_numeric($id)) {
            $position  =  $this->entityManager->getRepository(Position::class)->find($id);
            $nom =  $position->getName();
            $etage = $position->getFloor();
            $groupe = $position->getGroup();
            $groupe_id = $position->getGroupId();
            $categories  =  $position->getCategories() ?  : array();
            $site = $position->getSite();
            $activites = $position->getActivities();
            $obligatoire = $position->getMandatory() == 'Obligatoire' ? 'checked="checked"' : '';
            $renfort = $position->getMandatory() == 'Renfort' ? 'checked="checked"' : '';
            $stat1 = $position->isStatistics() ? 'checked="checked"' : '';
            $stat2 = !$position->isStatistics() ? 'checked="checked"' : '';
            $quota_sp1 = $position->isQuotaSP() ? 'checked="checked"' : '';
            $quota_sp2 = !$position->isQuotaSP() ? 'checked="checked"' : '';
            $bloq1 = $position->isBlocking() ? 'checked="checked"' : '';
            $bloq2 = !$position->isBlocking() ? 'checked="checked"' : '';
            $teleworking1 = $position->isTeleworking() ? 'checked="checked"' : '';
            $teleworking2 = !$position->isTeleworking() ? 'checked="checked"' : '';
            $lunch1 = $position->isLunch() ? 'checked="checked"' : '';
            $lunch2 = !$position->isLunch() ? 'checked="checked"' : '';
        } else {
            $id = null;
            $nom =  null;
            $etage = null;
            $groupe = null;
            $groupe_id = null;
            $categories = array();
            $site = null;
            $activites = array();
            $obligatoire = 'checked';
            $renfort = null;
            $stat1 = 'checked';
            $stat2 = null;
            $quota_sp1 = 'checked';
            $quota_sp2 = null;
            $bloq1 = 'checked';
            $bloq2 = null;
            $teleworking1 = null;
            $teleworking2 = 'checked';
            $lunch1 = null;
            $lunch2 = 'checked';
        }

        // Floors, groups and skills
        $floors = $this->entityManager->getRepository(SelectFloor::class)->findBy([], ['rang' => 'ASC']);
        $groups = $this->entityManager->getRepository(SelectGroup::class)->findBy([], ['rang' => 'ASC']);
        $skill_list = $this->entityManager->getRepository(Skill::class)->findBy(['supprime' => null], ['nom' => 'ASC']);

        // Used floors
        $used_floors = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a.etage')
           ->from(Position::class,'a')
           ->where($qb->expr()->isNull('a.supprime'))
           ->groupBy('a.etage');

        $query = $qb->getQuery();

        $response = $query->getResult();

        if ($response) {
            foreach ($response as $elem) {
                $used_floors[] = $elem['etage'];
            }
        }

        // Used groups
        $used_groups = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.groupe')
           ->from(Position::class,'p')
           ->where($qb->expr()->isNull('p.supprime'))
           ->groupBy('p.groupe');

        $query = $qb->getQuery();

        $response = $query->getResult();
        if ($response) {
            foreach ($response as $elem) {
                $used_groups[] = $elem['groupe'];
            }
        }

        // Recherche des catégories
        $db = new \db();
        $db->select2("select_categories", "*", "1", "order by rang");
        $categories_list = $db->result;

        $nbSites = $this->config('Multisites-nombre');
        $multisite = array();
        $selectedSites = array();

        if ($nbSites>1){
            for ($i = 1; $i<= $nbSites; $i++) {
                $selected = $site==$i?"selected='selected'":null;
                $multisite[] = $this->config("Multisites-site{$i}");
                $selectedSites[] = $selected;
            }
        }

        $this->templateParams(array(
            'id'            => $id,
            'nom'           => $nom,
            'etage'         => $etage,
            'groupe'        => $groupe,
            'group_id'      => $groupe_id,
            'categories'    => $categories,
            'site'          => $site,
            'activites'     => $activites,
            'obligatoire'   => $obligatoire,
            'renfort'       => $renfort,
            'stat1'         => $stat1,
            'stat2'         => $stat2,
            'teleworking1'  => $teleworking1,
            'teleworking2'  => $teleworking2,
            'lunch1'        => $lunch1,
            'lunch2'        => $lunch2,
            'bloq1'         => $bloq1,
            'bloq2'         => $bloq2,
            'quota_sp1'     => $quota_sp1,
            'quota_sp2'     => $quota_sp2,
            'floors'        => $floors,
            'groups'        => $groups,
            'nbSites'       => $nbSites,
            'multisite'     => $multisite,
            'skillList'     => $skill_list,
            'categoriesList'=> $categories_list,
            'usedGroups'    => $used_groups,
            'usedFloors'    => $used_floors,
            'selectedSites' => $selectedSites,
            'CSRFToken'     => $GLOBALS['CSRFSession']
            )
        );
        return $this->output('position/edit.html.twig');
    }

    #[Route(path: '/position', name: 'position.save', methods: ['POST'])]
    public function save(Request $request, Session $session): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $CSRFToken = $request->get('CSRFToken');
        $nom = $request->get('nom');
        $id = $request->get('id');

        if (!$nom) {
            $session->getFlashBag()->add('error',"Le nom est obligatoire");
            if(!$id){
                return $this->redirectToRoute('position.add');
            } else {
                return $this->redirectToRoute('position.edit', array('id' => $id));
            }
        } else {

            $activites = $request->get('activites', []);
            $categories = $request->get('categories', []);
            $bloquant = $request->get('bloquant', true);
            $quota_sp = $request->get('quota_sp', true);
            $lunch = $request->get('lunch', false);
            $statistiques = (bool) $request->get('statistiques', true);
            $teleworking = $request->get('teleworking', false);
            $etage = $request->get('etage',"");
            $groupe = $request->get('groupe', "");
            $groupe_id = (int) $request->get('group_id', "");
            $obligatoire = $request->get('obligatoire', 'Obligatoire');
            $site = (int) $request->get('site', 1);

            if (!$id){
                $position = new Position;
                $position->setName($nom);
                $position->setActivities($activites);
                $position->setCategories($categories);
                $position->setBlocking($bloquant);
                $position->setQuotaSP($quota_sp);
                $position->setStatistics($statistiques);
                $position->setTeleworking($teleworking);
                $position->setLunch($lunch);
                $position->setFloor($etage);
                $position->setGroup($groupe);
                $position->setGroupId($groupe_id);
                $position->setMandatory($obligatoire);
                $position->setSite($site);

                try{
                    $this->entityManager->persist($position);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }

                if (isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'ajout du poste " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice', "Le poste a été ajouté avec succès");
                }

            } else {
                $position = $this->entityManager->getRepository(Position::class)->find($id);
                $position->setName($nom);
                $position->setActivities($activites);
                $position->setCategories($categories);
                $position->setBlocking($bloquant);
                $position->setQuotaSP($quota_sp);
                $position->setStatistics($statistiques);
                $position->setTeleworking($teleworking);
                $position->setLunch($lunch);
                $position->setFloor($etage);
                $position->setGroup($groupe);
                $position->setGroupId($groupe_id);
                $position->setMandatory($obligatoire);
                $position->setSite($site);

                try{
                    $this->entityManager->persist($position);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }

                if(isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de la modification du poste " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice',"Le poste a été modifié avec succès");
                }
            }
        }
    
        return $this->redirectToRoute('position.index');
    }

    #[Route(path: '/position', name: 'position.delete', methods: ['DELETE'])]
     public function delete_position(Request $request, Session $session): \Symfony\Component\HttpFoundation\JsonResponse{

        $id = $request->get('id');
        $p = $this->entityManager->getRepository(Position::class)->find($id);

        $date = new \DateTime();
        $p->setDelete($date);
        try{
            $this->entityManager->persist($p);
            $this->entityManager->flush();
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }

        if(isset($error)) {
            $session->getFlashBag()->add('error', "Une erreur est survenue lors de la suppression du poste " );
            $this->logger->error($error);
        } else {
            $session->getFlashBag()->add('notice',"Le poste a bien été supprimé");
            return $this->json("Ok");
        }
     }

}
