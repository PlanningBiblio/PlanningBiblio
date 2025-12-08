<?php

namespace App\Command;

use App\Entity\Absence;
use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../../legacy/Common/function.php';
require_once( __DIR__ . '/../../legacy/Class/class.personnel.php');

#[AsCommand(
    name: 'app:absence:import-csv',
    description: 'Import absences from a CSV file',
)]
class AbsenceImportCSVCommand extends Command
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

        $entityManager = $this->entityManager;
        $config = $entityManager->getRepository(Config::class)->getAll();

        if (file_exists(__DIR__ . '/../../custom_options.php')) {
            include __DIR__ . '/../../custom_options.php';
        }

        $status_extra = $config['hamac_status_extra'] ?? array();
        $status_waiting = $config['hamac_status_waiting'] ?? array(3);
        $status_validated = $config['hamac_status_validated'] ?? array(2,5);
        // Days_before is used to remove entries that have been deleted from source file.
        // If null or false : deleted entries will not be removed. If interger >= 0 : entries with end upper than today minor the specified value will be deleted
        $days_before = $config['hamac_days_before'] ?? null;
        $debug = $config['Hamac-debug'] ?? false;
        $motif = !empty(trim($config['Hamac-motif'])) ? trim($config['Hamac-motif']) : 'Hamac';

        $CSRFToken = CSRFToken();

        $this->log('Start CSV import', 'AbsenceImportCSV');

        // Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
        $tmp_dir = sys_get_temp_dir();
        $lockFile = $tmp_dir . '/plannoAbsenceImportCSV.lock';

        if (file_exists($lockFile)) {
            if ($debug) {
                $this->log('Lock file ' . $lockFile . ' exists', 'AbsenceImportCSV');
            }
            $fileTime = filemtime($lockFile);
            $time = time();
            // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
            if ($time - $fileTime > 600) {
                if ($debug) {
                    $this->log('Lock file' . $lockFile . ' is more than 10 minutes old. I delete it.', 'AbsenceImportCSV');
                }
                unlink($lockFile);
                // Si le fichier existe et date de moins de 10 minutes, on quitte
            } else {
                $message = 'Lock file is less than 10 minutes old. Exit !';
                $this->log($message, 'AbsenceImportCSV');
                $io->error($message);

                return Command::FAILURE;
            }
        } else {
            if ($debug) {
                $this->log('Lock file ' . $lockFile . ' does not exist.', 'AbsenceImportCSV');
            }
        }
        // On créé le fichier .lock
        $inF = fopen($lockFile, 'w');
        fclose($inF);

        if ($debug) {
            $this->log('Lock file ' . $lockFile . ' created', 'AbsenceImportCSV');
        }

        // On recherche tout le personnel actif
        if ($debug) {
            $this->log('On recherche tout le personnel actif', 'AbsenceImportCSV');
        }

        $p = new \personnel();
        $p->supprime = array(0);
        $p->fetch();
        $agents = $p->elements;

        // Les logins des agents qui acceptent la synchronisation depuis Hamac
        $logins = array();
        $perso_ids = array();
        $key = $config['Hamac-id'];
        if ($debug) {
            $this->log("\$key = \$config['Hamac-id'] = " . $config['Hamac-id'], 'AbsenceImportCSV');
        }

        foreach ($agents as $elem) {
            if ($debug) {
                $this->log("mail = " . $elem['mail'] . " - login = " . $elem[$key], 'AbsenceImportCSV');
            }
            if ($elem['check_hamac']) {
                if ($debug) {
                    $this->log("\$elem['check_hamac'] = true", 'AbsenceImportCSV');
                }
                $logins[] = $elem[$key];
                $perso_ids[$elem[$key]] = $elem['id'];
                if ($debug) {
                    $this->log("\$elem['id'] = " . $elem['id'] . " - \$perso_ids[\$elem[\$key]] = " . $perso_ids[$elem[$key]], 'AbsenceImportCSV');
                }
            } else {
                if ($debug) {
                    $this->log("\$elem['check_hamac'] = false", 'AbsenceImportCSV');
                }
            }
        }

        $ids_list = implode(',', $perso_ids);

        if ($debug) {
            $this->log("\$ids_list = " . $ids_list, 'AbsenceImportCSV');
        }

        // Recherche de toutes les absences déjà importées depuis Hamac
        if ($debug) {
            $this->log("Recherche de toutes les absences déjà importées depuis Hamac", 'AbsenceImportCSV');
        }

        $absences = array();
        $abs = $this->entityManager->getRepository(Absence::class)->getByUserIds($perso_ids, 'hamac');
        foreach ($abs as $elem) {
            // On indexe le tableau avec le champ UID qui n'est autre que l'id Hamac
            $absences[$elem->getUid()] = $elem;
            if ($debug) {
                $this->log("\$elem->getUid() = " . $elem->getUid() . " - \$absences[\$elem->getUid()] = " . json_encode($absences[$elem->getUid()]), 'AbsenceImportCSV');
            }
        }

        // On récupère les clés (IDs Hamac) pour vérifier si les absences du fichier Hamac sont dans la base de données
        $uids = array_keys($absences);


        // On lit le fichier CSV
        $filename = trim($config['Hamac-csv']);

        // Si le fichier n'existe pas, on quitte
        if (!file_exists($filename)) {
            if ($debug) {
                $this->log("Le fichier $filename n'existe pas, on quitte, arret du traitement", 'AbsenceImportCSV');
            }
            // Unlock
            unlink($lockFile);

            $message = 'Le fichier n\'existe pas.';
            $this->log($message, 'AbsenceImportCSV');
            $io->error($message);

            return Command::FAILURE;
        }

        // Status à importer
        $status = explode(',', $config['Hamac-status']);
        $status = array_merge($status, $status_extra);

        if ($debug) {
            $this->log("Status à importer : \$config['Hamac-status'] " . $config['Hamac-status'], 'AbsenceImportCSV');
        }




        // Absences DB / file : used to remove entries deleted from source file
        $absences_file = array();
        $absences_db = array();

        if (is_numeric($days_before)) {
            $days_before = (int) $days_before;
            $end = date('Y-m-d 00:00:00', strtotime("- $days_before days"));
            $absx = $this->entityManager->getRepository(Absence::class)->findIcalKeysAfterEnd($end, 'hamac');
            
            foreach ($absx as $elem) {
                $absences_db[] = $elem['ical_key'];
            }
        }

        // On lit le fichier CSV
        $inF = fopen($filename, 'r');

        if ($debug) {
            $this->log("On lit le fichier CSV " . $filename, 'AbsenceImportCSV');
        }

        while ($tab = fgetcsv($inF, 1024, ';')) {

            if ($tab[0] === null) {
                continue;
            }

            $uid = $tab[0];
            $absences_file[] = $uid;

            if ($debug) {
                $this->log("uid = " . $uid, 'AbsenceImportCSV');
            }

            if (!isset($tab[4]) and $debug) {
                    $this->log("\$tab[4] is not defined", 'AbsenceImportCSV');
                    continue;
            }

            // Si les logins du fichier Hamac ne sont pas dans le tableau $logins, on passe.
            // Le tableau $logins ne contient que les agents actifs qui acceptent la synchronisation Hamac
            if (!in_array($tab[4], $logins)) {
                if ($debug) {
                    $this->log("\$tab[4] = " . $tab[4] . " ne fait pas partie des logins des agents actifs qui acceptent la synchronisation Hamac, on passe à la ligne suivante dans le CSV", 'AbsenceImportCSV');
                }

                continue;
            }

            // Si l'absence a été supprimée, on la supprime de la base (status 9)
            // Important : Faire la suppression avant le contrôle des status car le status 9 sera ignoré à la prochaine étape
            if ($tab[6] == 9 and in_array($uid, $uids)) {
                if ($debug) {
                    $this->log("Status = 9, absence supprimée, on passe à la ligne suivante dans le CSV", 'AbsenceImportCSV');
                }

                $absd = $this->entityManager->getRepository(Absence::class)->find($absences[$uid]->getId());

                foreach ($absd as $elem) {
                    $this->entityManager->remove($elem);
                }

                continue;
            }

            // Si le status de l'absence Hamac n'est pas dans la liste des status à importer, on passe.
            if (!in_array($tab[6], $status)) {
                if ($debug) {
                    $this->log("\$status = " . $tab[6] . " n'est pas dans la liste des status à importer (" . $config['Hamac-status'] . "), on passe à la ligne suivante dans le CSV", 'AbsenceImportCSV');
                }

                continue;
            }

            // Préparation des données
            if ($debug) {
                $this->log("Préparation des données", 'AbsenceImportCSV');
            }

            $perso_id = $perso_ids[$tab[4]];
            $requestDate = new \DateTime();
            $start = \DateTime::createFromFormat('d/m/Y H:i:s', $tab[2]);
            $end = \DateTime::createFromFormat('d/m/Y H:i:s', $tab[3]);
            $comment = $tab[1];

            $log_info = "agent=" . $perso_id;
            $log_info .= " / request=" . $requestDate->format('Y-m-d H:i:s');
            $log_info .= " / start=" . $start->format('Y-m-d H:i:s');
            $log_info .= " / end=" . $end->format('Y-m-d H:i:s');
            $log_info .= " / comments=" . $comment;

            // Validations
            // Si le status de l'absence Hamac est 2, l'absence est validée
            if ( in_array($tab[6], $status_validated)) {
                if ($debug) {
                    $this->log("Si le status de l'absence Hamac est 2, l'absence est validée au niveau 2", 'AbsenceImportCSV');
                }
                $validLevel1 = 99999;
                $validLevel1Date = new \DateTime();
                $validLevel2 = 99999;
                $validLevel2Date = new \DateTime();
            } elseif ( in_array($tab[6], $status_waiting)) {
                if ($debug) {
                    $this->log("Si le status de l'absence Hamac est 1, l'absence est validée au niveau 1", 'AbsenceImportCSV');
                }
                $validLevel1 = 99999;
                $validLevel1Date = new \DateTime();
                $validLevel2 = 0;
                $validLevel2Date = null;
            } else {
                if ($debug) {
                    $this->log("L'absence n'est pas validée", 'AbsenceImportCSV');
                }
                $validLevel1 = 0;
                $validLevel1Date = null;
                $validLevel2 = 0;
                $validLevel2Date = null;
            }


            // Si l'absence n'est pas dans la base de données, on l'importe.
            if (!in_array($uid, $uids)) {
                if ($debug) {
                    $this->log("Si l'absence n'est pas dans la base de données, on l'importe", 'AbsenceImportCSV');
                }

                $absi = new Absence();
                $absi->setStart($start);
                $absi->setEnd($end);
                $absi->setRequestDate($requestDate);
                $absi->setValidLevel2Date($validLevel2Date);
                $absi->setValidLevel1Date($validLevel1Date);
                $absi->setUserId($perso_id);
                $absi->setValidLevel2($validLevel2);
                $absi->setValidLevel1($validLevel1);
                $absi->setReason($motif);
                $absi->setComment($comment);
                $absi->setCalName('hamac');
                $absi->setICalKey($uid);
                $absi->setUid($uid);
                $absi->setOtherReason('');
                $absi->setStatus('');
                $absi->setOriginId(0);
                $this->entityManager->persist($absi);

                if ($debug) {
                    $this->log("Absence importée, on passe à la ligne suivante dans le CSV", 'AbsenceImportCSV');
                }

                $this->log("Absence inserted : $uid / $log_info", 'AbsenceImportCSV');

                continue;
            }

            // Si l'absence existe, on vérifie si elle a changé.
            if ($debug) {
                $this->log("Si l'absence existe, on vérifie si elle a changé", 'AbsenceImportCSV');
            }
            $absu = $absences[$uid];

            if ($absu->getUserId() != $perso_id
                or $absu->getStart() != $start
                or $absu->getEnd() != $end
                or $absu->getComment() != $comment
                or $absu->getValidLevel1() != $validLevel1
                or $absu->getValidLevel2() != $validLevel2) {
                // Si l'absence a changé, on met à jour la base de données
                if ($debug) {
                    $this->log("Si l'absence a changé, on met à jour la base de données", 'AbsenceImportCSV');
                }

                $absu->setStart($start);
                $absu->setEnd($end);
                $absu->setRequestDate($requestDate);
                $absu->setValidLevel2Date($validLevel2Date);
                $absu->setValidLevel1Date($validLevel1Date);
                $absu->setUserId($perso_id);
                $absu->setValidLevel2($validLevel2);
                $absu->setValidLevel1($validLevel1);
                $absu->setReason('');
                $absu->setComment($comment);
                $absu->setCalName('hamac');
                $absu->setICalKey(null);
                $absu->setUid(null);
                $absu->setOtherReason('');
                $absu->setStatus('');
                $absu->setOriginId(0);

                if ($debug) {
                    $this->log("Absence changée dans la base de donnée, on passe à la ligne suivante dans le CSV", 'AbsenceImportCSV');
                }

                $this->log("Absence updated : $uid / {$absu->getId()} / $log_info", 'AbsenceImportCSV');

                continue;
            }
        }
        fclose($inF);

        // Remove entries deleted from source file
        foreach ($absences_db as $elem) {
            if (!in_array($elem, $absences_file)) {
                $absd = $this->entityManager->getRepository(Absence::class)->findBy(['cal_name' => 'hamac', 'ical_key' => $elem]);
                foreach ($absd as $a) {
                    $this->entityManager->remove($a);
                }

                $this->log("Absence deleted from source file : $elem", 'AbsenceImportCSV');
            }
        }

        $this->entityManager->flush();  
        // Unlock
        unlink($lockFile);

        $this->log("Hamac import completed", 'AbsenceImportCSV');

        if ($output->isVerbose()) {
            $io->success('Hamac import completed: absences inserted/updated and obsolete entries pruned.');
        }

        return Command::SUCCESS;
    }
}
