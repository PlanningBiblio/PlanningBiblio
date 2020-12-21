<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Position;
use App\Model\Skill;

require_once(__DIR__ . '/../../public/postes/class.postes.php');
require_once(__DIR__ . '/../../public/activites/class.activites.php');

class PositionController extends BaseController
{
    /**
     * @Route("/position", name="position.index", methods={"GET"})
     */
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

        // Sélection des étages
        $etagesTab = array();
        $db = new \db();
        $db->select("select_etages");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $etagesTab[$elem["id"]] = $elem["valeur"];
            }
        }

        // Sélection des groupes
        $groupesTab = array();
        $db = new \db();
        $db->select("select_groupes");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $groupesTab[$elem["id"]] = $elem["valeur"];
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

        $positions = array();

        foreach ($postes as $id => $value) {
            // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle

            $activites = array();
            $activitesAffichees = array();
            $activitesPoste = $value->activites();

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
            $activites = join(", ", $activites);
            $activitesAffichees = join(", ", $activitesAffichees);
            if (count($activitesPoste)>3) {
                $activitesAffichees.=" ...";
            }

            if ($nbMultisite>1) {
                $site = $this->config("Multisites-site{$value->site()}") ? $this->config("Multisites-site{$value->site()}") :"-";
                $new['site'] = $site;
            }
            $new['nom'] =  $value->nom();
            $new['activites'] = $activites;
            $new['activitesAffichees'] = $activitesAffichees;
            $new['id'] = $value->id();
            $new['groupe'] = $value->groupe() > 0 ? $groupesTab[$value->groupe()] : null;
            $new['etage'] = $value->etage() > 0 ? $etagesTab[$value->etage()] : null;
            $new['statistiques'] = $value->statistiques();
            $new['bloquant'] = $value->bloquant();
            $new['obligatoire'] = $value->obligatoire();
            $positions[] = $new;
        }

        $this->templateParams(array('positions'=> $positions));

        return $this->output('position/index.html.twig');
    }

    /**
     * @Route("/position/add", name="position.add", methods={"GET"})
     */
    public function add(Request $request)
    {
        $a = new \activites();
        $a->fetch();
        $actList = $a->elements;

        $db = new \db();
        $db->select2("select_categories", "*", "1", "order by rang");
        $categories_list = $db->result;

        $db = new \db();
        $db->select2("select_etages", "*", "1", "order by rang");
        $etages = $db->result;

        $db = new \db();
        $db->select2("select_groupes", "*", "1", "order by rang");
        $groupes = $db->result;

        $activites = $actList;
        $categories = $categories_list;

        $nbMultisite = $this->config('Multisites-nombre');
        $sites = array();
        $selectedSites = array();

        if ($nbMultisite>1) {
            for ($elem = 0; $elem < $nbMultisite +1; $elem++){
                if ($this->config("Multisites-site$elem")){
                    $sites[] = $this->config("Multisites-site$elem");
                    $selectedSites[] = null;
                }
            }
        }
        $nom ="";
        $groupe ="";
        $etage ="";
        $activites = "[]";
        $categories = "";
        $obligatoire = "checked";
        $bloq1 = "checked";
        $stat1 = "checked";



        $this->templateParams(array(
            'activites'      => $activites,
            'actList'        => $actList,
            'categories'     => $categories,
            'bloq1'          => $bloq1,
            'bloq2'          => null,
            'CSRFToken'      => $GLOBALS['CSRFSession'],
            'categoriesList' => $categories_list,
            'etage'          => $etage,
            'etages'         => $etages,
            'groupe'         => $groupe,
            'groupes'        => $groupes,
            'id'             => null,
            'multisite'      => $sites,
            'nbSites'        => $nbMultisite,
            'nom'            => $nom,
            'obligatoire'    => $obligatoire,
            'renfort'        => null,
            'selectedSites'  => $selectedSites,
            'stat1'          => $stat1,
            'stat2'          => null,
            'usedFloors'     => null,
            'usedGroups'     => null,

        ));
        return $this->output('position/edit.html.twig');
    }

    /**
     * @Route("/position/{id}", name="position.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        // Initialisation des variables
        $id = $request->get('id');
        $a = new \activites();
        $a->fetch();
        $actList = $a->elements;

        $position  =  $this->entityManager->getRepository(Position::class)->find($id);
        $nom =  $position->nom();
        $etage = $position->etage();
        $groupe = $position->groupe();
        $categories  =  $position->categories() ?  : array();
        $site = $position->site();
        $activites = $position->activites();
        $obligatoire = $position->obligatoire() =="Obligatoire"?"checked='checked'":"";
        $renfort = $position->obligatoire() == "Renfort"?"checked='checked'":"";
        $stat1 = $position->statistiques()?"checked='checked'":"";
        $stat2 = !$position->statistiques()?"checked='checked'":"";
        $bloq1 = $position->bloquant()?"checked='checked'":"";
        $bloq2 = !$position->bloquant()?"checked='checked'":"";

        $checked = null;

        // Recherche des étages
        $db = new \db();
        $db->select2("select_etages", "*", "1", "order by rang");
        $etages = $db->result;

        // Recherche des étages utilisés
        $etages_utilises = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a.etage')
           ->from(Position::class,'a')
           ->where($qb->expr()->isNull('a.supprime'))
           ->groupBy('a.etage');

        $query = $qb->getQuery();

        $response = $query->getResult();

        if ($response) {
            foreach ($response as $elem) {
                $etages_utilises[] = $elem['etage'];
            }
        }

        // Recherche des groupes
        $db = new \db;
        $db->select2("select_groupes", "*", "1", "order by rang");
        $groupes = $db->result;

        //Recherche des groupes utilisés
        $groupes_utilises = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.groupe')
           ->from(Position::class,'p')
           ->where($qb->expr()->isNull('p.supprime'))
           ->groupBy('p.groupe');

        $query = $qb->getQuery();

        $response = $query->getResult();
        if ($response) {
            foreach ($response as $elem) {
                $groupes_utilises[] = $elem['groupe'];
            }
        }

        // Recherche des catégories
        $db = new \db();
        $db->select2("select_categories", "*", "1", "order by rang");
        $categories_list = $db->result;

        $nbSites = $this->config('Multisites-nombre');
        $multisite = array();
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
            'categories'    => $categories,
            'site'          => $site,
            'activites'     => $activites,
            'obligatoire'   => $obligatoire,
            'renfort'       => $renfort,
            'stat1'         => $stat1,
            'stat2'         => $stat2,
            'bloq1'         => $bloq1,
            'bloq2'         => $bloq2,
            'etages'        => $etages,
            'groupes'       => $groupes,
            'nbSites'       => $nbSites,
            'multisite'     => $multisite,
            'actList'       => $actList,
            'categoriesList'=> $categories_list,
            'usedGroups'    => $groupes_utilises,
            'usedFloors'    => $etages_utilises,
            'selectedSites' => $selectedSites,
            'CSRFToken'     => $GLOBALS['CSRFSession']
            )
        );
        return $this->output('position/edit.html.twig');
    }

    /**
     * @Route("/position", name="position.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
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
            $site = $request->get('site');
            $bloquant = $request->get('bloquant');
            $statistiques = $request->get('statistiques');
            $etage = $request->get('etage');
            $groupe = $request->get('groupe');
            $obligatoire = $request->get('obligatoire');
            $site = $request->get('site', "");
            if (!$id){
                $position = new Position;
                $position->nom($nom);
                $position->activites($activites);
                $position->categories($categories);
                $position->bloquant($bloquant);
                $position->statistiques($statistiques);
                $position->etage($etage);
                $position->groupe($groupe);
                $position->obligatoire($obligatoire);
                $position->site($site);

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
                $position=$this->entityManager->getRepository(Position::class)->find($id);
                $position->nom($nom);
                $position->activites($activites);
                $position->categories($categories);
                $position->bloquant($bloquant);
                $position->statistiques($statistiques);
                $position->etage($etage);
                $position->groupe($groupe);
                $position->obligatoire($obligatoire);
                $position->site($site);

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

    /**
     * @Route("/position",name="position.delete", methods={"DELETE"})
     */
     public function delete_position(Request $request, Session $session){

        $id = $request->get('id');
        $p = $this->entityManager->getRepository(Position::class)->find($id);

        $date = new \DateTime();
        $p->supprime($date);
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
