<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\Model\AbsenceReason;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../public/include/function.php');
require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class AjaxController extends BaseController
{
    /**
     * @Route("/ajax/holiday-credit", name="ajax.holidaycredit", methods={"GET"})
     */
    public function checkCredit(Request $request)
    {
        // Initilisation des variables
        $debut =dateSQL($request->get('debut'));
        $fin =dateSQL($request->get('fin'));
        $hre_debut = $request->get('hre_debut');
        $hre_fin = $request->get('hre_fin');
        $perso_id = $request->get('perso_id');
        $is_recover = $request->get('is_recover');

        $c = new \conges();
        $recover = $c->calculCreditRecup($perso_id, $debut);

        $holidayHlper = new HolidayHelper(array(
            'start' => $debut,
            'hour_start' => $hre_debut,
            'end' => $fin,
            'hour_end' => $hre_fin,
            'perso_id' => $perso_id,
            'is_recover' => $is_recover
        ));
        $result = $holidayHlper->getCountedHours();

        $result['recover'] = $recover;

        return $this->json($result);
    }

    /**
     * @Route("/ajax/holiday-delete", name="ajax.holidaydelete", methods={"GET"})
     */
    public function deleteHoliday(Request $request)
    {
        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');

        $c = new \conges();
        $c->id = $id;
        $c->CSRFToken = $CSRFToken;
        $c->delete();

        return $this->json("Holiday deleted");
    }

    /**
     * @Route("/ajax/mail-test", name="ajax.mailtest", methods={"POST"})
     */
    public function mailTest(Request $request)
    {

        include_once(__DIR__ . '/../../public/include/config.php');
        include_once(__DIR__ . '/../../public/include/function.php');

        $mailSmtp = $request->get('mailSmtp');
        $wordwrap = $request->get('wordwrap');
        $hostname = $request->get('hostname');
        $host = $request->get('host');
        $port = $request->get('port');
        $secure = $request->get('secure');
        $auth = $request->get('auth');
        $user = $request->get('user');
        $password = $request->get('password');
        $fromMail = $request->get('fromMail');
        $fromName = $request->get('fromName');
        $signature = $request->get('signature');
        $planning = $request->get('planning');

        // Connexion au serveur de messagerie
        if ($fp=@fsockopen($host, $port, $errno, $errstr, 5)) {
            $config['Mail-IsEnabled'] = 1;
            $config['Mail-IsMail-IsSMTP'] = $mailSmtp;
            $config['Mail-WordWrap'] = $wordwrap;
            $config['Mail-Hostname'] = $hostname;
            $config['Mail-Host'] = $host;
            $config['Mail-Port'] = $port;
            $config['Mail-SMTPSecure'] = $secure;
            $config['Mail-SMTPAuth'] = $auth;
            $config['Mail-Username'] = $user;
            $config['Mail-Password'] = encrypt($password);
            $config['Mail-From'] = $fromMail;
            $config['Mail-FromName'] = $fromName;
            $config['Mail-Signature'] = $signature;
            $config['Mail-Planning'] = $planning;

            $m=new \CJMail();
            $m->subject="Message de test, Planning Biblio";
            $m->message="Message de test, Planning Biblio<br/><br/>La messagerie de votre application Planning Biblio est correctement param&eacute;tr&eacute;e.";
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

    /**
     * @Route("/ajax/edit-absence-reasons", name="ajax.editabsencereasons", methods={"POST"})
     */
    public function editAbsenceReasons(Request $request)
    {
        $CSRFToken = $request->get('CSRFToken');
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
            $reason->valeur($r[0]);
            $reason->rang($r[1]);
            $reason->type($r[2]);
            $reason->notification_workflow($r[3]);
            $this->entityManager->persist($reason);
        }
        $this->entityManager->flush();

        #return $this->json("Ok");
        return $this->json($data);
    }

    /**
     * @Route("/ajax/change-password", name="ajax.changepassword", methods={"POST"})
     */
    public function changePassword(Request $request)
    {
        $agent_id = $request->get('id');
        $password = $request->get('password');

        $agent = $this->entityManager->find(Agent::class, $agent_id);

        $response = new Response();
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);

            return $response;
        }

        if (!$password) {
            $response->setContent('Missing password');
            $response->setStatusCode(400);

            return $response;
        }

        $password = password_hash($password, PASSWORD_BCRYPT);
        $agent->password($password);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        $response->setContent('Password successfully changed');
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * @Route("/ajax/holiday-absence-control", name="ajax.holiday.absence.control", methods={"GET"})
     */
    public function holidayAbsenceControl(Request $request)
    {
      $id = $request->get('id');
      $type = $request->get('type');
      $config_name = ($type == "holiday") ? "Conges" : "Absences";
      $groupe = $request->get('groupe');
      $debut = $request->get('debut');
      $fin = $request->get('fin');
      $perso_ids = $request->get('perso_ids');
      $perso_ids=json_decode(html_entity_decode($perso_ids, ENT_QUOTES|ENT_IGNORE, "UTF-8"), true);

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
                  $result['users'][$perso_id]["planning_validated"]=join(" ; ", $datesValidees);
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

          // Pour chaque dates
          $date = $date_debut;
          while ($date <= $date_fin) {
              // Vérifie si les plannings de tous les sites sont validés
              $db = new \db();
              $db->select2("pl_poste_verrou", "*", array("date"=>$date, "verrou2"=>"1"));
              // S'ils ne sont pas tous validés, vérifie si certains d'entre eux sont commencés
              if ($db->nb < $this->config('Multisites-nombre')) {
                  // TODO : ceci peut être amélioré en cherchant en particulier si les sites non validés sont commencés, car les sites non validés et non commencés ne nous interressent pas.
                  // for($i=1;$i<=$this->config('Multisites-nombre');$i++){} // Attention, faire une première requête si $db->nb=0 pour éviter les erreurs foreach not array
                  // Le nom des sites pourrait également être retourné

                  $db2 = new \db();
                  $db2->select2("pl_poste", "id", array("date"=>$date));
                  // Si tous les sites ne sont pas validés et si certains sont commencés, on affichera la date correspondante
                  if ($db2->result) {
                      $planningsEnElaboration[]=date("d/m/Y", strtotime($date));
                  }
              }
              $date = date("Y-m-d", strtotime($date." +1 day"));
          }

          // Affichage des dates correspondantes aux plannings en cours d'élaboration
          $result["planning_started"] = implode(" ; ", $planningsEnElaboration);
      }

      $result = json_encode($result);

      $response = new Response();
      $response->setContent($result);
      $response->setStatusCode(200);
      return $response;
    }

}