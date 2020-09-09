<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

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

        $p=new \postes();
        $p->fetch("nom", $nom, $groupe);
        $postes=$p->elements;
        $nbMultisite = $this->config('Multisites-nombre');
        $this->templateParams(array(
            'multisite' =>$nbMultisite,
            'usedPositions' => $postes_utilises,
            'CSRFSession' => $GLOBALS['CSRFSession']
        ));
        foreach ($postes as $id => $value) {
            // Affichage des 3 premières activités dans le tableau, toutes les activités dans l'infobulle
            $activites=array();
            $activitesAffichees=array();
            $activitesPoste=json_decode(html_entity_decode($value['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
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
                $site = $this->config("Multisites-site{$value['site']}") ? $this->config("Multisites-site{$value['site']}") :"-";
                $value['site']=$site;
            }
            $value['nom']=html_entity_decode($value['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $value['activites']=html_entity_decode($activites, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $value['activitesAffichees']=html_entity_decode($activitesAffichees, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $postes[$id]=$value;
        }
        $this->templateParams(array('positions'=> $postes));
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

        $this->templateParams(array(
            'CSRFToken'=> $GLOBALS['CSRFSession'],
            'activites' => $activites,
            'actList' => $actList,
            'categories' => $categories,
            'categoriesList' => $categories_list,
            'etages' => $etages,
            'groupes'=>$groupes,
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

        $db=new \db();
        $db->select2("postes", "*", array("id"=>$id));
        $nom=$db->result[0]['nom'];
        $etage=$db->result[0]['etage'];
        $groupe=$db->result[0]['groupe'];
        $categories = $db->result[0]['categories'] ? json_decode(html_entity_decode($db->result[0]['categories'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true) : array();
        $site=$db->result[0]['site'];
        $activites=json_decode(html_entity_decode($db->result[0]['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
        $obligatoire=$db->result[0]['obligatoire']=="Obligatoire"?"checked='checked'":"";
        $renfort=$db->result[0]['obligatoire']=="Renfort"?"checked='checked'":"";
        $stat1=$db->result[0]['statistiques']?"checked='checked'":"";
        $stat2=!$db->result[0]['statistiques']?"checked='checked'":"";
        $bloq1=$db->result[0]['bloquant']?"checked='checked'":"";
        $bloq2=!$db->result[0]['bloquant']?"checked='checked'":"";

        $checked=null;
        // Recherche des étages
        $db=new \db();
        $db->select2("select_etages", "*", "1", "order by rang");
        $etages=$db->result;

        // Recherche des étages utilisés
        $etages_utilises = array();
        $db=new \db();
        $db->select2('postes', 'etage', array('supprime'=>null), 'group by etage');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $etages_utilises[] = $elem['etage'];
            }
        }

        // Recherche des groupes
        $db=new \db;
        $db->select2("select_groupes", "*", "1", "order by rang");
        $groupes=$db->result;

        //Recherche des groupes utilisés
        $groupes_utilises = array();
        $db=new \db();
        $db->select2('postes', 'groupe', array('supprime'=>null), 'group by groupe');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $groupes_utilises[] = $elem['groupe'];
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
            //$activites = array();
            $activites = json_encode($request->get('activites'));
            $categories = json_encode($request->get('categories'));


            $id = $request->get('id');
            $site = $request->get('site');
            $bloquant= $request->get('bloquant');
            $statistiques= $request->get('statistiques');
            $etage = $request->get('etage');
            $groupe = $request->get('groupe');
            $obligatoire= $request->get('obligatoire');
            $site=$site?$site:1;
            $data=array("nom"=>$nom,"obligatoire"=>$obligatoire,"etage"=>$etage,"groupe"=>$groupe,"activites"=>$activites, "statistiques"=>$statistiques,"bloquant"=>$bloquant,"site"=>$site,"categories"=>$categories);
            if (!$id){
                    $position = new \db();
                    $position->CSRFToken = $CSRFToken;
                    $position->insert("postes", $data);
                    if ($position->error) {
                        $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'ajout du poste " . $position->error);
                    } else {
                        $session->getFlashBag()->add('notice', "Le poste a été ajouté avec succès");
                    }
            }else{
                    $position=new \db();
                    $position->CSRFToken = $CSRFToken;
                    $position->update("postes", $data, array("id"=>$id));
                    if ($position->error) {
                        $session->getFlashBag()->add('error', "Une erreur est survenue lors de la modification du poste " . $position->CSRFToken);
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
     public function delete_position(Request $request){

        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');
        $p = new \postes();
        $p->CSRFToken = $CSRFToken;
        $p->id=$id;
        $p->delete();

        return $this->json("Ok");
     }


}
