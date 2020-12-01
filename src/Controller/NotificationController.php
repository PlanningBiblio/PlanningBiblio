<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class NotificationController extends BaseController {

    /**
     * @Route("/notification", name="notification.index", methods={"GET"})
     */
    public function index(Request $request){
        // Initialisation des variables
        $nbSites = $this->config("Multisites-nombre");
        $actif = $request->get("actif");
        $agents_liste = array();

        if (!$actif) {
            $actif = isset($_SESSION['perso_actif']) ? $_SESSION['perso_actif'] : 'Actif';
        }
        $_SESSION['perso_actif'] = $actif;

        $p = new \personnel();
        $p->supprime = array(0);
        $p->responsablesParAgent = true;
        $p->fetch("nom,prenom");
        $agents = $p->elements;

        // Agents ayant les droits de validation d'absence N1
        $agents_responsables = array();
        foreach ($agents as $elem) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $droits = json_decode(html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));
                if ($droits != null and in_array((200+$i), $droits)) {
                    $agents_responsables[$elem['id']] = $elem;
                }
            }
        }

        foreach ($agents as &$agent) { // Filtre des agents service public / administratif
            if ($agent['actif'] != $actif) {
                continue;
            }
            $id = $agent['id'];
            $sites = null;

            $agent['service'] = str_replace("`", "'", $agent['service']);
            if ($nbSites > 1) {
                $tmp = array();
                if (!empty($agent['sites'])) {
                    foreach ($agent['sites'] as $site) {
                        if ($site) {
                            $tmp[] = $this->config("Multisites-site{$site}");
                        }
                    }
                }
                $sites=!empty($tmp)?join(", ", $tmp):null;
            }

            $responsables = array();
            foreach ($agent['responsables'] as $resp) {
                if (!empty($resp['responsable']) and array_key_exists($resp['responsable'], $agents)) {
                    $notification = $resp['notification'] ? 1 : 0 ;
                    $tmp = "<span class='resp_$id' data-resp='{$resp['responsable']}' data-notif='$notification' >";
                    $tmp .= nom($resp['responsable'], $format="nom p", $agents);
                    if ($notification) {
                        $tmp .= ' - Notifications';
                    }
                       $tmp .= "</span>";
                    $responsables[] = $tmp;
                }
            }

            if (!empty($responsables)) {
                usort($responsables, 'cmp_strip_tags');
                $responsables = implode('<br/>', $responsables);
            }

            $agent['sites_list'] = $sites;
            $agent['responsables_list'] = $responsables;
            $agents_liste[] = $agent;
        }

        $this->templateParams(
            array(
                "actif"                => $actif,
                "agents"               => $agents_liste,
                "agents_responsables"  => $agents_responsables,
                "CSRFToken"            => $GLOBALS['CSRFSession'],
                "nbSites"              => $nbSites

            )
        );

        return $this->output('notifications/index.html.twig');

    }

}