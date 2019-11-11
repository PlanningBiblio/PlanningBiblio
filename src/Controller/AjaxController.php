<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/conges/class.conges.php');

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

        $c = new \conges();
        $recover = $c->calculCreditRecup($perso_id, $debut);

        $holidayHlper = new HolidayHelper(array(
            'start' => $debut,
            'hour_start' => $hre_debut,
            'end' => $fin,
            'hour_end' => $hre_fin,
            'perso_id' => $perso_id
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

}