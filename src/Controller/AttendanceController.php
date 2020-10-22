<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__. '/../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__. '/../../public/personnel/class.personnel.php');

class AttendanceController extends BaseController
{

    /**
     * @Route("/attendance", name="attendance.index", methods={"GET"})
     */
    public function index(Request $request, Session $session){
        // Initialisation des variables
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $reset = $request->get("reset");

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $reset = filter_var($reset, FILTER_CALLBACK, array("options"=>"sanitize_on"));

        if (!$debut) {
            $debut = array_key_exists("planningHebdoDebut", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoDebut']:null;
        }

        if (!$fin) {
            $fin = array_key_exists("planningHebdoFin", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoFin']:null;
        }

        if ($reset) {
            $debut = null;
            $fin = null;
        }
        $_SESSION['oups']['planningHebdoDebut'] = $debut;
        $_SESSION['oups']['planningHebdoFin'] = $fin;
        $message = null;

        // Droits d'administration
        // Seront utilisés pour n'afficher que les agents gérés si l'option "PlanningHebdo-notifications-agent-par-agent" est cochée
        $adminN1 = in_array(1101, $droits);
        $adminN2 = in_array(1201, $droits);

        // Droits de gestion des plannings de présence agent par agent
        if ($adminN1 and $config['PlanningHebdo-notifications-agent-par-agent']) {
            $db = new db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));

            if (!$adminN2) {
                $perso_ids = array($_SESSION['login_id']);
            
                if ($db->result) {
                    foreach ($db->result as $elem) {
                        $perso_ids[] = $elem['perso_id'];
                    }
                }
            }
        }

        // Recherche des plannings
        $p = new \planningHebdo();
        $p->merge_exception = false;
        $p->debut=  dateFr($debut);
        $p->fin = dateFr($fin);
        if (!empty($perso_ids)) {
            $p->perso_ids = $perso_ids;
        }
        $p->fetch();

        $a = new \personnel();
        $a->supprime = array(0,1,2);
        $a->fetch();
        $agents = $a->elements;

        foreach ($p->elements as $elem) {
            $actuel = $elem['actuel'] ? "Oui" : null;

            // Validation
            $validation_class = 'bold';
            $validation_date = dateFr($elem['saisie'], true);
            $validation = 'Demandé;';

            // Validation niveau 1
            if ($elem['valide_n1'] > 0) {
                $validation_date = dateFr($elem['validation_n1'], true);
                $validation = $lang['work_hours_dropdown_accepted_pending'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide_n1'] != 99999) {
                    $validation .= ", ".nom($elem['valide_n1'], 'nom p', $agents);
                }
            } elseif ($elem['valide_n1'] < 0) {
                $validation_date = dateFr($elem['validation_n1'], true);
                $validation = $lang['work_hours_dropdown_refused_pending'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide_n1'] != 99999) {
                    $validation.=", ".nom(-$elem['valide_n1'], 'nom p', $agents);
                }
            }
            // Validation niveau 2
            if ($elem['valide'] > 0) {
                $validation_date = dateFr($elem['validation'], true);
                $validation = $lang['work_hours_dropdown_accepted'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide'] != 99999) {
                    $validation.=", ".nom($elem['valide'], 'nom p', $agents);
                }
            } elseif ($elem['valide'] < 0) {
                $validation_class = 'red';
                $validation_date = dateFr($elem['validation'], true);
                $validation = $lang['work_hours_dropdown_refused'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide'] != 99999) {
                    $validation.=", ".nom(-$elem['valide'], 'nom p', $agents);
                }
            }
        
            $planningRemplace = $elem['remplace'] == 0 ? dateFr($elem['saisie'], true) : $planningRemplace;
            $commentaires = $elem['remplace']?"Remplace les heures <br/>du $planningRemplace" : null;
            $commentaires = $elem['exception'] ? 'Exception' : $commentaires;
            
            $elem['debut'] = dateFr($elem['debut']);
            $elem['fin'] = dateFr($elem['fin']);
            $elem['saisie'] = dateFr($elem['saisie'], true);
        }
         return $this->output('/attendance/index.html.twig');
    }
    
    /**
     * @Route("/attendance/add", name="attendance.add", methods={"GET"})
     */
    public function add(Request $request, Session $session){
        // Initialisation des variables
        $copy = $request->get('copy');
        $request_exception = $request->get('exception');
        $retour = $request->get('retour');
        $is_exception = 0;
        $exception_id = '';
        $droits = $GLOBALS['droits'];

        $exception_back = 'monCompte.php';
        if ($retour != 'monCompte.php') {
            $exception_back = $retour;
            $retour = "planningHebdo/$retour";
        }
        
        if ($copy) {
            $id = $copy;
        }
        
        if ($request_exception) {
            $id = $request_exception;
        }
        
        $is_new = 0;
        if (!$id) {
            $is_new = 1;
        }
        
        // Sécurité
        $adminN1 = in_array(1101, $droits);
        $adminN2 = in_array(1201, $droits);
        
        $cle = null;
        $action = "ajout";
        $modifAutorisee = true;
        $debut1 = null;
        $fin1 = null;
        $debut1Fr = null;
        $fin1Fr = null;
        $perso_id = $_SESSION['login_id'];
        $temps = null;
        $valide_n2 = 0;
        $remplace = null;
        $sites = array();
        for ($i = 1; $i < $this->config('Multisites-nombre')+1; $i++) {
            $sites[] = $i;
        }
        $valide_n1 = 0;
        $valide_n2 = 0;

    }
    
    /**
     * @Route("/attendance/{id}", name="attendance.edit", methods={"GET"})
     */
    public function edit(Request $request, Session $session){
        // Initialisation des variables
        $copy = $request->get('copy');
        $request_exception = $request->get('exception');
        $id = $request->get('id');
        $retour = $request->get('retour');
        $is_exception = 0;
        $exception_id = '';
        $droits = $GLOBALS['droits'];

        $exception_back = 'monCompte.php';
        if ($retour != 'monCompte.php') {
            $exception_back = $retour;
            $retour = "planningHebdo/$retour";
        }
        
        if ($copy) {
            $id = $copy;
        }
        
        if ($request_exception) {
            $id = $request_exception;
        }
        
        $is_new = 0;
        if (!$id) {
            $is_new = 1;
        }
        
        // Sécurité
        $adminN1 = in_array(1101, $droits);
        $adminN2 = in_array(1201, $droits);
        
        $cle = null;
        
        $p=new \planningHebdo();
            $p->id = $id;
            $p->fetch();

            $debut1 = $p->elements[0]['debut'];
            $fin1 = $p->elements[0]['fin'];
            $debut1Fr = dateFr($debut1);
            $fin1Fr = dateFr($fin1);
        
            $perso_id = $p->elements[0]['perso_id'];
            $temps = $p->elements[0]['temps'];
            $breaktime = $p->elements[0]['breaktime'];
        
            if ($p->elements[0]['exception']) {
                $is_exception = 1;
                $exception_id = $p->elements[0]['exception'];
            }
        
            if ($copy or $request_exception) {
                $valide_n1 = 0;
                $valide_n2 = 0;
            } else {
                $valide_n1 = $p->elements[0]['valide_n1'] ?? 0;
                $valide_n2 = $p->elements[0]['valide'] ?? 0;
            }
        
            $remplace = $p->elements[0]['remplace'];
            $cle = $p->elements[0]['cle'];
        
            // Informations sur l'agents
            $p = new \personnel();
            $p->fetchById($perso_id);
            $sites = $p->elements[0]['sites'];
        
            // Droits de gestion des plannings de présence agent par agent
            if ($adminN1 and $config['PlanningHebdo-notifications-agent-par-agent']) {
                $db = new \db();
                $db->select2('responsables', 'perso_id', array('perso_id' => $perso_id, 'responsable' => $_SESSION['login_id']));
            
                $adminN1 = $db->result ? true : false;
            }
        
            // Modif autorisée si n'est pas validé ou si validé avec des périodes non définies (BSB).
            // Dans le 2eme cas copie des heures de présence avec modification des dates
            $action = "modif";
            $modifAutorisee = true;
        
            if (!($adminN1 or $adminN2) and !$this->config('PlanningHebdo-Agents')) {
                $modifAutorisee = false;
            }
          
            // Si le champ clé est renseigné, les heures de présences ont été importées automatiquement depuis une source externe. Donc pas de modif
            if ($cle) {
                $modifAutorisee = false;
            }
        
            if (!($adminN1 or $adminN2) and $valide_n2 > 0) {
                $action = "copie";
            }
        
            if ($copy or $request_exception) {
                $action = "ajout";
            }
                  
        $nomAgent = nom($perso_id, "prenom nom");
        
    }

    /**
     * @Route("/attendance", name="attendance.save", methods={"POST"})
     */
    public function save(Request $request, Session $session){
        $post = $request->request->all();
        switch ($post["action"]) {
            case "ajout":
              $p=new planningHebdo();
              $p->add($post);
              if ($p->error) {
                  $msg = urlencode("Une erreur est survenue lors de l'enregistrement du planning.");
          
                  if ($post['id']) {
                      $msg = urlencode("Une erreur est survenue lors de la copie du planning.");
                  }
          
                  $msgType = "error";
              } else {
                  $msg = urlencode("Le planning a été ajouté avec succès.");
                  if ($post['id']) {
                      $msg = urlencode("Le planning a été copié avec succès.");
                  }
                  $msgType = "success";
              }
              echo "<script type='text/JavaScript'>document.location.href='index.php?page={$post['retour']}&msg=$msg&msgType=$msgType';</script>\n";
              break;
          
            case "modif":
              $p = new planningHebdo();
              $p->update($post);
              if ($p->error) {
                  $msg = urlencode("Une erreur est survenue lors de la modification du planning.");
                  $msgType = "error";
              } else {
                  $msg = urlencode("Le planning a été modifié avec succès.");
                  $msgType = "success";
              }
              echo "<script type='text/JavaScript'>document.location.href='index.php?page={$post['retour']}&msg=$msg&msgType=$msgType';</script>\n";
              break;
           
            case "copie":
              $p = new planningHebdo();
              $p->copy($post);
              if ($p->error) {
                  $msg = urlencode("Une erreur est survenue lors de la modification du planning.");
                  $msgType = "error";
              } else {
                  $msg = urlencode("Le planning a été modifié avec succès.");
                  $msgType = "success";
              }
              echo "<script type='text/JavaScript'>document.location.href='index.php?page={$post['retour']}&msg=$msg&msgType=$msgType';</script>\n";
              break;
          }


        return $this->redirectToRoute('presence.index');
    }
}