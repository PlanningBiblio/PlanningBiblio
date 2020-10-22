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
     * @Route ("/framework/{id}", name="framework.edit", methods={"GET"})
     */
    public function edit (Request $request, Session $session){
        $CSRFToken = $request->get("CSRFToken");
        $cfgType = $request->get("cfg-type");
        $cfgTypeGet = $request->get("cfg-type");
        $tableauNumero = $request->request->get("numero");
        $tableauGet = $request->get("numero");
        
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
            $db->select2("pl_poste_tab", "*", array("tableau"=>$tableauNumero));
            $tableauNom = $db->result[0]['nom'];
        }

        $multisites = array();
        if ($nbSites>1) {
            for ($i = 1 ;$i <= $nbSites; $i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }
          
    }

}