<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Position;

require_once(__DIR__ . '/../../public/postes/class.postes.php');
require_once(__DIR__ . '/../../public/activites/class.activites.php');

class AdminPositionController extends BaseController
{
    /**
     * @Route("/position", name="position.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        //            Affichage de la liste des postes
        $groupe="Tous";
        $this->templateParams(array('groupe' =>$groupe));

        $nom=$request->get('nom');

        // Contrôle si le poste est utilisé dans un tableau non-supprimé (tables pl_poste_lignes et pl_poste_tab)
        $postes_utilises=array();

        $db=new \db();
        $db->selectInnerJoin(array("pl_poste_lignes","numero"), array("pl_poste_tab","tableau"), array(array("name"=>"poste", "as"=>"poste")), array(), array("type"=>"poste"), array("supprime"=>null));
        if ($db->result) {
            foreach ($db->result as $elem) {
                $postes_utilises[]=$elem['poste'];
              }
        }
        // Sélection des activités
        $activitesTab=array();
        $db=new \db();
        $db->select("activites");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $activitesTab[$elem["id"]]=$elem["nom"];
            }
        }

        $p=$this->entityManager->getRepository(Position::class)->findBy(array('supprime' => NULL), array('nom'=>'ASC'));
        $postes = array();
        foreach($p as $poste){
            $postes[]=$poste;
        }

        $nbMultisite = $this->config('Multisites-nombre');
        $this->templateParams(array(
            'multisite' =>$nbMultisite,
            'usedPositions' => $postes_utilises,
            'CSRFSession' => $GLOBALS['CSRFSession']
        ));

        $positions = array();

        foreach ($postes as $id => $value) {
            // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle

            $activites=array();
            $activitesAffichees=array();
            $activitesPoste=$value->activites();

            if (is_array($activitesPoste)) {
                foreach ($activitesPoste as $act) {
                    if (array_key_exists($act, $activitesTab)) {
                        $activites[]=$activitesTab[$act];
                        if (count($activitesAffichees)<3) {
                            $activitesAffichees[]=$activitesTab[$act];
                        }
                    }
                }
            }
            $activites=join(", ", $activites);
            $activitesAffichees=join(", ", $activitesAffichees);
            if (count($activitesPoste)>3) {
                $activitesAffichees.=" ...";
            }

            if ($nbMultisite>1) {
                $site = $this->config("Multisites-site{$value->site()}") ? $this->config("Multisites-site{$value->site()}") :"-";
                $new['site']=$site;
            }
            $new['nom']=html_entity_decode($value->nom(), ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $new['activites']=$activites;
            $new['activitesAffichees']=html_entity_decode($activitesAffichees, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $new['id']=$value->id();
            $new['groupe']=$value->groupe();
            $new['etage']=$value->etage();
            $new['statistiques']=$value->statistiques();
            $new['bloquant']=$value->bloquant();
            $new['obligatoire']=$value->obligatoire();
            $positions[]=$new;
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
        $actList=$a->elements;

        $db = new \db();
        $db->select2("select_categories", "*", "1", "order by rang");
        $categories_list = $db->result;

        $db= new \db();
        $db->select2("select_etages", "*", "1", "order by rang");
        $etages=$db->result;

        $db= new \db();
        $db->select2("select_groupes", "*", "1", "order by rang");
        $groupes = $db->result;

        $activites = $actList;
        $categories = $categories_list;

        $nbMultisite = $this->config('Multisites-nombre');
        $sites = array();

        if ($nbMultisite>1) {
            for ($elem = 0; $elem < $nbMultisite +1; $elem++){
                if ($this->config("Multisites-site$elem")){
                    $sites[]=$this->config("Multisites-site$elem");
                }
            }
        }
        $groupe_id  = '0';
        $obligatoire = "NULL";
        $bloquant = "NULL";

        $this->templateParams(array(
            'CSRFToken'=> $GLOBALS['CSRFSession'],
            'activites' => $activites,
            'actList' => $actList,
            'categories' => $categories,
            'categoriesList' => $categories_list,
            'etages' => $etages,
            'groupes'=> $groupes,
            'group-id' => $groupe_id,
            'obligatoire' => $obligatoire,
            'bloquant' => $bloquant,
            'nbSites' => $nbMultisite,
            'multisite' => $sites
        ));
        return $this->output('position/edit.html.twig');
    }

    /**
     * @Route("/position/{id}", name="position.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        // Initialisation des variables
        $id=$request->get('id');
        $a=new \activites();
        $a->fetch();
        $actList=$a->elements;

        $position = $this->entityManager->getRepository(Position::class)->find($id);
        $nom=$position->nom();
        $etage=$position->etage();
        $groupe=$position->groupe();
        $groupe_id=$position->groupe_id();
        $categories = $position->categories() ?  : array();
        $site=$position->site();
        $activites=$position->activites();
        $obligatoire=$position->obligatoire()=="Obligatoire"?"checked='checked'":"";
        $renfort=$position->obligatoire()=="Renfort"?"checked='checked'":"";
        $stat1=$position->statistiques()?"checked='checked'":"";
        $stat2=!$position->statistiques()?"checked='checked'":"";
        $bloq1=$position->bloquant()?"checked='checked'":"";
        $bloq2=!$position->bloquant()?"checked='checked'":"";

        $checked=null;
        // Recherche des étages
        $db=new \db();
        $db->select2("select_etages", "*", "1", "order by rang");
        $etages=$db->result;

        // Recherche des étages utilisés
        $etages_utilises = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->add('select','etage')
           ->add('from', 'postes etage')
           ->add('where', 'supprime = null')
           ->add('groupBy', 'etage');

        $response = $qb->getQuery();

        if ($response) {
            foreach ($response as $elem) {
                $etages_utilises[] = $elem->etage();
            }
        }

        // Recherche des groupes
        $db=new \db;
        $db->select2("select_groupes", "*", "1", "order by rang");
        $groupes=$db->result;

        //Recherche des groupes utilisés
        $groupes_utilises = array();

        $qb = $this->entityManager->createQueryBuilder();
        $qb->add('select','groupe')
           ->add('from', 'postes groupe')
           ->add('where', 'supprime = null')
           ->add('groupBy', 'groupe');

        $response = $qb->getQuery();
        if ($response) {
            foreach ($response as $elem) {
                $groupes_utilises[] = $elem->groupe();
            }
        }

        // Recherche des catégories
        $db=new \db();
        $db->select2("select_categories", "*", "1", "order by rang");
        $categories_list=$db->result;

        $nbSites = $this->config('Multisites-nombre');
        $multisite=array();
        if ($nbSites>1){
            for ($i=1;$i<=$nbSites;$i++) {
                $selected=$site==$i?"selected='selected'":null;
                $multisite[]=$this->config("Multisites-site{$i}");
                $selectedSites[]=$selected;
            }
        }

        $this->templateParams(array(
            'id' => $id,
            'nom' => $nom,
            'etage' => $etage,
            'groupe' => $groupe,
            'group-id'=>$groupe_id,
            'categories' =>$categories,
            'site' => $site,
            'activites' => $activites,
            'obligatoire'=> $obligatoire,
            'renfort' => $renfort,
            'stat1' => $stat1,
            'stat2' => $stat2,
            'bloq1' => $bloq1,
            'bloq2' => $bloq2,
            'etages' => $etages,
            'groupes' => $groupes,
            'nbSites'=> $nbSites,
            'multisite'=>$multisite,
            'actList' => $actList,
            'categoriesList'=>$categories_list,
            'usedGroups'=> $groupes_utilises,
            'selectedSites' => $selectedSites,
            'CSRFToken' => $GLOBALS['CSRFSession']
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
        $nom=$request->get('nom');


        if (!$nom) {
            $session->getFlashBag()->add('error',"Le nom est obligatoire");
            if(!$id){
                return $this->redirectToRoute('position.add');
            } else {
                return $this->redirectToRoute('position.edit', array('id' => $id));
            }
        }else{

            $activites = json_encode($request->get('activites'));
			$categories = json_encode($request->get('categories'));
            $id = $request->get('id');
            $site = $request->get('site');
            $bloquant= $request->get('bloquant');
            $statistiques= $request->get('statistiques');
            $etage = $request->get('etage');
            $groupe = $request->get('groupe');
            $groupe_id = $request->get('group-id');
            $obligatoire= $request->get('obligatoire');
            $site=$site?$site:1;

            if (!$id){
                    $position = new Position;
                    $position->nom($nom);
                    $position->activites($activites);
                    $position->categories($categories);
                    $position->bloquant($bloquant);
                    $position->statistiques($statistiques);
                    $position->etage($etage);
                    $position->groupe($groupe);
                    $position->groupe_id($groupe_id);
                    $position->obligatoire($obligatoire);
                    $position->site(site);
                    $this->entityManager->persist($position);

                    if (isset($error)) {
                        $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'ajout du poste " );
                    } else {
                        $this->entityManager->flush();
                        $session->getFlashBag()->add('notice', "Le poste a été ajouté avec succès");
                    }

            }else{
                    $position=$this->entityManager->getRepository(Position::class)->find($id);
                    $position->nom($nom);
                    $position->activites($activites);
                    $position->categories($categories);
                    $position->bloquant($bloquant);
                    $position->statistiques($statistiques);
                    $position->etage($etage);
                    $position->groupe($groupe);
                    $position->groupe_id($groupe_id);
                    $position->obligatoire($obligatoire);
                    $position->site($site);

                    $this->entityManager->persist($position);

                    if(isset($error)) {
                        $session->getFlashBag()->add('error', "Une erreur est survenue lors de la modification du poste " );
                    } else {
                        $this->entityManager->flush();
                        $session->getFlashBag()->add('notice',"Le poste a été modifié avec succès");
                    }
            }
        }


        return $this->redirectToRoute('position.index');
    }

    /**
     * @Route("/position",name="position.delete", methods={"DELETE"})
     */
     public function delete_position(Request $request){

        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');
        $p = $this->entityManager->getRepository(Position::class)->find($id);

        $this->entityManager->remove($p);
        $this->entityManager->flush();

        return $this->json("Ok");
     }


}
