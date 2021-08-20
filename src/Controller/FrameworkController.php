<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Framework;
use App\Model\Position;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $t = new Framework();
        $t->fetchAll();
        $tableaux = $t->elements;

        // Tableaux supprimés
        $t = new Framework();
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

        if($tableaux){
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
        $t = new Framework();
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
                "numero1"           => null,
                "tableaux"          => $tableaux,
                "tableauxSupprimes" => $tableauxSupprimes
            )
        );

        return $this->output("/framework/index.html.twig");
    }

    /**
     * @Route ("/framework/info", name="framework.save_table_info", methods={"POST"})
     */
    public function saveInfo(Request $request, Session $session){
        $post = $request->request->all();
        $id = $post["id"];
        $CSRFToken = $post["CSRFToken"];
        $nombre = $post["nombre"];
        $nom = $post["nom"];
        $site = $post["site"];

        // Ajout
        if (!$id) {

        // Recherche du numero de tableau à utiliser
            $db = new \db();
            $db->select2("pl_poste_tab", array(array("name" => "MAX(tableau)", "as" => "numero")));
            $numero = $db->result[0]["numero"]+1;

            // Insertion dans la table pl_poste_tab
            $insert = array("nom" => trim($nom), "tableau" => $numero, "site" => "1");
            if ($site) {
                $insert["site"] = $site;
            }
        
            $db = new \db();
            $db->sanitize_string = false;
            $db->CSRFToken = $CSRFToken;
            $db->insert("pl_poste_tab", $insert);

            $t = new Framework();
            $t->id = $numero;
            $t->CSRFToken = $CSRFToken;
            $t->setNumbers($nombre);

            return $this->json((int) $numero);
        } else {  // Modification
            $t = new Framework();
            $t->id = $id;
            $t->CSRFToken = $CSRFToken;
            $t->setNumbers($nombre);

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->sanitize_string = false;
            $db->update("pl_poste_tab", array("nom" => trim($nom)), array("tableau" => $id));

            if ($site) {
                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update('pl_poste_tab', array('site' => $site), array('tableau' => $id));
            }

            return $this->json('OK');
        }
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
        $t = new Framework();
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
        $t = new Framework();
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
     * @Route ("framework-table/save-line", name="framework.save_table_line", methods={"POST"})
     */
    public function saveTableLine(Request $request, Session $session){
        $form_post = $request->request->all();
        $CSRFToken = $form_post['CSRFToken'];
        $tableauNumero = $form_post['id'];
        $dbprefix = $GLOBALS['dbprefix'];

        $post=array();
        foreach ($_POST as $key => $value) {
            $key = filter_var($key, FILTER_SANITIZE_STRING);
            $post[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }

        // Suppression des infos concernant ce tableau dans la table pl_poste_lignes
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste_lignes", array("numero" => $tableauNumero));

        // Insertion des données dans la table pl_poste_lignes
        $values = array();
        foreach ($post as $key => $value) {
            if ($value and substr($key, 0, 6) == "select") {
                $tab = explode("_", $key);  //1: tableau ; 2 lignes
                if (substr($tab[1], -5) == "Titre") {
                    $type = "titre";
                    $tab[1] = substr($tab[1], 0, -5);
                } elseif (substr($tab[1], -6) == "Classe") {
                    $type = "classe";
                    $tab[1] = substr($tab[1], 0, -6);
                } elseif (substr($value, -5) == "Ligne") {
                    $type = "ligne";
                    $value = substr($value, 0, -5);
                } else {
                    $type = "poste";
                }
                $values[] = array(
                    ":numero"  => $tableauNumero, 
                    ":tableau" => $tab[1], 
                    ":ligne"   => $tab[2], 
                    ":poste"   => $value, 
                    ":type"    =>$type
                );
            }
        }
        if ($values[0]) {
            $sql = "INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`,`tableau`,`ligne`,`poste`,`type`) ";
            $sql.="VALUES (:numero, :tableau, :ligne, :poste, :type);";

            $db = new \dbh();
            $db->CSRFToken = $CSRFToken;
            $db->prepare($sql);
            foreach ($values as $elem) {
                $db->execute($elem);
            }
        }

        // Suppression des infos concernant ce tableau dans la table pl_poste_cellules
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste_cellules", array("numero" => $tableauNumero));

        // Insertion des données dans la table pl_poste_cellules
        $values=array();
        foreach ($post as $key => $value) {
            if ($value and substr($key, 0, 8)=="checkbox") {
                $tab = explode("_", $key);  //1: tableau ; 2 lignes ; 3 colonnes
                $values[] = array(
                    ":numero"   =>$tableauNumero,
                    ":tableau"  =>$tab[1],
                    ":ligne"    =>$tab[2],
                    ":colonne"  =>$tab[3]
                );
            }
        }
        if (!empty($values)) {
            $sql="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`,`tableau`,`ligne`,`colonne`) ";
            $sql.="VALUES (:numero, :tableau, :ligne, :colonne)";

            $db = new \dbh();
            $db->CSRFToken = $CSRFToken;
            $db->prepare($sql);
            foreach ($values as $elem) {
                $db->execute($elem);
            }
        }

        return $this->json('ok');
    }

     /**
     * @Route ("/framework", name="framework.delete_table", methods={"DELETE"})
     */
    public function deleteTable (Request $request, Session $session){
        $post = $request->request->all();
        $CSRFToken = $post['CSRFToken'];
        $tableau = $post['tableau'];
        $name = $post['name'];

        try {
            $t = new Framework();
            $t->number = $tableau;
            $t->CSRFToken = $CSRFToken;
            $t->deleteTab();

        } catch (Exception $e) {
            $session->getFlashBag()->add(
                'error',
                "Une erreur est survenue lors de la suppression du tableau \"$name\"\n"
                    . $e->getMessage()
            );
            return $this->json('notok');
        }

        $session->getFlashBag()->add('notice', "Le tableau \"$name\" a été supprimé avec succès");
        return $this->json('ok');
    }

     /**
     * @Route ("/framework-batch_delete", name="framework.delete_selected_tables", methods={"GET"})
     */
    public function deleteSelectedTables (Request $request, Session $session){
        $CSRFToken = $request->get("CSRFToken");
        $ids = $request->get("ids");
        $dbprefix = $GLOBALS['dbprefix'];

        $today = date("Y-m-d H:i:s");
        $set = array("supprime"=>$today);
        $where = array("tableau"=>"IN $ids");

        $db = new \db();
        $db->query("UPDATE `{$dbprefix}pl_poste_tab_grp` SET `supprime`='$today' WHERE `lundi` IN ($ids) OR `mardi` IN ($ids) OR `mercredi` IN ($ids) OR `jeudi` IN ($ids) OR `vendredi` IN ($ids) OR `samedi` IN ($ids) OR `dimanche` IN ($ids);");

        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update("pl_poste_tab", $set, $where);

        return $this->json('ok');
    }

    /**
    * @Route ("/framework/restore_table", name="framework.restore_table", methods={"POST"})
    */
    public function restoreTable (Request $request, Session $session) {
        $CSRFToken = $request->get("CSRFToken");
        $id = $request->get("id");
        $name = $request->get("name");

        $postes=array();

        $db = new \db();
        $db->selectInnerJoin(
            array('pl_poste_lignes', 'numero'),
            array('pl_poste_tab', 'tableau'),
            array(array('name' => 'poste', 'as' => 'poste')),
            array(), array(), array('tableau' => $id)
        );

        if ($db->result) {
            foreach ($db->result as $elem) {
                $postes[]=$elem['poste'];
            }
        }

        if (!empty($postes)) {
            $postes_str = implode(',', $postes);
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('postes', array('supprime' => null), array('id' => "IN $postes_str"));

            // Get skills
            $query = $this->entityManager->createQueryBuilder()
                ->select('p.activites')
                ->from(Position::class, 'p')
                ->where('p.id IN (:positions)')
                ->setParameter('positions', $postes)
                ->getQuery();

            $result = $query->getResult();
            $skills = array_map(function($s) { return $s['activites'][0]; }, $result);

            if (!empty($skills)) {
                $skills = implode(',', $skills);
                $db=new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update('activites', array('supprime' => null), array('id' => "IN $skills"));
            }
        }

        // Recupération du tableau
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update('pl_poste_tab', array('supprime' => null), array('tableau' => $id));

        $session->getFlashBag()->add('notice', "Le tableau \"$name\" a été récupéré avec succès");
        return $this->json('OK');
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
        $t = new Framework();
        $t->fetchAll();
        $tableaux = $t->elements;

        //	Recherche des groupes
        $t = new Framework();
        $t->fetchAllGroups();
        $groupes = $t->elements;

        $groupe = array("nom" => null, "site" => null);

        $semaine = array("lundi","mardi","mercredi","jeudi","vendredi","samedi");
        if ($this->config('Dimanche')) {
            $semaine[] = "dimanche";
        }
        $champs = '"Nom,'.implode(',', $semaine).'"';

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
        $t = new Framework();
        $t->fetchAll();
        $tableaux = $t->elements;

        //	Recherche des groupes
        $t = new Framework();
        $t->fetchAllGroups();
        $groupes = $t->elements;

        //	Modification d'un groupe
        //	Recherche du groupe
        $t = new Framework();
        $t->fetchGroup($id);
        $groupe=$t->elements;

        //	Supprime le nom actuel de la liste des noms deja utilises
        $key = array_keys($groupes, $groupe);
        unset($groupes[$key[0]]);


        $semaine = array("lundi","mardi","mercredi","jeudi","vendredi","samedi");
        if ($this->config('Dimanche')) {
            $semaine[] = "dimanche";
        }
        $champs = '"Nom,'.implode(',', $semaine).'"';	//	Pour ctrl_form

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

        $t = new Framework();
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
        
        $t = new Framework();
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

        $t = new Framework();
        $t->id = $id;
        $t->CSRFToken = $CSRFToken;
        $t->deleteLine();
        return $this->json('ok');
    }

    /**
     * @Route ("/framework/copy/{id}", name="framework.copy_table", methods={"GET", "POST"})
     */
    public function copyTable (Request $request, Session $session){

        // Initilisation des variables
        $confirm = $request->get('confirm');
        $CSRFToken = $request->get('CSRFToken');
        $nom = trim($request->get('nom'));
        $numero1 = $request->get('id');
        $confirm = filter_var($confirm, FILTER_CALLBACK, array("options"=>"sanitize_on"));
        $dbprefix = $GLOBALS['dbprefix'];

        if ($confirm) {
            //		Copie des horaires
            $values = array();
            $db = new \db();
            $db->select2("pl_poste_horaires", array("debut","fin","tableau"), array("numero"=>$numero1), "ORDER BY `tableau`,`debut`,`fin`");
            if ($db->result) {
                $db2 = new \db();
                $db2->select2("pl_poste_tab", array(array("name"=>"MAX(tableau)","as"=>"tableau"),"site"));
                $numero2 = $db2->result[0]['tableau']+1;
                foreach ($db->result as $elem) {
                    if (array_key_exists('tableau', $elem)) {
                        $values[] = array(":debut"=>$elem['debut'], ":fin"=>$elem['fin'], ":tableau"=>$elem['tableau'], ":numero"=>$numero2);
                    }
                }
                $req = "INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`,`fin`,`tableau`,`numero`) VALUES (:debut, :fin, :tableau, :numero);";
                $db2 = new \dbh();
                $db2->CSRFToken = $CSRFToken;
                $db2->prepare($req);
                foreach ($values as $elem) {
                    $db2->execute($elem);
                }

                // Récupération du site
                $db2 = new \db();
                $db2->select2("pl_poste_tab", "site", array("tableau"=>$numero1));
                $site=$db2->result[0]["site"];

                // Enregistrement du nouveau tableau
                $db2 = new \db();
                $db2->CSRFToken = $CSRFToken;
                $db2->insert("pl_poste_tab", array("nom"=>$nom ,"tableau"=>$numero2, "site"=>$site));
            } else {		// par sécurité, si pas d'horaires à  copier, on stop le script pour éviter d'avoir une incohérence dans les numéros de tableaux
               return new RedirectResponse($this->config('URL')."/index.php?page=planning/postes_cfg/modif.php&cfg-type=horaires&numero={$numero1}");

            }

            //		Copie des lignes
            $values = array();
            $db->select2("pl_poste_lignes", array("tableau","ligne","poste","type"), array("numero"=>$numero1), "ORDER BY `tableau`,`ligne`");
            if ($db->result) {
                foreach ($db->result as $elem) {
                    if (array_key_exists('ligne', $elem)) {
                        $values[] = array(":tableau"=>$elem['tableau'], ":ligne"=>$elem['ligne'], ":poste"=>$elem['poste'], ":type"=>$elem['type'],
            "numero"=>$numero2);
                    }
                }
                $req = "INSERT INTO `{$dbprefix}pl_poste_lignes` (`tableau`,`ligne`,`poste`,`type`,`numero`) ";
                $req .= "VALUES (:tableau, :ligne, :poste, :type, :numero)";
                $db2 = new \dbh();
                $db2->CSRFToken = $CSRFToken;
                $db2->prepare($req);
                foreach ($values as $elem) {
                    $db2->execute($elem);
                }
            }

            //		Copie des cellules grises
            $values = array();
            $db->select2("pl_poste_cellules", array("ligne","colonne","tableau"), array("numero"=>$numero1), "ORDER BY `tableau`,`ligne`,`colonne`");
            if ($db->result) {
                foreach ($db->result as $elem) {
                    if (array_key_exists('ligne', $elem) and array_key_exists('colonne', $elem)) {
                        $values[] = array(":ligne"=>$elem['ligne'], ":colonne"=>$elem['colonne'], ":tableau"=>$elem['tableau'], ":numero"=>$numero2);
                    }
                }
                $req = "INSERT INTO `{$dbprefix}pl_poste_cellules` (`ligne`,`colonne`,`tableau`,`numero`) ";
                $req .= "VALUES (:ligne, :colonne, :tableau, :numero)";
                $db2 = new \dbh();
                $db2->CSRFToken = $CSRFToken;
                $db2->prepare($req);
                foreach ($values as $elem) {
                    $db2->execute($elem);
                }
            }

            // Retour à  la page principale
            return $this->redirectToRoute('framework.index', array("cfg-type" => "horaires", "numero" => $numero2 ));
        }
    }
}