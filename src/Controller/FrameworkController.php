<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/planning/postes_cfg/class.tableaux.php');

class FrameworkController extends BaseController
{
    /**
     * @Route ("/framework", name="framework.index", methods={"GET"})
     */
    public function index (Request $request, Session $session){
        $nbSites = $this->config('Multisites-nombre');

        // Tableaux
        $t = new \tableau();
        $t->fetchAll();
        $tableaux = $t->elements;

        // Tableaux supprimés
        $t = new \tableau();
        $t->supprime = true;
        $t->fetchAll();
        $tableauxSupprimes = $t->elements;

        // Dernières utilisations des tableaux
        $tabAffect = array();
        $db = new \db();
        $db->select2("pl_poste_tab_affect", null, null, "order by `date` asc");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $tabAffect[$elem['tableau']] = $elem['date'];
            }
        }

        foreach ($tableaux as &$elem) {
            if (array_key_exists($elem['tableau'], $tabAffect)) {
                $utilisation=dateFr($tabAffect[$elem['tableau']]);
            } else {
                $utilisation="Jamais";
            }
            $elem['tabAffect'] = $utilisation;

            if ($nbSites > 1){
                $elem['multisite'] = $this->config("Multisites-site{$elem['site']}");
            }
        }

        // Récupération de tableaux supprimés dans l'année
        if (!empty($tableauxSupprimes)) {
            foreach ($tableauxSupprimes as &$elem) {
                if (array_key_exists($elem['tableau'], $tabAffect)) {
                    $utilisation=dateFr($tabAffect[$elem['tableau']]);
                } else {
                    $utilisation="Jamais";
                }
                $elem['tabAffect'] = $utilisation;
            }
        }
        //		Groupes
        $t = new \tableau();
        $t->fetchAllGroups();
        $groupes = $t->elements;

        if (is_array($groupes)) {
            foreach ($groupes as &$elem) {
                if ($nbSites > 1) {
                    $elem['multisite'] = $this->config("Multisites-site{$elem['site']}");
                }
            }
        }

        $db = new \db();
        $db->select("lignes", null, null, "order by nom");
        $lignes = $db->result; 
        if ($lignes) {
            foreach ($lignes as &$elem) {
                $db2 = new \db();
                $db2->select("pl_poste_lignes", "*", "poste='{$elem['id']}' AND type='ligne'");
                $delete = $db2->result ? false : true;
                $elem['delete'] = $delete == true ? true : false;
            }
        }

        $this->templateParams(
            array(
                "groupes"           => $groupes,
                "lignes"            => $lignes,
                "nbSites"           => $nbSites,
                "tableaux"          => $tableaux,
                "tableauxSupprimes" => $tableauxSupprimes
            )
        );
        return $this->output("/framework/index.html.twig");
    }

    /**
     * @Route ("/framework-group/add", name="framework.add_group", methods={"GET"})
     */
    public function addGroup (Request $request, Session $session){
        // Initialisation des variables
        $id = $request->get("id");
        $CSRFToken = $GLOBALS['CSRFSession'];
        $multisites = array();

        if($this->config('Multisites-nombre') > 1){
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++){
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        //	Recherche des tableaux
        $t = new \tableau();
        $t->fetchAll();
        $tableaux = $t->elements;

        //	Recherche des groupes
        $t = new \tableau();
        $t->fetchAllGroups();
        $groupes = $t->elements;

        $groupe = array("nom" => null, "site" => null);

        $semaine = array("lundi","mardi","mercredi","jeudi","vendredi","samedi");
        if ($this->config('Dimanche')) {
            $semaine[] = "dimanche";
        }
        $champs = '"Nom,'.join(',', $semaine).'"';	

        $this->templateParams(
            array(
                "champs"     => $champs,
                "CSRFToken"  => $CSRFToken,
                "id"         => null,
                "groupe"     => $groupe,
                "groupes"    => $groupes,
                "multisites" => $multisites,
                "semaine"    => $semaine,
                "tableaux"   => $tableaux
            )
        );

        return $this->output('framework/edit_group.html.twig');
    }

    /**
     * @Route ("/framework-group/{id}", name="framework.edit_group", methods={"GET"})
     */
    public function editGroup (Request $request, Session $session){
        // Initialisation des variables
        $id = $request->get("id");
        $CSRFToken = $GLOBALS['CSRFSession'];
        $multisites = array();

        if($this->config('Multisites-nombre') > 1){
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++){
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        //	Recherche des tableaux
        $t = new \tableau();
        $t->fetchAll();
        $tableaux = $t->elements;

        //	Recherche des groupes
        $t = new \tableau();
        $t->fetchAllGroups();
        $groupes = $t->elements;

        //	Modification d'un groupe
        //	Recherche du groupe
        $t = new \tableau();
        $t->fetchGroup($id);
        $groupe=$t->elements;

        //	Supprime le nom actuel de la liste des noms deja utilises
        $key = array_keys($groupes, $groupe);
        unset($groupes[$key[0]]);


        $semaine = array("lundi","mardi","mercredi","jeudi","vendredi","samedi"); 
        if ($this->config('Dimanche')) {
            $semaine[] = "dimanche";
        }
        $champs = '"Nom,'.join(',', $semaine).'"';	//	Pour ctrl_form

        $this->templateParams(
            array(
                "champs"     => $champs,
                "CSRFToken"  => $CSRFToken,
                "id"         => $id,
                "groupe"     => $groupe,
                "groupes"    => $groupes,
                "multisites" => $multisites,
                "semaine"    => $semaine,
                "tableaux"   => $tableaux
            )
        );

        return $this->output('framework/edit_group.html.twig');	
    }

    /**
     * @Route ("/framework-group", name="framework.save_group", methods={"POST"})
     */
    public function saveGroup (Request $request, Session $session){
        $post = $request->request->all();
        $CSRFToken = $post['CSRFToken'];
        unset($post['CSRFToken']);
        unset($post['page']);

        $t = new \tableau();
        $t->CSRFToken = $CSRFToken;
        $t->update($post);

        return $this->redirectToRoute('framework.index');
    }

    /**
     * @Route ("/framework-group", name="framework.delete_group", methods={"DELETE"})
     */
    public function deleteGroup (Request $request, Session $session){
        $CSRFToken =  $request->request->get("CSRFToken");
        $id = $request->request->get("id");
        
        $t = new \tableau();
        $t->id = $id;
        $t->CSRFToken = $CSRFToken;
        $t->deleteGroup();
        return $this->json(null);
    }

    /**
     * @Route ("/framework-line/add", name="framework.add_line", methods={"GET"})
     */
    public function addLine (Request $request, Session $session){
        $CSRFToken = $GLOBALS['CSRFSession'];

        $this->templateParams(
            array(
                "CSRFToken"    => $CSRFToken,
            )
        );
        return $this->output("/framework/edit_lines.html.twig");
    }

    /**
     * @Route ("/framework-line/{id}", name="framework.edit_line", methods={"GET"})
     */
    public function editLine (Request $request, Session $session){
        // Initialisation des variables
        $CSRFToken = $GLOBALS['CSRFSession'];
        $id = $request->get('id');

        // Récupération de la ligne
        $db = new \db();
        $db->sanitize_string = false;
        $db->select2("lignes", "nom", array("id"=>$id));
        $nom = $db->result[0]['nom'];

        $this->templateParams(
            array(
                "CSRFToken"    => $CSRFToken,
                "id"           => $id,
                "nom"          => $nom
            )
        );
        return $this->output("/framework/edit_lines.html.twig");
    }

    /**
     * @Route ("/framework-line", name="framework.save_line", methods={"POST"})
     */
    public function saveLine (Request $request, Session $session){
        $post = $request->request->all();
        $id = $post['id'];
        $nom = $post['nom'];
        $CSRFToken = $post['CSRFToken'];

        if ($id){
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("lignes", array("nom"=>$nom), array("id"=>$id));

            if(!$db->error){
                $msg = "La ligne a bien été modifiée." ;
                $msgType = "success";
            } else {
                $msg = "Une erreur a eu lieu lors de la modification de la ligne." ;
                $msgType = "error";
            }

        } else {
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("lignes", array("nom"=>$nom));

            if(!$db->error){
                $msg = "La ligne a bien été enregistrée." ;
                $msgType = "success";
            } else {
                $msg = "Une erreur a eu lieu lors de l'enregistrement de la ligne." ;
                $msgType = "error";
            }

        }

        return $this->redirectToRoute('framework.index', array("msg" => $msg, "msgType" => $msgType));

    }
}