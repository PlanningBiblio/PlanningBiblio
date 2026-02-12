<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\AbsenceReason;
use App\Entity\Agent;
use App\Planno\Helper\AbsenceBlockHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

// require_once(__DIR__ . '/../../init/init_ajax.php');
require_once(__DIR__ . '/../../legacy/Common/function.php');
require_once(__DIR__ . '/../../legacy/Class/class.absences.php');
require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');

class AjaxController extends BaseController
{

    #[Route(path: '/ajax/sanitize-html', name: 'ajax.sanitizehtml', methods: ['POST'])]
    public function ajax_sanitize_html(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $text = $request->get('text');
        $response = new Response();
        $sanitized = sanitize_html($text);
        $response->setContent($sanitized);
        $response->setStatusCode(200);

        return $response;
    }

    #[Route(path: '/ajax/agents-by-sites', name: 'ajax.agentsbysites', methods: ['GET'])]
    public function agentsBySites(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $session = $request->getSession();

        $sites = json_decode($request->get('sites'));
        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($session->get('loginId'));

        $agents = array();
        foreach ($managed as $m) {
            if ($m->getId() == $session->get('loginId') ||
                $this->config('Multisites-nombre') == 1 ||
                ($sites && $m->inOneOfSites($sites))) {

                $agents[] = array(
                    'id'        => $m->getId(),
                    'nom'       => $m->getLastname(),
                    'prenom'    => $m->getFirstname(),
                    'sites'     => $m->getSites(),
                );
            }
        }
        return $this->json($agents);
    }

    #[Route(path: '/ajax/holiday-delete', name: 'ajax.holidaydelete', methods: ['GET'])]
    public function deleteHoliday(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');

        $c = new \conges();
        $c->id = $id;
        $c->CSRFToken = $CSRFToken;
        $c->delete();

        return $this->json("Holiday deleted");
    }

    #[Route(path: '/ajax/mail-test', name: 'ajax.mailtest', methods: ['POST'])]
    public function mailTest(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {

        include_once(__DIR__ . '/../../legacy/Common/config.php');
        include_once(__DIR__ . '/../../legacy/Common/function.php');

        $mailSmtp = $request->get('mailSmtp');
        $hostname = $request->get('hostname');
        $host = $request->get('host');
        $port = $request->get('port');
        $secure = $request->get('secure');
        $autoTLS = $request->get('autoTLS');
        $auth = $request->get('auth');
        $user = $request->get('user');
        $password = $request->get('password');
        $fromMail = $request->get('fromMail');
        $fromName = $request->get('fromName');
        $signature = $request->get('signature');
        $planning = $request->get('planning');

        // Connexion au serveur de messagerie
        if ($fp=@fsockopen($host, $port, $errno, $errstr, 5)) {
            $GLOBALS['config']['Mail-IsEnabled'] = 1;
            $GLOBALS['config']['Mail-IsMail-IsSMTP'] = $mailSmtp;
            $GLOBALS['config']['Mail-Hostname'] = $hostname;
            $GLOBALS['config']['Mail-Host'] = $host;
            $GLOBALS['config']['Mail-Port'] = $port;
            $GLOBALS['config']['Mail-SMTPSecure'] = $secure;
            $GLOBALS['config']['Mail-SMTPAutoTLS'] = $autoTLS;
            $GLOBALS['config']['Mail-SMTPAuth'] = $auth;
            $GLOBALS['config']['Mail-Username'] = $user;
            $GLOBALS['config']['Mail-Password'] = encrypt($password);
            $GLOBALS['config']['Mail-From'] = $fromMail;
            $GLOBALS['config']['Mail-FromName'] = $fromName;
            $GLOBALS['config']['Mail-Signature'] = $signature;
            $GLOBALS['config']['Mail-Planning'] = $planning;

            $m=new \CJMail();
            $m->subject="Message de test";
            $m->message="Message de test.<br/><br/>La messagerie de votre application Planno est correctement paramétrée.";
            $m->to=$planning;
            $m->send();

            if ($m->error) {
                return $this->json($m->error_CJInfo);
                exit;
            } else {
                return $this->json('ok');
                exit;
            }
        } else {
            return $this->json('socket');
            exit;
        }
    }

    #[Route(path: '/ajax/edit-absence-reasons', name: 'ajax.editabsencereasons', methods: ['POST'])]
    public function editAbsenceReasons(Request $request): Response
    {
        // CSRF Protection
        if (!$this->csrf_protection($request)) {
            $return = ['CSRF token error', 'error'];
            $response = new Response();
            $response->setContent(json_encode($return));
            $response->setStatusCode(200);
            return $response;
        }

        $data = $request->get('data');

        $reasons = $this->entityManager->getRepository(AbsenceReason::class)->findAll();
        foreach ($reasons as $reason) {
            $this->entityManager->remove($reason);
        }
        $this->entityManager->flush();

        foreach ($data as $r) {
            $r[2] = isset($r[2]) ? $r[2] : 0;
            $r[3] = isset($r[3]) ? $r[3] : 'A';
            $reason = new AbsenceReason();
            $reason->setValue($r[0]);
            $reason->setRank($r[1]);
            $reason->setType($r[2]);
            $reason->setNotificationWorkflow($r[3]);
            $reason->setTeleworking($r[4]);
            $this->entityManager->persist($reason);
        }
        $this->entityManager->flush();

        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setStatusCode(200);
        return $response;
    }


    #[Route(path: '/ajax/holiday-absence-control', name: 'ajax.holiday.absence.control', methods: ['GET'])]
    public function holidayAbsenceControl(Request $request): Response
    {
      $session = $request->getSession();

      $id = $request->get('id');
      $type = $request->get('type');
      $config_name = ($type == "holiday") ? "Conges" : "Absences";
      $groupe = $request->get('groupe');
      $debut = $request->get('debut');
      $fin = $request->get('fin');
      $perso_ids = $request->get('perso_ids');
      $perso_ids = json_decode(html_entity_decode($perso_ids, ENT_QUOTES|ENT_IGNORE, "UTF-8"), true);

      // Get comma separated sites for agent
      $sites = implode(',', $this->entityManager->getRepository(Agent::class)->getSitesForAgents($perso_ids));

      $fin = $fin ?? str_replace('00:00:00', '23:59:59', $debut);
      $result = array();

      $p = new \personnel();
      $p->supprime=array(0,1,2);
      $p->fetch();
      $agents = $p->elements;

      // Pour chaque agent, contrôle si autre absence, si placé sur planning validé, si placé sur planning en cours d'élaboration
      foreach ($perso_ids as $perso_id) {
          if ($type == 'absence') {
              $result['users'][$perso_id]=array("perso_id"=>$perso_id, "autresAbsences"=>array(), "planning"=>null);

              // Contrôle des autres absences
              if ($groupe) {
                  // S'il s'agit de la modification d'un groupe, contrôle s'il y a d'autres absences en dehors du groupe
                  $db=new \db();
                  $db->select("absences", null, "`perso_id`='$perso_id' AND `groupe`<>'$groupe' AND ((debut<='$debut' AND fin>'$debut') OR (debut<'$fin' AND fin>='$fin') OR (debut>='$debut' AND fin <='$fin'))", "ORDER BY `debut`, `fin`");
              } else {
                  // S'il ne s'agit pas d'un groupe, contrôle s'il y a d'autre absences en dehors de celle sélectionnée
                  $db=new \db();
                  $db->select("absences", null, "`perso_id`='$perso_id' AND `id`<>'$id' AND ((debut<='$debut' AND fin>'$debut') OR (debut<'$fin' AND fin>='$fin') OR (debut>='$debut' AND fin <='$fin'))", "ORDER BY `debut`, `fin`");
              }

              if ($db->result) {
                  foreach ($db->result as $elem) {
                      // Si absence sur une seule journée
                      if (substr($elem['debut'], 0, 10) == substr($elem['fin'], 0, 10)) {
                          // Si journée complète
                          if (substr($elem['debut'], -8) == '00:00:00' and substr($elem['fin'], -8) == '23:59:59') {
                              $absence = "le ".dateFr($elem['debut']). " ({$elem['motif']})";
                          // Si journée incomplète
                          } else {
                              $absence = "le ".dateFr($elem['debut'])." entre ".heure2(substr($elem['debut'], -8))." et ".heure2(substr($elem['fin'], -8)). " ({$elem['motif']})";
                          }
                      }
                      // Si absence sur plusieurs journées
                      else {
                          // Si journées complètes
                          if (substr($elem['debut'], -8) == '00:00:00' and substr($elem['fin'], -8) == '23:59:59') {
                              $absence = "entre le ".dateFr($elem['debut'])." et le ".dateFr($elem['fin']). " ({$elem['motif']})";
                          // Si journées incomplètes
                          } else {
                              $absence = "entre le ".dateFr($elem['debut'])." ".heure2(substr($elem['debut'], -8))." et le ".dateFr($elem['fin'])." ".heure2(substr($elem['fin'], -8)). " ({$elem['motif']})";
                          }
                      }

                      $result['users'][$perso_id]["autresAbsences"][] = $absence;
                  }
              }
          } elseif ($type == 'holiday') {
              if ($holiday_exists = \conges::exists($perso_id, $debut, $fin, $id)) {
                  $result['users'][$perso_id]['holiday'] = 'du ' . dateFr($holiday_exists['from'], true) . ' au ' . dateFr($holiday_exists['to'], true);
              }
          }

          // Contrôle si placé sur planning validé
          if ($this->config("$config_name-apresValidation") == 0) {
              $datesValidees=array();
              $dbprefix = $this->config('dbprefix');
              $req = "SELECT `date`,`site` FROM `{$dbprefix}pl_poste` WHERE `perso_id`='$perso_id' "
                . "AND CONCAT_WS(' ',`date`,`debut`)<'$fin' AND CONCAT_WS(' ',`date`,`fin`)>'$debut' "
                . "GROUP BY `date`;";

              $db = new \db();
              $db->query($req);
              if ($db->result) {
                  foreach ($db->result as $elem) {
                      $db2 = new \db();
                      $db2->select2("pl_poste_verrou", "*", array("date"=>$elem['date'], "site"=>$elem['site'], "verrou2"=>"1"));
                      if ($db2->result) {
                          $datesValidees[] = dateFr($elem['date']);
                      }
                  }
              }
              if (!empty($datesValidees)) {
                  $result['users'][$perso_id]["planning_validated"]=implode(" ; ", $datesValidees);
              }
          }

          // Ajoute le nom de l'agent
          $result['users'][$perso_id]['nom'] = nom($perso_id, 'nom prenom', $agents);
      }

      // Contrôle si placé sur des plannings en cours d'élaboration;
      if ($this->config("$config_name-planningVide") == 0) {
          // Dates à contrôler
          $date_debut = substr($debut, 0, 10);
          $date_fin = substr($fin, 0, 10);

          // Tableau des plannings en cours d'élaboration
          $planningsEnElaboration=array();

          if ($sites != "") {
              // Pour chaque dates
              $date = $date_debut;
              while ($date <= $date_fin) {
                  // Vérifie si les plannings de tous les sites sont validés
                  $db = new \db();

                  $db->select2("pl_poste_verrou", "*", array("date"=>$date, "verrou2"=>"1", "site" => "IN $sites"));
                  // S'ils ne sont pas tous validés, vérifie si certains d'entre eux sont commencés
                  if ($db->nb < count($result)) {
                      // TODO : ceci peut être amélioré en cherchant en particulier si les sites non validés sont commencés, car les sites non validés et non commencés ne nous interressent pas.
                      // for($i=1;$i<=$this->config('Multisites-nombre');$i++){} // Attention, faire une première requête si $db->nb=0 pour éviter les erreurs foreach not array
                      // Le nom des sites pourrait également être retourné

                      $db2 = new \db();
                      $db2->select2("pl_poste", "id", array("date"=>$date, "site" => "IN $sites"));
                      // Si tous les sites ne sont pas validés et si certains sont commencés, on affichera la date correspondante
                      if ($db2->result) {
                          $planningsEnElaboration[]=date("d/m/Y", strtotime($date));
                      }
                  }
                  $date = date("Y-m-d", strtotime($date." +1 day"));
              }
          }

          // Affichage des dates correspondantes aux plannings en cours d'élaboration
          $result["planning_started"] = implode(" ; ", $planningsEnElaboration);
      }

      // Check if logged in user is admin
      list($adminN1, $adminN2) = $this->entityManager
          ->getRepository(Agent::class)
          ->setModule($type == 'absence' ? 'absence' : 'holiday')
          ->forAgent($perso_ids[0])
          ->getValidationLevelFor($session->get('loginId'));

      $result['admin'] = ($adminN1 or $adminN2);

      if ($this->config('Absences-blocage') == 1) {
          $absenceBlockHelper = new AbsenceBlockHelper();
          $result['has_block'] = $absenceBlockHelper->hasBlock($debut, $fin);
      }

      $result = json_encode($result);

      $response = new Response();
      $response->setContent($result);
      $response->setStatusCode(200);
      return $response;
    }

    /* Enregistre la liste des groupes de postes et des étages dans la base de données
     * Appelé lors du clic sur le bouton "Enregistrer" de la dialog box "Liste des groupes" ou "Lsite des étages" à partir de la fiche poste
     * TODO: Les étages peuvent être supprimés s'ils sont attachés à des postes supprimés. TODO : permettre la restauration des étages lors de la restauration des postes (via restauration des tableaux).
     * TODO: Les groupes peuvent être supprimés s'ils sont attachés à des postes supprimés. TODO : permettre la restauration des groupes lors de la restauration des postes (via restauration des tableaux).
     * TODO: Faire en sorte de conserver les ID dans la base de données et façon à les utiliser comme clé.
     */
    #[Route(path: '/ajax/update-select-options', name: 'ajax.update.select.options', methods: ['POST'])]
    public function updateSelectOptions(Request $request)
    {
        if (!$this->csrf_protection($request)) {
           return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->get('CSRFToken');
        $menu = $request->get('menu');
        $option = $request->get('option');

        $tab = $_POST['tab'];

        // New method to use for updating menus
        if (in_array($menu, array('etages', 'groupes'))) {
            $tab = json_decode($tab);

            // Deleting items put in trash
            $ids = array();
            foreach ($tab as $elem) {
                $ids[] = $elem->id;
            }
            $ids = implode(',', $ids);

            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("select_$menu", ['id' => "NOT IN$ids"]);

            // Adding new items
            $db_ids = array();
            $db=new \db();
            $db->select("select_$menu");
            if(!empty($db->result)){
                foreach ($db->result as $elem) {
                    $db_ids[] = $elem['id'];
                }
            }

            foreach ($tab as $elem) {
                if (!in_array($elem->id, $db_ids)) {
                    $db = new \db();
                    $db->CSRFToken = $CSRFToken;
                    $db->insert("select_$menu", ["valeur" => $elem->value, "rang" => $elem->place]);
                } else {
                    $db = new \db();
                    $db->CSRFToken = $CSRFToken;
                    $db->update("select_$menu", ["rang" => $elem->place], ["id" => $elem->id]);
                }
            }
            return new Response(json_encode('ok'));
        }

        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("select_$menu");
        foreach ($tab as $elem) {
            $elements = array("valeur"=>$elem[0],"rang"=>$elem[1]);
            if ($option == 'type') {
                $elements['type'] = $elem[2];
            }
            if ($option == 'categorie') {
                $elements['categorie'] = $elem[2];
            }

            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("select_$menu", $elements);
        }
        return new Response(json_encode('ok'));
    }
}
