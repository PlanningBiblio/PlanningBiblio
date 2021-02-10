<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/planning/postes_cfg/class.tableaux.php');
require_once(__DIR__ . '/../../public/planning/poste/fonctions.php');
require_once(__DIR__ . '/../../public/postes/class.postes.php');

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
     * @Route ("/framework/add", name="framework.add_table", methods={"GET"})
     */
     public function addTable (Request $request, Session $session){
        $CSRFToken = $GLOBALS['CSRFSession'];
        $cfgType = $request->get("cfg-type");
        $cfgTypeGet = $request->get("cfg-type");
        $tableauNumero = $request->request->get("numero");
        $tableauGet = $request->get("numero");
        $nbSites = $this->config('Multisites-nombre');

        // Choix de l'onglet (cfg-type)
        if ($cfgTypeGet) {
            $cfgType = $cfgTypeGet;
        }
        if (!$cfgType and in_array("cfg_type", $_SESSION)) {
            $cfgType = $_SESSION['cfg_type'];
        }
        if (!$cfgType and !in_array("cfg_type", $_SESSION)) {
            $cfgType = "infos";
        }
        $_SESSION['cfg_type'] = $cfgType;

        $tableauNom = '';
        if ($tableauNumero) {
            $db = new \db();
            $db->sanitize_string = false;
            $db->select2("pl_poste_tab", "*", array("tableau"=>$tableauNumero));
            $tableauNom = $db->result[0]['nom'];
        }

        $multisites = array();
        if ($nbSites>1) {
            for ($i = 1 ;$i <= $nbSites; $i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        $this->templateParams(
            array(
                "cfgType"       => $cfgType,
                "CSRFToken"     => $CSRFToken,
                "lignes_sep"    => null,
                "multisites"    => $multisites,
                "postes"        => null,
                "nbSites"       => $nbSites,
                "nombre"        => null,
                "selectHeure"   => null,
                "site"          => null,
                "tableauNom"    => $tableauNom,
                "tableauNumero" => $tableauNumero,
                "tableaux"      => null,
                "tabs"          => null,
            )
        );

        return $this->output('framework/edit_tab.html.twig');

     }
    /**
     * @Route ("/framework/{id}", name="framework.edit_table", methods={"GET"})
     */
    public function editTable (Request $request, Session $session){
        $CSRFToken = $GLOBALS['CSRFSession'];
        $cfgType = $request->get("cfg-type");
        $cfgTypeGet = $request->get("cfg-type");
        $tableauNumero = $request->request->get("id");
        $tableauGet = $request->get("id");
        $nbSites = $this->config('Multisites-nombre');

        // Choix du tableau
        if ($tableauGet) {
            $tableauNumero = $tableauGet;
        }

        // Choix de l'onglet (cfg-type)
        if ($cfgTypeGet) {
            $cfgType = $cfgTypeGet;
        }
        if (!$cfgType and in_array("cfg_type", $_SESSION)) {
            $cfgType = $_SESSION['cfg_type'];
        }
        if (!$cfgType and !in_array("cfg_type", $_SESSION)) {
            $cfgType = "infos";
        }
        $_SESSION['cfg_type'] = $cfgType;

        // Affichage
        $tableauNom = '';
        if ($tableauNumero) {
            $db = new \db();
            $db->sanitize_string = false;
            $db->select2("pl_poste_tab", "*", array("tableau"=>$tableauNumero));
            $tableauNom = $db->result[0]['nom'];
        }

        $multisites = array();
        if ($nbSites>1) {
            for ($i = 1 ;$i <= $nbSites; $i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        // Nombre de tableaux
        $t = new \tableau();
        $t->id = $tableauNumero;
        $t->getNumbers();
        $nombre = $t->length;
        $site = 1;


        // Site
        if ($nbSites > 1 && $tableauNumero) {
            $db = new \db();
            $db->select("pl_poste_tab", "*", "tableau='$tableauNumero'");
            $site=$db->result[0]['site'];
        }

        //	Liste des horaires
        $db = new \db();
        $db->select("pl_poste_horaires", "*", "`numero` ='$tableauNumero'", "ORDER BY `tableau`,`debut`,`fin`");
        $horaires = $db->result;

        // Liste des tableaux
        $tableaux = array();
        if ($horaires) {
            foreach ($horaires as $elem) {
                if (!array_key_exists($elem['tableau'], $tableaux)) {
                    $tableaux[$elem['tableau']]=array('tableau'=>$elem['tableau'], 'horaires'=>array());
                }
                $tableaux[$elem['tableau']]['horaires'][]=array("id"=>$elem["id"], "debut"=>$elem["debut"],"fin"=>$elem["fin"]);
            }
        }

        //	Liste des tableaux utilisés
        $used = array();
        $db = new \db();
        $db->select("pl_poste_tab_affect", "tableau", null, "group by tableau");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $used[] = $elem['tableau'];
            }
        }
        $db = new \db();
        $db->select("pl_poste_modeles_tab", "tableau", null, "group by tableau");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $used[] = $elem['tableau'];
            }
        }

        $selectHeure= array();
        if (!empty($tableaux)) {
            foreach ($tableaux as $t) {
                $tableau=$t['tableau'];
                $i=0;
                foreach ($t['horaires'] as $elem) {
                    $selectHeure[$tableau][$i]['debut'] = myselectHeure(6, 23, true);
                    $selectHeure[$tableau][$i]['fin'] = myselectHeure(6, 23, true);
                    $i++;
                }

                // Affichage des select cachés pour les ajouts
                for ($j = 0; $j < 25; $j++) {
                    $selectHeure[$tableau][$i]['debut'] = myselectHeure(6, 23, true);
                    $selectHeure[$tableau][$i]['fin'] = myselectHeure(6, 23, true);
                    $i++;
                }
            }
        }

        // Liste des postes
        $p = new \postes();
        if ($nbSites > 1) {
            $p->site = $site;
        }
        $p->fetch("nom");
        $postes = $p->elements;

        // Liste des lignes de séparation
        $db = new \db();
        $db->select("lignes", null, null, "ORDER BY nom");
        $lignes_sep = $db->result;

        // Le tableau (contenant les sous-tableaux)
        $t = new \tableau();
        $t->id = $tableauNumero;
        $t->get();
        $tabs = $t->elements;

        if ($tableauNumero) {
            foreach ($tabs as &$tab) {
                $colspan = 0;
                foreach ($tab['horaires'] as &$horaire) {
                    $horaire['colspan'] = nb30($horaire['debut'], $horaire['fin']);
                    $horaire['debut'] = heure3($horaire['debut']);
                    $horaire['fin'] = heure3($horaire['fin']);
                    $colspan+=$horaire['colspan'];
                }
                $tab['colspan'] = $colspan;
            }
        }


        $this->templateParams(
            array(
                "cfgType"       => $cfgType,
                "CSRFToken"     => $CSRFToken,
                "lignes_sep"    => $lignes_sep,
                "multisites"    => $multisites,
                "postes"        => $postes,
                "nbSites"       => $nbSites,
                "nombre"        => $nombre,
                "selectHeure"   => $selectHeure,
                "site"          => $site,
                "tableauNom"    => $tableauNom,
                "tableauNumero" => $tableauNumero,
                "tableaux"      => $tableaux,
                "tabs"          => $tabs,
            )
        );

        return $this->output('framework/edit_tab.html.twig');
    }

    /**
     * @Route ("/framework", name="framework.save_table", methods={"POST"})
     */
    public function saveTable (Request $request, Session $session){
        $post = $request->request->all();
        $CSRFToken = $post['CSRFToken'];
        $tableauNumero = $post['numero'];

        if (isset($post['action'])) {
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("pl_poste_horaires", array("numero"=>$tableauNumero));

            $keys = array_keys($post);

            foreach ($keys as $key) {
                if ($key != "page" and $key != "action" and $key != "numero") {
                    $tmp = explode("_", $key);				// debut_1_22
                    if (array_key_exists(1, $tmp) and array_key_exists(2, $tmp)) {
                        if (empty($tab[$tmp[1]."_".$tmp[2]])) {
                            $tab[$tmp[1]."_".$tmp[2]] = array($tmp[1]);
                        }	// tab[0]=tableau
                        if ($tmp[0] == "debut") {				// tab[1]=debut
                            $tab[$tmp[1]."_".$tmp[2]][1] = $post[$key];
                        }
                        if ($tmp[0] == "fin") {				// tab[2]=fin
                            $tab[$tmp[1]."_".$tmp[2]][2] = $post[$key];
                        }
                    }
                }
            }
            $values = array();
            foreach ($tab as $elem) {
                if ($elem[1] and $elem[2]) {
                    $values[] = array("debut"=>$elem[1], "fin"=>$elem[2], "tableau"=>$elem[0], "numero"=>$tableauNumero);
                }
            }
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("pl_poste_horaires", $values);
            if (!$db->error) {
                $msg = "Les horaires ont été modifiés avec succès";
                $msgType = "success";
            } else {
                $msg = "Une erreur est survenue lors de l'enregistrement des horaires";
                $msgType = "error";
            }

            return $this->redirectToRoute('framework.edit_table', array("id" => $tableauNumero, "cfg-type"=> $post['cfg-type'], "msg" => $msg, "msgType" => $msgType));
        }
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

    /**
     * @Route ("/framework-line", name="framework.delete_line", methods={"DELETE"})
     */
    public function deleteLine (Request $request, Session $session){
        $post = $request->request->all();
        $id = $post['id'];
        $CSRFToken = $post['CSRFToken'];

        $t = new \tableau();
        $t->id = $id;
        $t->CSRFToken = $CSRFToken;
        $t->deleteLine();
        return $this->json('ok');
    }
}