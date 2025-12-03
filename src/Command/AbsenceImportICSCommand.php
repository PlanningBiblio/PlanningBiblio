<?php

namespace App\Command;

use App\Entity\Agent;
use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__ . '/../../legacy/Common/function.php';
require_once __DIR__ . '/../../legacy/Class/class.ics.php';
require_once __DIR__ . '/../../legacy/Class/class.personnel.php';

#[AsCommand(
    name: 'app:absence:import-ics',
    description: 'Import absences from ICS calendars',
)]
class AbsenceImportICSCommand extends Command
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

        $CSRFToken = CSRFToken();

        $this->log('Début d\'importation des fichiers ICS', 'AbsenceImportICS');

        if (empty(trim($config['ICS-Server1'])) &&
            empty(trim($config['ICS-Server2'])) &&
            !$config['ICS-Server3']) {
            $io->warning("Aucune source ICS n’est configurée. Aucun fichier ne sera importé.");

            return Command::SUCCESS;
        }

        // Créé un fichier .lock dans le dossier temporaire qui sera supprimé à la fin de l'execution du script, pour éviter que le script ne soit lancé s'il est déjà en cours d'execution
        $tmp_dir=sys_get_temp_dir();
        $lockFile = $tmp_dir . '/plannoAbsenceImportICS.lock';

        if (file_exists($lockFile)) {
            $fileTime = filemtime($lockFile);
            $time = time();
            // Si le fichier existe et date de plus de 10 minutes, on le supprime et on continue.
            if ($time - $fileTime > 600) {
                unlink($lockFile);
                // Si le fichier existe et date de moins de 10 minutes, on quitte
            } else {
                $message = 'Le fichier existe et date de moins de 10 minutes';
                $this->log($message, 'AbsenceImportICS');
                $io->warning($message);
                return Command::SUCCESS;
            }
        }
        // On créé le fichier .lock
        $inF=fopen($lockFile, "w");
        fclose($inF);

        // Recherche les serveurs ICS et les variables openURL
        // Index des tableaux $servers et $var :
        // 1 : Fichiers ICS provenant d'une source externe, renseignés dans la config. : ICS / ICS-Servers1
        // 2 : Fichiers ICS provenant d'une source externe, renseignés dans la config. : ICS / ICS-Servers2
        // 3 : Fichiers ICS provenant d'une source externe, renseignés dans la fiche des agents (url_ics)

        $servers=array(1=>null, 2=>null);
        $var=array(1=>null, 2=>null);

        for ($i=1; $i<3; $i++) {
            if (trim($config["ICS-Server$i"])) {
                $servers[$i]=trim($config["ICS-Server$i"]);
                if ($servers[$i]) {
                    $pos1=strpos($servers[$i], "[");

                    if ($pos1) {
                        $var[$i] = substr($servers[$i], $pos1 +1);

                        $pos2=strpos($var[$i], "]");

                        if ($pos2) {
                            $var[$i] = substr($var[$i], 0, $pos2);
                        }
                    }
                }
            }
        }

        // On recherche tout le personnel actif
        $agents = $this->entityManager->getRepository(Agent::class)->getByDeletionStatus([0]);

        // Pour chaque agent, on créé les URL des fichiers ICS et on importe les événements
        foreach ($agents as $agent) {

            // Pour les URL N°1, N°2 et url de la fiche agent (N°3)
            // Si le paramètre ICS-Server3 est activé, on recherche également une URL personnalisée dans la fiche des agents (champ url_ics).

            $fin = $config['ICS-Server3'] ? 3 : 2;
            for ($i=1; $i <= $fin; $i++) {
                if ($i<3) {
                    if (!$servers[$i] or !$var[$i]) {
                        continue;
                    }

                    $url=false;

                    // Selon le paramètre openURL (mail ou login)
                    switch ($var[$i]) {
                        case 'login':
                            if (!empty($agent->getLogin())) {
                                $url = str_replace("[{$var[$i]}]", $agent->getLogin(), $servers[$i]);
                            }
                            break;
                        case 'email':
                        case 'mail':
                            if (!empty($agent->getMail())) {
                                $url = str_replace("[{$var[$i]}]", $agent->getMail(), $servers[$i]);
                            }
                            break;
                        case 'matricule':
                            if (!empty($agent->getEmployeeNumber())) {
                                $url = str_replace("[{$var[$i]}]", $agent->getEmployeeNumber(), $servers[$i]);
                            }
                            break;
                        case 'perso_id':
                            if (!empty($agent->getId())) {
                                $url = str_replace("[{$var[$i]}]", $agent->getId(), $servers[$i]);
                            }
                            break;
                        default:
                            $url = false;
                            break;
                    }
                }

                if ($i == 3) {
                    $url = $agent->getIcsUrl();
                }

                // If the current calendar checkbox is not checked, we do not import it and we purge the relative events already imported.
                if (!$agent->getIcsCheck()[$i-1]) {
                    $this->log("Agent #{$agent->getId()} : Check ICS $i is disabled", 'AbsenceImportICS');
                    if (!$url) {
                        $this->log("Agent #{$agent->getId()} : Impossible de constituer une URL valide. Purge abandonnée", 'AbsenceImportICS');
                        continue;
                    }

                    $ics=new \CJICS();
                    $ics->src = $url;
                    $ics->number = $i;
                    $ics->perso_id = $agent->getId();
                    $ics->table = "absences";
                    $ics->logs = true;
                    $ics->CSRFToken = $CSRFToken;
                    $ics->purge();

                    continue;
                }

                if (!$url) {
                    $this->log("Agent #{$agent->getId()} : Impossible de constituer une URL valide", 'AbsenceImportICS');
                    continue;
                }

                // Test si le fichier existe
                if (substr($url, 0, 1) == '/' and !file_exists($url)) {
                    $this->log("Agent #{$agent->getId()} : Le fichier $url n'existe pas", 'AbsenceImportICS');
                    continue;
                }

                // Test si l'URL existe
                if (substr($url, 0, 4) == 'http') {
                    $test = @get_headers($url, 1);

                    if (empty($test)) {
                        $this->log("Agent #{$agent->getId()} : $url is not a valid URL", 'AbsenceImportICS');
                        continue;
                    }

                    if (!strstr($test[0], '200')) {
                        $this->log("Agent #{$agent->getId()} : $url {$test[0]}", 'AbsenceImportICS');
                        continue;
                    }
                }

                $this->log("Agent #{$agent->getId()} : Importation du fichier $url", 'AbsenceImportICS');

                if (!$url) {
                    continue;
                }

                $ics=new \CJICS();
                $ics->src=$url;
                $ics->perso_id=$agent->getId();
                $ics->pattern = $config["ICS-Pattern$i"];
                $ics->status = $config["ICS-Status$i"];
                $ics->desc = $config["ICS-Description$i"];
                $ics->number = $i;
                $ics->table="absences";
                $ics->logs=true;
                $ics->CSRFToken = $CSRFToken;
                $ics->updateTable();
            }
            sleep($config['ICS-Delay']);
        }

        // Unlock
        unlink($lockFile);

        if ($output->isVerbose()) {
            $io->success('ICS import completed: absences updated and entries from disabled calendars purged.');
        }

        return Command::SUCCESS;
    }
}
