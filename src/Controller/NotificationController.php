
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
     * @Route("/notification", name="notification.index", methods={"GET", "POST"})
     */
    public function index(Request $request){
        // Initialisation des variables
        $actif = $request->get("actif");

        if (!$actif) {
            $actif = isset($_SESSION['perso_actif']) ? $_SESSION['perso_actif'] : 'Actif';
        }
        $_SESSION['perso_actif'] = $actif;

        $option1 = $actif == 'Actif' ? "selected='selected'" : null;
        $option2 = $actif == 'Inactif' ? "selected='selected'" : null;

        $p = new \personnel();
        $p->supprime = array(0);
        $p->responsablesParAgent = true;
        $p->fetch("nom,prenom");
        $agents = $p->elements;

        // Agents ayant les droits de validation d'absence N1
        $agents_responsables = array();
        foreach ($agents as $elem) {
            for ($i = 1; $i <= $config['Multisites-nombre']; $i++) {
                if (in_array((200+$i), json_decode(html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8')))) {
                    $agents_responsables[$elem['id']] = $elem;
                }
            }
        }

        foreach ($agents as $agent) { // Filtre des agents service public / administratif
            if ($agent['actif'] != $actif) {
                continue;
            }
            $id=$agent['id'];
            $agent['service']=str_replace("`", "'", $agent['service']);
            if ($config['Multisites-nombre']>1) {
                $tmp=array();
                if (!empty($agent['sites'])) {
                    foreach ($agent['sites'] as $site) {
                        if ($site) {
                            $tmp[]=$config["Multisites-site{$site}"];
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
        }

    }

}