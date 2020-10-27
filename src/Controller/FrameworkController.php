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
