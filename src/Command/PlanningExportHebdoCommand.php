<?php

namespace App\Command;

use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');
require_once __DIR__ . '/../../public/include/function.php';

#[AsCommand(
    name: 'app:planning:export-hebdo',
    description: 'Exports agents’ weekly working hours from the planning table to a CSV file',
)]
class PlanningExportHebdoCommand extends Command
{
    protected $entityManager;

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
        $CSVFile = $config['PlanningHebdo-ExportFile'] ?? '/tmp/export-planno-edt.csv';
        $days_before = $config['PlanningHebdo-ExportDaysBefore'] ?? 15;
        $days_after = $config['PlanningHebdo-ExportDaysAfter'] ?? 60;
        $agentIdentifier = $config['PlanningHebdo-ExportAgentId'] ?? 'matricule';

        $CSRFToken = CSRFToken();

        // Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
        $tmp_dir=sys_get_temp_dir();
        $lockFile=$tmp_dir."/plannoExportCSV.lock";
        //unlink($lockFile);//TODO TEST

        if (file_exists($lockFile)) {
            $fileTime = filemtime($lockFile);
            $time = time();
            // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
            if ($time - $fileTime > 600) {
                unlink($lockFile);
                // Si le fichier existe et date de moins de 10 minutes, on quitte
            } else {
                $message = 'TODO1';
                \logs($message, 'TODO', $CSRFToken);
                $io->warning($message);
                return Command::SUCCESS;
            }
        }
        // On créé le fichier .lock
        $inF=fopen($lockFile, "w");

        // On recherche tout le personnel actif
        $p= new \personnel();
        $p->supprime = array(0);
        $p->fetch();

        if (empty($p->elements)) {
            $message = 'TODO2';
            \logs($message, 'TODO', $CSRFToken);
            $io->warning($message);
            return Command::SUCCESS;
        }

        $agents = $p->elements;

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

            // Recherche les heures de présence valides ce jour pour tous les agents
            $p=new \planningHebdo();
            $p->debut=$current;
            $p->fin=$current;
            //$p->valide=true;
            $p->fetch();

            if (!empty($p->elements)) {
                foreach ($p->elements as $elem) {

                    // Récupération de l'dentifiant de l'agent (ex : login, adresse email ou ID Harpege renseigné dans le champ "matricule")
                    // Si l'identifiant n'est pas renseigné dans Planno (ex : champ matricule vide), nous n'importons pas l'agent (donc continue) (Demande initiale de la société Bodet Software)
                    if (empty($agents[$elem["perso_id"]][$agentIdentifier])) {
                        continue;
                    }

                    $agent_id = $agents[$elem["perso_id"]][$agentIdentifier];

                    // Mise en forme du tableau temps
                    /** Le tableau $elem["temps"][$jour] est constitué comme suit :
                     0 => début période 1,                                           *
                     1 => fin période 1,
                     2 => début période 2,
                     5 => fin période 2 si pause2 activée, sinon null,
                     6 => début période 3 si pause 2, sinon null,
                     3 => fin de journée (peut être fin de période 1, 2 ou 3)
                     */
                    $temps = array();

                    if (isset($elem["temps"][$jour])) {

                        // Première période : matinée : index 0 (début) et 1 (fin)
                        if (!empty($elem["temps"][$jour][0]) and !empty($elem["temps"][$jour][1])) {
                            $temps[] = substr($elem["temps"][$jour][0], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][1], 0, 5);
                        }
                        // Deuxième période : après-midi : index 2 (début) et 3 (fin)
                        // Seulement s'il n'y a pas de 3ème période (voir cas suivant)
                        if (!empty($elem["temps"][$jour][2]) and !empty($elem["temps"][$jour][3]) and empty($elem["temps"][$jour][5])) {
                            $temps[] = substr($elem["temps"][$jour][2], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][3], 0, 5);
                        }
                        // Si 2 pauses sont enregistrées, les index 5 et 6 viennent s'intercaler entre les index 2 et 3. Les périodes sont donc composées des index 2 (début1) et 5 (fin1) et 6 (début2) et 3 (fin2)
                        if (!empty($elem["temps"][$jour][2]) and !empty($elem["temps"][$jour][5])) {
                            $temps[] = substr($elem["temps"][$jour][2], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][5], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][6], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][3], 0, 5);
                        }
                        // Journée complète : heures enregistrées sans pause entre les index 0 et 3
                        if (!empty($elem["temps"][$jour][0]) and empty($elem["temps"][$jour][2]) and empty($elem["temps"][$jour][5]) and !empty($elem["temps"][$jour][3])) {
                            $temps[] = substr($elem["temps"][$jour][0], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][3], 0, 5);
                        }
                        // Journée complète : heures enregistrées sans pause entre les index 0 et 5
                        if (!empty($elem["temps"][$jour][0]) and empty($elem["temps"][$jour][2]) and !empty($elem["temps"][$jour][5]) and empty($elem["temps"][$jour][3])) {
                            $temps[] = substr($elem["temps"][$jour][0], 0, 5);
                            $temps[] = substr($elem["temps"][$jour][5], 0, 5);
                        }
                    }

                    $heures_supp = null ;

                    $list[]=array_merge(array($current, $agent_id, $heures_supp), $temps);
                }
            }

            $current = date('Y-m-d', strtotime($current." + 1 day"));
        }

        // On ouvre le fichier CSV
        logs("Exportation des données vers le fichier $CSVFile", "PlanningHebdo", $CSRFToken);

        $fp = fopen($CSVFile, 'w');

        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        // Unlock
        unlink($lockFile);
        logs("Exportation terminée (fichier $CSVFile)", "PlanningHebdo", $CSRFToken);

        if ($output->isVerbose()){
            $io->success('CSV export completed successfully.');
        }

        return Command::SUCCESS;
    }
}
