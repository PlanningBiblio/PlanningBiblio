<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\AbsenceDocument;
use App\Model\Manager;
use App\Model\Agent;

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

        // Agents that can validate absence to level 1.
        $manager_level1 = array();
        $manager_level2 = array();
        foreach ($agents as $elem) {
            $droits = json_decode(html_entity_decode($elem['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));
            if ($droits == null) {
              continue;
            }

            for ($i = 1; $i <= $nbSites; $i++) {
                // FIXME Should rights for working hours
                // be take into account (1100, 1200) ?
                if (in_array((200+$i), $droits)) {
                    $manager_level1[$elem['id']] = $elem;
                }

                if (in_array((500+$i), $droits)) {
                    $manager_level2[$elem['id']] = $elem;
                }
            }
        }

        foreach ($agents as &$agent) { // Filtre des agents service public / administratif
            if ($agent['actif'] != $actif) {
                continue;
            }
            $id = $agent['id'];
            $sites = null;

            $agent_model = $this->entityManager->find(Agent::class, $id);
            $managers = $agent_model->getManagers();

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
                $sites=!empty($tmp)?implode(", ", $tmp):null;
            }

            $agent['sites_list'] = $sites;
            $agent['managers'] = $managers;
            $agents_liste[] = $agent;
        }

        $this->templateParams(
            array(
                "actif"                => $actif,
                "agents"               => $agents_liste,
                "manager_level1"       => $manager_level1,
                "manager_level2"       => $manager_level2,
                "CSRFToken"            => $GLOBALS['CSRFSession'],
                "nbSites"              => $nbSites

            )
        );

        return $this->output('notifications/index.html.twig');

    }

    /**
     * @Route("/notification", name="notification.save", methods={"POST"})
     */
    public function save(Request $request){
        $agents = $request->get('agents');
        $responsables = $request->get('responsables');
        $notifications = $request->get('notifications');
        $responsablesl2 = $request->get('responsablesl2');
        $notificationsl2 = $request->get('notificationsl2');
        $CSRFToken = $request->get('CSRFToken');

        $agents = html_entity_decode($agents, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $responsables = html_entity_decode($responsables, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $notifications = html_entity_decode($notifications, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $responsablesl2 = html_entity_decode($responsablesl2, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $notificationsl2 = html_entity_decode($notificationsl2, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        $agents = json_decode($agents);
        $responsables = json_decode($responsables);
        $notifications = json_decode($notifications);
        $responsablesl2 = json_decode($responsablesl2);
        $notificationsl2 = json_decode($notificationsl2);

        $this->entityManager->getRepository(Manager::class)->deleteForAgents($agents);
        $this->entityManager->getRepository(Manager::class)
            ->addForAgentsLevel1($agents, $responsables, $notifications)
            ->addForAgentsLevel2($agents, $responsablesl2, $notificationsl2);

        return $this->json('ok');
    }
}