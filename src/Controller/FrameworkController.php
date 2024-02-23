<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Framework;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionCells;
use App\Model\PlanningPositionHours;
use App\Model\PlanningPositionLines;
use App\Model\PlanningPositionTab;
use App\Model\PlanningPositionTabAffectation;
use App\Model\Position;
use App\PlanningBiblio\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

require_once(__DIR__ . '/../../public/planning/poste/fonctions.php');
require_once(__DIR__ . '/../../public/postes/class.postes.php');

class FrameworkController extends BaseController
{
    use \App\Trait\FrameworkTrait;

    /**
     * @Route ("/framework", name="framework.index", methods={"GET"})
     */
    public function index (Request $request, Session $session){
        $nbSites = $this->config('Multisites-nombre');

        // Frameworks
        $frameworks = $this->getAllFrameworks();

        // Deleted Frameworks
        $deletedFrameworks = $this->getAllFrameworks('all', true);

        // Last use of this framework
        $assignments = array();
        $db = new \db();
        $db->select2("pl_poste_tab_affect", null, null, "order by `date` asc");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $assignments[$elem['tableau']] = $elem['date'];
            }
        }

        if(!empty($frameworks)){
            foreach ($frameworks as &$framework) {
                if (array_key_exists($framework->tableau(), $assignments)) {
                    $lastUsed = dateFr($assignments[$framework->tableau()]);
                } else {
                    $lastUsed = 'Jamais';
                }
                $framework->assignment($lastUsed);

                if ($nbSites > 1) {
                    $framework->siteName($this->config("Multisites-site{$framework->site()}"));
                }
            }
        }

        // Récupération de tableaux supprimés dans l'année
        if (!empty($deletedFrameworks)) {
            foreach ($deletedFrameworks as &$framework) {
                if (array_key_exists($framework->tableau(), $assignments)) {
                    $lastUsed = dateFr($assignments[$framework->tableau()]);
                } else {
                    $lastUsed = 'Jamais';
                }
                $framework->assignment($lastUsed);
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
                'groupes'           => $groupes,
                'lignes'            => $lignes,
                'nbSites'           => $nbSites,
                'numero1'           => null,
                'frameworks'        => $frameworks,
                'deletedFrameworks' => $deletedFrameworks
            )
        );

        return $this->output("/framework/index.html.twig");
    }

    /**
     * @Route ("/framework/info", name="framework.save_table_info", methods={"POST"})
     */
    public function saveInfo(Request $request, Session $session){

        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');
        $name = trim($request->get('name'));
        $numberOfTables = $request->get('nombre');
        $site = $request->get('site', 1);

        // Create a new framework
        if (!$id) {

            // Find the new ID
            $id = $this->entityManager->getRepository(PlanningPositionTab::class)->nextTableId();

            // Save the new framework
            $framework = new PlanningPositionTab();
            $framework->nom($name);
            $framework->tableau($id);
            $framework->site($site);
            $this->entityManager->persist($framework);
            $this->entityManager->flush();

            // Create the tables
            $this->createTables($id, $numberOfTables);

            $session->getFlashBag()->add('notice', 'Le tableau a été créé');

        // Update an existing framework
        } else {

            // Get framework information
            $framework = $this->entityManager->getRepository(PlanningPositionTab::class)->findOneBy(['tableau' => $id]);

            // Prohibit modification of the site if the framework already contains lines
            $lines = $this->entityManager->getRepository(PlanningPositionLines::class)->findBy(['numero' => $id, 'type' => 'poste']);

            if (!empty($lines)) {
                $site = $framework->site();
            }

            $originalNumberOfTables = $this->getNumberOfTables($id);

            $isUsed = $this->getLastUsed($id);

            // If the framework is used and if something else than the name is changed, we create a hidden copy
            if ($isUsed and ($site != $framework->site() or $numberOfTables != $originalNumberOfTables)) {
                $id = $this->frameworkCopy($id);
            }

            // Create or update the tables
            $this->createTables($id, $numberOfTables);

            // Change name and/or site
            $framework->nom($name);
            $framework->site($site);
            $this->entityManager->flush();

            $session->getFlashBag()->add('notice', 'Les modifications ont été enregistrées');
        }

        $session->set('frameworkActiveTab', 0);

        return $this->redirectToRoute('framework.edit_table', ['id' => $id]);
    }


     /**
     * @Route ("/framework/add", name="framework.add_table", methods={"GET"})
     */
     public function addTable (Request $request, Session $session){
        $tableauNumero = $request->request->get("numero");
        $tableauGet = $request->get("numero");
        $nbSites = $this->config('Multisites-nombre');

        $tableauNom = '';
        if ($tableauNumero) {
            $db = new \db();
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
                "activeTab"     => 0,
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
                "used"          => false,
            )
        );

        return $this->output('framework/edit_tab.html.twig');
     }


    /**
     * @Route ("/framework/{id}", name="framework.edit_table", methods={"GET"})
     */
    public function editTable (Request $request, Session $session)
    {

        // MT36324 / TODO : Prohibit access to updated frameworks (when the field updated is not null)

        $tableauNumero = $request->request->get("id");
        $tableauGet = $request->get("id");
        $nbSites = $this->config('Multisites-nombre');

        // Choix du tableau
        if ($tableauGet) {
            $tableauNumero = $tableauGet;
        }

        // Affichage
        $tableauNom = '';
        if ($tableauNumero) {
            $db = new \db();
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
        $nombre = $this->getNumberOfTables($tableauNumero);
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
                "activeTab"     => $session->get('frameworkActiveTab'),
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
                'used'          => $t->is_used(), // MT36324 / TODO : see if it's still necessary, Maybe to display an alert
            )
        );

        return $this->output('framework/edit_tab.html.twig');
    }

    /**
     * @Route ("/framework", name="framework.save_table", methods={"POST"})
     */
    public function saveTable (Request $request, Session $session){

        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('numero');
        $post = $request->request->all();

        // Look for differences between origin and new hours
        $repo = $this->entityManager->getRepository(PlanningPositionHours::class)->findBy(['numero' => $id], ['numero' => 'ASC', 'debut' => 'ASC', 'fin' => 'ASC']);

        $origin = array();
        foreach ($repo as $elem) {
            $origin[$elem->tableau()][] = array($elem->debut()->format('H:i'), $elem->fin()->format('H:i'));
        }

        $new = array();
        foreach ($post as $key => $value) {
            $keys = explode('_', $key);
            if ($keys[0] == 'debut' and !empty($value)) {
                $end = $post['fin_' . $keys[1] . '_' . $keys[2]]; 
                if (!empty($end)) {
                    $new[$keys[1]][] = array($value, $end); 
                }
            }
        }

        $diff = false;
        if (count($new) != count($origin)) {
            $diff = true;
        }

        for($i=0; $i<=count($new); $i++) {
            if (!empty($new[$i]) and !empty($origin[$i])) {
                if (json_encode($new[$i]) != json_encode($origin[$i])) {
                    $diff = true;
                    break;
                }
            }
        }

        // If hours changed
        if ($diff) {

            // If the framework is used and hours changed, we create a copy then update the copy
            $isUsed = $this->getLastUsed($id);

            if ($isUsed) {
                $id = $this->frameworkCopy($id);
            }

            // Delete hours from DB to create new ones
            $this->entityManager->getRepository(PlanningPositionHours::class)->delete($id);
    
            // Store new hours into DB
            foreach ($new as $key => $value) {
                foreach ($value as $elem) {
                    $hours = new PlanningPositionHours();
                    $hours->debut(\DateTime::createFromFormat('H:i', $elem[0]));
                    $hours->fin(\DateTime::createFromFormat('H:i', $elem[1]));
                    $hours->tableau($key);
                    $hours->numero($id);
                    $this->entityManager->persist($hours);
                    $this->entityManager->flush();
                }
            }
    
            $session->getFlashBag()->add('notice', 'Les horaires ont été modifiés avec succès');

        // No changes
        } else {
            $session->getFlashBag()->add('notice', 'Aucune modification n\'a été apportée');
        }

        $session->set('frameworkActiveTab', 1);

        return $this->redirectToRoute('framework.edit_table', ['id' => $id]);
    }


    /**
     * @Route ("framework-table/save-line", name="framework.save_table_line", methods={"POST"})
     */
    public function saveTableLine(Request $request, Session $session){

        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $form_post = $request->request->all();
        $CSRFToken = $form_post['CSRFToken'];
        $tableauNumero = $form_post['id'];
        $dbprefix = $GLOBALS['dbprefix'];

        // MT36324 / TODO : Create a copy, then update the copy

        // Suppression des infos concernant ce tableau dans la table pl_poste_lignes
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste_lignes", array("numero" => $tableauNumero));

        // Insertion des données dans la table pl_poste_lignes
        foreach ($form_post as $key => $value) {
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

                $line = new PlanningPositionLines();
                $line->numero($tableauNumero);
                $line->tableau($tab[1]);
                $line->ligne($tab[2]);
                $line->poste($value);
                $line->type($type);

                $this->entityManager->persist($line);
                $this->entityManager->flush();
            }
        }

        // Suppression des infos concernant ce tableau dans la table pl_poste_cellules
        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("pl_poste_cellules", array("numero" => $tableauNumero));

        // Insertion des données dans la table pl_poste_cellules
        $values=array();
        foreach ($form_post as $key => $value) {
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
        $frameworks = $this->getAllFrameworks();

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
                'champs'     => $champs,
                'CSRFToken'  => $CSRFToken,
                'id'         => null,
                'groupe'     => $groupe,
                'groupes'    => $groupes,
                'multisites' => $multisites,
                'semaine'    => $semaine,
                'frameworks' => $frameworks
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
        $frameworks = $this->getAllFrameworks();

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
                'champs'     => $champs,
                'CSRFToken'  => $CSRFToken,
                'id'         => $id,
                'groupe'     => $groupe,
                'groupes'    => $groupes,
                'multisites' => $multisites,
                'semaine'    => $semaine,
                'frameworks' => $frameworks
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
     * @Route ("/framework/copy/{id}", name="framework.copy_table", methods={"POST"})
     */
    public function frameworkCopyRoute(Request $request, Session $session)
    {

        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $this->frameworkCopy(
            $request->get('id'),
            $request->get('nom')
        );

        return $this->redirectToRoute('framework.index');
    }

    private function createTables($id, $number)
    {
        $length = $this->getNumberOfTables($id);

        $diff = intval($number) - intval($length);

        if ($diff == 0) {
            return;
        }

        if ($diff > 0) {
            for ($i = $length + 1; $i < ($diff + $length + 1); $i++) {
                $hours = new PlanningPositionHours();
                $hours->debut(\DateTime::createFromFormat('H:i:s', '09:00:00'));
                $hours->fin(\DateTime::createFromFormat('H:i:s', '10:00:00'));
                $hours->numero($id);
                $hours->tableau($i);
                $this->entityManager->persist($hours);
                $this->entityManager->flush();
            }
        }

        if ($diff < 0) {
            for ($i = $length; $i > ($length + $diff); $i--) {
                $hours = $this->entityManager->getRepository(PlanningPositionHours::class)
                    ->findBy(['numero' => $id, 'tableau' => $i]);
                foreach ($hours as $hour) {
                    $this->entityManager->remove($hour);
                    $this->entityManager->flush();
                }

                $lines = $this->entityManager->getRepository(PlanningPositionLines::class)
                    ->findBy(['numero' => $id, 'tableau' => $i]);
                foreach ($lines as $line) {
                    $this->entityManager->remove($line);
                    $this->entityManager->flush();
                }
            }
        }
    }


    // Last assignment of the given framework
    private function getLastAssignment($id)
    {
        $last = $this->entityManager->getRepository(PlanningPositionTabAffectation::class)->findOneBy(
            array('tableau' => $id),
            array('date' => 'desc'),
        );

        $return = $last ? $last->date() : null;

        return $return;
    }

    // Last use of the given framework
    private function getLastUsed($id)
    {
        $site = $this->entityManager->getRepository(PlanningPositionTab::class)->findOneBy(['tableau' => $id])->site();

        $assignments = $this->entityManager->getRepository(PlanningPositionTabAffectation::class)->findBy(
            array('tableau' => $id),
            array('date' => 'desc'),
        );

        if (empty($assignments)) {
            return null;
        }

        $dates = array();
        foreach ($assignments as $assignment) {
            $dates[] = $assignment->date();
        }

        $plannings = $this->entityManager->getRepository(PlanningPosition::class)->getByDate($dates, $site);

        if (empty($plannings)) {
            return null;
        }

        $planning = end($plannings);

        return $planning['date'];
    }

    private function getNumberOfTables($id)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('h')
            ->from(PlanningPositionHours::class, 'h')
            ->where('h.numero = :numero')
            ->groupBy('h.tableau')
            ->setParameter('numero', $id);

        return count($qb->getQuery()->getResult());
    }

    private function frameworkCopy(int $id, $newName = null)
    {
        // Get framework elements
        $framework = $this->entityManager->getRepository(PlanningPositionTab::class)->findOneBy(array(
            'tableau' => $id,
        ));

        // If $newName is not given, it's an update with hidden copy
        if (!$newName) {
            $newName = $framework->nom();

            // We set the updated field to true on the original framework to hide it
            $framework->updated(true);
            $this->entityManager->flush();
        }

        // Cells
        $cells = $this->entityManager->getRepository(PlanningPositionCells::class)->findBy(array(
            'numero' => $id,
        ));

        // Hours
        $hours = $this->entityManager->getRepository(PlanningPositionHours::class)->findBy(array(
            'numero' => $id,
        ));

        // Lines
        $lines = $this->entityManager->getRepository(PlanningPositionLines::class)->findBy(array(
            'numero' => $id,
        ));

        // Find the next Framework ID (field "tableau")
        $next = $this->entityManager->getRepository(PlanningPositionTab::class)->nextTableId();

        // Create the copy
        // Framework's copy
        $copy = new PlanningPositionTab();
        $copy->tableau($next);
        $copy->nom($newName);
        $copy->site($framework->site());
        $copy->origin($id);
        $copy->updated_at(New \DateTime());
        $this->entityManager->persist($copy);
        $this->entityManager->flush();

        // Cells
        foreach ($cells as $cell) {
            $new = new PlanningPositionCells();
            $new->numero($next);
            $new->tableau($cell->tableau());
            $new->ligne($cell->ligne());
            $new->colonne($cell->colonne());
            $this->entityManager->persist($new);
            $this->entityManager->flush();
        }

        // Hours
        foreach ($hours as $hour) {
            $new = new PlanningPositionHours();
            $new->numero($next);
            $new->tableau($hour->tableau());
            $new->debut($hour->debut());
            $new->fin($hour->fin());
            $this->entityManager->persist($new);
            $this->entityManager->flush();
        }

        // Lines
        foreach ($lines as $line) {
            $new = new PlanningPositionLines();
            $new->numero($next);
            $new->tableau($line->tableau());
            $new->ligne($line->ligne());
            $new->poste($line->poste());
            $new->type($line->type());
            $this->entityManager->persist($new);
            $this->entityManager->flush();
        }

        // Return the new ID
        return $next;
    }
}
