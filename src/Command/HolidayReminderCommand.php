<?php

namespace App\Command;

use App\Model\Agent;
use App\Model\Holiday;
use App\Model\Manager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:holiday:reminder',
    description: 'Send reminders for leave to be validated',
)]
class HolidayReminderCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
            ->setHelp('
Envoie un mail aux N+1 pour les informer des congés non validés à venir.
Nombre de jours ouvrés à contrôler paramétrable dans Administration / Configuration fonctionnelle / Congés
Les samedis et dimanches (si l\'établissement est ouvert le dimanche) sont contrôlés en plus :
ex : 3 jours ouvrés à contrôler, le test du mercredi controlera le mercredi, le jeudi, le vendredi, le samedi
ET le lundi suivant (3 jours ouvrés + samedi + jour courant)
Exemple à ajouter en crontab :
# Controle du planning du lundi au vendredi à 7h
0 7 * * 1-5 /path/to/planno/bin/console app:holiday:reminder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $version = 'Symfony Command';

        require_once __DIR__ . '/../../public/include/config.php';
        require_once __DIR__ . '/../../init/init_entitymanager.php';
        require_once __DIR__ . '/../../public/include/function.php';

        // $xxxx = $GLOBALS['xxxx']; is required for unit tests
        $config = $GLOBALS['config'];
        $entityManager = $GLOBALS['entityManager'];
        $CSRFToken = CSRFToken();

        if (!$config['Conges-Rappels']) {
            $message = 'Rappels congés désactivés';
            logs($message, 'Rappels-conges', $CSRFToken);

            $io->warning($message);
            return Command::SUCCESS;
        }

        // Gestion des sites
        // Dates à controler
        $jours = $config['Conges-Rappels-Jours'];

        // Recherche la date du jour et les $jours suivants
        $dates = [];
        for ($i=0; $i <= $jours; $i++) {
            $time = strtotime("+ $i days");
            $jour_semaine = date("w", $time);

            // Si le jour courant est un dimanche et que l'établissement n'ouvre pas les dimanches, on ne l'ajoute pas
            if ($jour_semaine != 0 or $config['Dimanche']) {
                $dates[] = date('Y-m-d', $time);
            }

            // Si le jour courant est un samedi, nous recherchons 2 jours supplémentaires pour avoir le bon nombre de jours ouvrés.
            // Nous controlons également le samedi et le dimanche
            if ($jour_semaine == 6) {
                $jours += 2;
            }
        }

        $debut = $dates[0];
        $fin = $dates[count($dates) -1];

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
        $data = [];

        // Recherches des informations sur les agents
        $agentRepository = $entityManager->getRepository(Agent::class)
            ->findBy(['supprime' => 0], ['nom' => 'ASC']);

        $agents = [];
        foreach ($agentRepository as $a) {
            $a->notification_level1 = [];
            $a->notification_level2 = [];
            $agents[$a->id()] = $a;
        }

        // Look for managers when the validation scheme is enabled (config: Absences-notifications-agent-par-agent
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
        $holidays = $entityManager->getRepository(Holiday::class)->get("$debut 00:00:00", "$fin 23:59:59", false);

        // Assemble les informations des congés et des agents
        foreach ($holidays as $elem) {
            $agent = $agents[$elem->perso_id()];

            $tmp = $elem;
            $tmp->lastname = $agent->nom();
            $tmp->firstname = $agent->prenom();
            $tmp->recipients = [];

            // Consider the validation scheme (config Absences-notifications-agent-par-agent)
            if ($config['Absences-notifications-agent-par-agent']) {
                $tmp->recipients = $elem->valide_n1() == 0 ? $agent->notification_level1 : $agent->notification_level2;

            } else {
                // TODO : Use Absences-notifications-A1, Absences-notifications-B1 instead of Conges-Rappels-N1, then remove param Conges-Rappels-N1
                // Ajoute les destinataires pour les congés n'étant pas validés en N1 en fonction du paramètre $config['Conges-Rappels-N1']
                if ($elem->valide_n1() == 0) {
                    $destN1 = json_decode(html_entity_decode($config['Conges-Rappels-N1'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));

                    if (is_array($destN1)) {
                        if (in_array('Mail-Planning', $destN1)) {
                            $tmp->recipients = array_merge($tmp->recipients, $agent->get_planning_unit_mails());
                        }
                        if (in_array('mails_responsables', $destN1)) {
                            $tmp->recipients = array_merge($tmp->recipients, $agent->get_manager_emails());
                        }
                    }
                }

                // TODO : Use Absences-notifications-A3, Absences-notifications-B3 instead of Conges-Rappels-N2, then remove param Conges-Rappels-N2
                // Ajoute les destinataires pour les congés n'étant pas validés en N2 en fonction du paramètre $config['Conges-Rappels-N2']
                if ($elem->valide_n1() != 0) {
                    $destN2 = json_decode(html_entity_decode($config['Conges-Rappels-N2'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'));
                    if (is_array($destN2)) {
                        if (in_array('Mail-Planning', $destN2)) {
                            $tmp->recipients = array_merge($tmp->recipients, $agent->get_planning_unit_mails());
                        }
                        if (in_array('mails_responsables', $destN2)) {
                            $tmp->recipients = array_merge($tmp->recipients, $agent->get_manager_emails());
                        }
                    }
                }
            }

            // Regroupe les informations par destinaire pour des envois uniques
            $tmp->recipients = array_unique($tmp->recipients);
            $tmp->recipients = array_map('trim', $tmp->recipients);

            foreach ($tmp->recipients as $dest) {
                if (!isset($data[$dest])) {
                    $data[$dest] = array('recipient' => $dest);
                }
                $data[$dest][] = $tmp;
            }
        }

        // Création du message pour chaque destinataire
        foreach ($data as $dest) {
            $to = $dest['recipient'];
            unset($dest['recipient']);

            if (count($dest)>1) {
                $subject = "Congés en attente de validation du " . dateFr($debut) . " au " . dateFr($fin);
                $msg = "<p>Bonjour,</p><p>Les congés suivants ne sont pas validés.</p>\n";
            } else {
                $subject = "Congé en attente de validation du " . dateFr($debut) . " au " . dateFr($fin);
                $msg = "<p>Bonjour,</p><p>Le congé suivant n'est pas validé.</p>\n";
            }

            // Affichage de tous les congés non validé le concernant
            $msg .= "<ul>\n";
            foreach ($dest as $conge) {
                $link = $config['URL'] . "/holiday/edit/{$conge->id()}";

                $msg .= "<li style='margin-bottom:15px;'>\n";
                $msg .= "<strong>{$conge->lastname} {$conge->firstname}</strong><br/>\n";
                $msg .= '<strong>Du ' . $conge->debut()->format('d/m/Y H:i') . ' à ' .  $conge->fin()->format('d/m/Y H:i') . "</strong><br/><br/>\n";
                $msg .= 'Demandé le ' . $conge->saisie()->format('d/m/Y h:i') . ' par ' . nom($conge->saisie_par(), $agents) . "<br/>\n";
                if ($conge->valide_n1() > 0) {
                    $msg .= 'Validation niveau 1 : Accepté le ' . $conge->validation_n1()->format('d/m/Y H:i') . ' par ' . nom($conge->valide_n1(), $agents) . "<br/>\n";
                }
                $msg .= "<a href='$link' target='_blank'>$link</a>\n";
                $msg .= "</li>\n";
            }
            $msg .= "</ul>\n";

            $m = new \CJMail();
            $m->to = $to;
            $m->subject = $subject;
            $m->message = $msg;
            $m->send();
            if ($m->error) {
                logs($m->error, "Rappels-conges", $CSRFToken);
            }

            if ($output->isVerbose()) {
                $io->text("Recipient: $to\n");
                $io->text("Subject: $subject\n");
                $io->text("Message: $msg\n\n");
            }
        }

        if ($output->isVerbose()) {
            $io->success('Reminders sent for leave pending validation.');
        }

        return Command::SUCCESS;
    }
}
