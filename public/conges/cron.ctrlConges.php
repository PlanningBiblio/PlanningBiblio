<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/cron.ctrlConges.php
Création : 21 juillet 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Envoie un mail aux N+1 pour les informer des congés non validés à venir.
Nombre de jours ouvrés à contrôler paramétrable dans Administration / Configuration / Congés
Les samedis et dimanches (si bibliothèque ouverte le dimanche) sont contrôlés en plus :
ex : 3 jours ouvrés à contrôler, le test du mercredi controlera le mercredi, jeudi, le vendredi, le samedi
ET le lundi suivant (3 jours ouvrés + samedi + jour courant)

@note : Modifiez le crontab de l'utilisateur Apache (ex: #crontab -eu www-data) en ajoutant les 2 lignes suivantes :
# Controle du planning du lundi au vendredi à 7h
0 7 * * 1-5 /usr/bin/php5 -f /var/www/html/planning/conges/cron.ctrlConges.php
Remplacer si besoin le chemin d'accès au programme php et le chemin d'accès à ce fichier
@note : Modifiez la variable $path suivante en renseignant le chemin absolu vers votre dossier planningBiblio
*/

session_start();

/** $version=$argv[0]; permet d'interdire l'execution de ce script via un navigateur
 *  Le fichier config.php affichera une page "accès interdit si la $version n'existe pas
 *  $version prend la valeur de $argv[0] qui ne peut être fournie que en CLI ($argv[0] = chemin du script appelé en CLI)
 */

// $version=$argv[0]; = sécurité : autorise l'execution du script en CLI, l'interdit en HTTP
$version=$argv[0];

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../../init/init_entitymanager.php';
require_once __DIR__ . '/../include/function.php';
require_once __DIR__ . '/class.conges.php';
require_once __DIR__ . '/../personnel/class.personnel.php';

use App\Model\Agent;
use App\Model\Manager;

$CSRFToken = CSRFToken();

if (!$config['Conges-Rappels']) {
    logs("Rappels congés désactivés", "Rappels-conges", $CSRFToken);
    exit;
}

// Gestion des sites

// Dates à controler
$jours=$config['Conges-Rappels-Jours'];

// Recherche la date du jour et les $jours suivants
$dates=array();
for ($i=0;$i<=$jours;$i++) {
    $time=strtotime("+ $i days");
    $jour_semaine=date("w", $time);

    // Si le jour courant est un dimanche et que la bibliothèque n'ouvre pas les dimanches, on ne l'ajoute pas
    if ($jour_semaine!=0 or $config['Dimanche']) {
        $dates[]=date("Y-m-d", $time);
    }

    // Si le jour courant est un samedi, nous recherchons 2 jours supplémentaires pour avoir le bon nombre de jours ouvrés.
    // Nous controlons également le samedi et le dimanche
    if ($jour_semaine==6) {
        $jours=$jours+2;
    }
}

$debut = $dates[0];
$fin = $dates[sizeof($dates) -1];


/**
 * Dates de contrôle         $debut                  $fin
 * Dates des congés     |---------------|
 *                      |------------------------------|
 *                      |--------------------------------------|
 *                             |----------------|
 *                                      |-------|
 *                                      |--------------|
 *                                      |----------------------|
 * WHERE debut < $fin 23:59:59 AND fin > $debut 00:00:00
 */
 
// Création du message qui sera envoyé par e-mail
$data=array();

// Recherches des informations sur les agents
$agentRepository = $entityManager->getRepository(Agent::class)
    ->findBy(['supprime' => 0], ['nom' => 'ASC']);

$agents = [];
foreach ($agentRepository as $a) {
    $a->notification_level1 = [];
    $a->notification_level2 = [];
    $agents[$a->id()] = $a;
}

if ($config['Absences-notifications-agent-par-agent']) {
    $manager = $entityManager->getRepository(Manager::class)
        ->findAll();

    foreach ($agents as &$a) {
        foreach ($manager as $m) {
            if ($a->id() == $m->perso_id()->id()) {
                if ($m->notification_level1()) {
                    $a->notification_level1[] = $m->responsable()->mail();
                }
                if ($m->notification_level2()) {
                    $a->notification_level2[] = $m->responsable()->mail();
                }
            }
        }
    }
}

// Recherche des congés non-validés
$db=new db();
$db->select2('conges', null, array('debut' => "<$fin 23:59:59", 'fin' => ">$debut 00:00:00", 'valide' => '0', 'supprime' => '0', 'information' => '0'));

// Assemble les informations des congés et des agents
if ($db->result) {
    foreach ($db->result as $elem) {
        $agent = $agents[$elem['perso_id']];

        $tmp = $elem;
        $tmp['nom'] = $agent->nom();
        $tmp['prenom'] = $agent->prenom();
        $tmp['destinataires'] = [];

        // Consider the validation scheme (config Absences-notifications-agent-par-agent)
        if ($config['Absences-notifications-agent-par-agent']) {
            if ($elem['valide_n1'] == 0 and $elem['valide'] == 0) {
                $tmp['destinataires'] = $agent->notification_level1;
            }

            if ($elem['valide_n1'] != 0 and $elem['valide'] == 0) {
                $tmp['destinataires'] = $agent->notification_level2;
            }

        } else {
            // TODO : Use Absences-notifications-A1, Absences-notifications-B1 instead of Conges-Rappels-N1, then remove param Conges-Rappels-N1
            // Ajoute les destinataires pour les congés n'étant pas validés en N1 en fonction du paramètre $config['Conges-Rappels-N1']
            if ($elem['valide_n1'] == 0 and $elem['valide'] == 0) {
                $destN1 = json_decode(html_entity_decode($config['Conges-Rappels-N1'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));
                if (is_array($destN1)) {
                    if (in_array('Mail-Planning', $destN1)) {
                        $tmp['destinataires'] = array_merge($tmp['destinataires'], $agent->get_planning_unit_mails());
                    }
                    if (in_array('mails_responsables', $destN1)) {
                        $tmp['destinataires'] = array_merge($tmp['destinataires'], $agent->get_manager_emails());
                    }
                }
            }

            // TODO : Use Absences-notifications-A3, Absences-notifications-B3 instead of Conges-Rappels-N2, then remove param Conges-Rappels-N2
            // Ajoute les destinataires pour les congés n'étant pas validés en N2 en fonction du paramètre $config['Conges-Rappels-N2']
            if ($elem['valide_n1'] != 0 and $elem['valide'] == 0) {
                $destN2 = json_decode(html_entity_decode($config['Conges-Rappels-N2'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));
                if (is_array($destN2)) {
                    if (in_array('Mail-Planning', $destN2)) {
                        $tmp['destinataires'] = array_merge($tmp['destinataires'], $agent->get_planning_unit_mails());
                    }
                    if (in_array('mails_responsables', $destN2)) {
                        $tmp['destinataires'] = array_merge($tmp['destinataires'], $agent->get_manager_emails());
                    }
                }
            }
        }

        // Regroupe les informations par destinaire pour des envois uniques
        $tmp['destinataires'] = array_unique($tmp['destinataires']);

        foreach ($tmp['destinataires'] as $dest) {
            if (!isset($data[$dest])) {
                $data[$dest] = array('destinaire' => $dest);
            }
            $data[$dest][] = $tmp;
        }
    }
}

// Création du message pour chaque destinataire
foreach ($data as $dest) {
    $to = $dest['destinaire'];
    unset($dest['destinaire']);
  
    if (count($dest)>1) {
        $subject = "Congés en attente de validation du " . dateFr($debut) . " au " . dateFr($fin);
        $msg = "<p>Bonjour,</p><p>Les congés suivants ne sont pas validés.</p>\n";
    } else {
        $subject = "Congé en attente de validation du " . dateFr($debut) . " au " . dateFr($fin);
        $msg = "<p>Bonjour,</p><p>Le congé suivant n'est pas validé.</p>\n";
    }
  
    // Affichage de tous les congés non validé le concernant
    $msg.="<ul>\n";
    foreach ($dest as $conge) {
        $link = $config['URL'] . "/holiday/edit/{$conge['id']}";

        $msg.="<li style='margin-bottom:15px;'>\n";
        $msg.="<strong>{$conge['nom']} {$conge['prenom']}</strong><br/>\n";
        $msg.="<strong>Du ".dateFr($conge['debut'], true)." à ".dateFr($conge['fin'], true)."</strong><br/><br/>\n";
        $msg.="Demandé le ".dateFr($conge['saisie'], true)." par ".nom($conge['saisie_par'], $agents)."<br/>\n";
        if ($conge['valide_n1'] > 0) {
            $msg.="Validation niveau 1 : Accepté le ".dateFr($conge['validation_n1'], true)." par ".nom($conge['valide_n1'], $agents)."<br/>\n";
        }
        $msg.="<a href='$link' target='_blank'>$link</a>\n";
        $msg.="</li>\n";
    }
    $msg.="</ul>\n";
  
    $m=new CJMail();
    $m->to=$to;
    $m->subject=$subject;
    $m->message=$msg;
    $m->send();
    if ($m->error) {
        logs($m->error, "Rappels-conges", $CSRFToken);
    }
}
