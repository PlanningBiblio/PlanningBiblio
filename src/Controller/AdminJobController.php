<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/postes/class.postes.php');


class AdminJobController extends BaseController
{
    /**
     * @Route("/job", name="job.index", methods={"GET"})
     */
    public function index(Request $request)
    {
      //			Affichage de la liste des postes
      $groupe="Tous";
      $this->templateParams(array('groupe' =>$groupe));

      $nom=filter_input(INPUT_GET, "nom", FILTER_SANITIZE_STRING);

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

      $nbMultisite = $GLOBALS['config']['Multisites-nombre'];
	  $this->templateParams(array(
          'multisite' =>$nbMultisite,
          'usedJobs' => $postes_utilises,
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
			  $site=array_key_exists("Multisites-site{$value['site']}", $GLOBALS['config'])?$GLOBALS['config']["Multisites-site{$value['site']}"]:"-";
			  $value['site']=$site;
		  }
		  $value['nom']=html_entity_decode($value['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
		  $value['activites']=html_entity_decode($activites, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
          $value['activitesAffichees']=html_entity_decode($activitesAffichees, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
          $postes[$id]=$value;
      }
      $this->templateParams(array('jobs'=> $postes));
      return $this->output('job/index.html.twig');
    }

}
