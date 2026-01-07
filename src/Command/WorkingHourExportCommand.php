<?php

namespace App\Command;

use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\WorkingHour;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');

#[AsCommand(
    name: 'app:workinghour:export',
    description: 'Export working hours to a CSV file',
)]
class WorkingHourExportCommand extends Command
{
    use \App\Traits\LoggerTrait;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->entityManager->getRepository(Config::class)->getAll();

        if (file_exists(__DIR__ . '/../../custom_options.php')) {
            include __DIR__ . '/../../custom_options.php';
        }

        $tmpDir = sys_get_temp_dir();

        $CSVFile = $config['PlanningHebdo-ExportFile'] ?? $tmpDir . '/export-planno-edt.csv';
        $days_before = $config['PlanningHebdo-ExportDaysBefore'] ?? 15;
        $days_after = $config['PlanningHebdo-ExportDaysAfter'] ?? 60;
        $agentIdentifier = $config['PlanningHebdo-ExportAgentId'] ?? 'matricule';

        // Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
        $lockFile = $tmpDir . '/plannoWorkingHourExport.lock';

        if (file_exists($lockFile)) {
            $fileTime = filemtime($lockFile);
            $time = time();
            // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
            if ($time - $fileTime > 600) {
                unlink($lockFile);
                // Si le fichier existe et date de moins de 10 minutes, on quitte
            } else {
                $message = 'The last execution took place less than 10 minutes ago';
                $this->log($message, 'WorkingHourExport');
                $io->warning($message);

                return Command::SUCCESS;
            }
        }
        // On créé le fichier .lock
        $inF=fopen($lockFile, "w");

        // On recherche tout le personnel actif
        $agents = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0]);

        if (empty($agents)) {
            $message = 'No agent was found';
            $this->log($message, 'WorkingHourExport');
            $io->warning($message);

            return Command::SUCCESS;
        }

        // $list sera un tableau contenant pour chaque date et pour chaque agent, les heures de présence
        // format array(array('id_agent', 'date', 'indicateur_SP', 'debut1', 'fin1', 'debut2', 'fin2', 'debut3', 'fin3'))
        $list = array();

        $current = date('Y-m-d', strtotime("-$days_before days"));
        $end = date('Y-m-d', strtotime("+$days_after days"));

        while ($current < $end) {

            // Recheche le jour de la semaine (lundi (0) à dimanche (6)) et l'offest (décalage si semaine paire/impaire ou toute autre rotation)
            $d=new \datePl($current);

            // jour de la semaine lundi = 0 ,dimanche = 6
            $jour = $d->position-1;
            if ($jour==-1) {
                $jour=6;
            }

            // Si utilisation de 2 plannings hebdo (semaine paire et semaine impaire)
            // Si semaine paire, position +=7 : lundi A = 0 , lundi B = 7 , dimanche B = 13
            if (!$config['EDTSamedi'] or $config['PlanningHebdo']) {
                $jour += ($d->semaine3 - 1) * 7;
            }

            $agentIds = [];
            foreach ($agents as $agent) {
                $agentIds[$agent->getId()] = match ($agentIdentifier) {
                    'email' => $agent->getMail(),
                    'login' => $agent->getLogin(),
                    'matricule' => $agent->getEmployeeNumber(),
                };
            }

            // Recherche les heures de présence valides ce jour pour tous les agents
            $workinghours = $this->entityManager->getRepository(WorkingHour::class)->get($current, $current);

            foreach ($workinghours as $elem) {

                // Récupération de l'dentifiant de l'agent (ex : login, adresse email ou ID Harpege renseigné dans le champ "matricule")
                // Si l'identifiant n'est pas renseigné dans Planno (ex : champ matricule vide), nous n'importons pas l'agent (donc continue) (Demande initiale de la société Bodet Software)
                if (empty($agentIds[$elem->getUser()])) {
                    continue;
                }

                $agentId = $agentIds[$elem->getUser()];

                // Mise en forme du tableau temps
                /** Le tableau $elem->getWorkingHours()[$jour] est constitué comme suit :
                 * 0 => début période 1,                                           *
                 * 1 => fin période 1,
                 * 2 => début période 2,
                 * 5 => fin période 2 si pause2 activée, sinon null,
                 * 6 => début période 3 si pause 2, sinon null,
                 * 3 => fin de journée (peut être fin de période 1, 2 ou 3)
                 */
                $temps = array();

                if (isset($elem->getWorkingHours()[$jour])) {

                    // Première période : matinée : index 0 (début) et 1 (fin)
                    if (!empty($elem->getWorkingHours()[$jour][0]) and !empty($elem->getWorkingHours()[$jour][1])) {
                        $temps[] = substr($elem->getWorkingHours()[$jour][0], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][1], 0, 5);
                    }
                    // Deuxième période : après-midi : index 2 (début) et 3 (fin)
                    // Seulement s'il n'y a pas de 3ème période (voir cas suivant)
                    if (!empty($elem->getWorkingHours()[$jour][2]) and !empty($elem->getWorkingHours()[$jour][3]) and empty($elem->getWorkingHours()[$jour][5])) {
                        $temps[] = substr($elem->getWorkingHours()[$jour][2], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][3], 0, 5);
                    }
                    // Si 2 pauses sont enregistrées, les index 5 et 6 viennent s'intercaler entre les index 2 et 3. Les périodes sont donc composées des index 2 (début1) et 5 (fin1) et 6 (début2) et 3 (fin2)
                    if (!empty($elem->getWorkingHours()[$jour][2]) and !empty($elem->getWorkingHours()[$jour][5])) {
                        $temps[] = substr($elem->getWorkingHours()[$jour][2], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][5], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][6], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][3], 0, 5);
                    }
                    // Journée complète : heures enregistrées sans pause entre les index 0 et 3
                    if (!empty($elem->getWorkingHours()[$jour][0]) and empty($elem->getWorkingHours()[$jour][2]) and empty($elem->getWorkingHours()[$jour][5]) and !empty($elem->getWorkingHours()[$jour][3])) {
                        $temps[] = substr($elem->getWorkingHours()[$jour][0], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][3], 0, 5);
                    }
                    // Journée complète : heures enregistrées sans pause entre les index 0 et 5
                    if (!empty($elem->getWorkingHours()[$jour][0]) and empty($elem->getWorkingHours()[$jour][2]) and !empty($elem->getWorkingHours()[$jour][5]) and empty($elem->getWorkingHours()[$jour][3])) {
                        $temps[] = substr($elem->getWorkingHours()[$jour][0], 0, 5);
                        $temps[] = substr($elem->getWorkingHours()[$jour][5], 0, 5);
                    }
                }

                $heures_supp = null ;

                $list[] = array_merge(array($current, $agentId, $heures_supp), $temps);
            }

            $current = date('Y-m-d', strtotime($current." + 1 day"));
        }

        // On ouvre le fichier CSV
        $this->log("Exportation des données vers le fichier $CSVFile", 'WorkingHourExport');

        $fp = fopen($CSVFile, 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        // Unlock
        unlink($lockFile);
        $this->log("Exportation terminée (fichier $CSVFile)", 'WorkingHourExport');

        if ($output->isVerbose()) {
            $io->success('CSV export completed successfully.');
        }

        return Command::SUCCESS;
    }
}
