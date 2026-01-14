<?php

namespace App\Planno\Helper;

use App\Planno\Helper\BaseHelper;
use App\Entity\Absence;
use App\Entity\Agent;

class AbsenceImportCSVHelper extends BaseHelper
{

    use \App\Traits\LoggerTrait;
    private string $program = "AbsenceImportCSV";
    private array $importResults = array();
    private const ERROR   = 1;
    private const WARNING = 2;
    private const INFO    = 3;
    private const DEBUG   = 4;

    public function __construct()
    {
        parent::__construct();
    }

    private function checkRegexes($config_option): string {
        $regexes = $this->config($config_option);
        if (trim($regexes) == '') {
            return "$config_option est vide";
        }
        $regexes_array = explode("\n", $regexes);
        foreach ($regexes_array as $regex) {
            if (@preg_match($regex, '') === FALSE) {
                return "$regex n'est pas une expression régulière valide dans l'option de configuration $config_option";
            }
        }
        return '';
    }

    public function import($file, $loggedin_id): array
    {
        $agent_match = $this->config('AbsImport-Agent');
        $filename    = $file->getClientOriginalName();
        $inF         = fopen($file->getPathname(), 'r');
        $dbprefix    = $GLOBALS['dbprefix'];
        $CSRFToken   = $GLOBALS['CSRFSession'];

        # Empty previous results
        $this->importResults = array();

        # Should this be hardcoded?
        $structure   = array('debut' => 5, 'fin' => 6);
        $date_format = "d/m/Y";

        $msg = "Début d'import des absences du fichier CSV '$filename': les agents sont cherchés via leur $agent_match";
        $this->importLog(0, $msg, self::INFO);

        $dbi = new \dbh();
        $dbi->CSRFToken = $CSRFToken;

        $dbi->prepare("INSERT INTO `{$dbprefix}absences` (`perso_id`, `debut`, `fin`, `motif`, `commentaires`, `demande`, `valide`, `validation`, `valide_n1`, `validation_n1`, `cal_name`, `ical_key`)
    VALUES (:perso_id, :debut, :fin, :motif, :commentaires, :demande, :valide, :validation, :valide_n1, :validation_n1, :cal_name, :ical_key);");

        $line = 0;
        $imported = 0;

        # We could use League\Csv\Reader instead
        while ($tab = fgetcsv($inF, 1024, ';')) {
            $line++;
            if ($line == 1) {
                $msg = "La ligne d'en-tête est ignorée";
                $this->importLog($line,$msg, self::INFO);
                continue;
            }
            $id = $tab[0];

            if (!$id) {
                $msg = "Impossible de récupérer l'identifiant de l'usager";
                $this->importLog($line,$msg, self::ERROR);
                continue;
            }

            // Regex start
            $regex_number = 0;
            $sql_debut = '';

            $regex_error = $this->checkRegexes('AbsImport-ConvertBegin');
            if ($regex_error == '') {
                $start_regexes = explode("\n", $this->config('AbsImport-ConvertBegin'));
                foreach ($start_regexes as $regex) {
                    $regex_number++;
                    $value = $tab[$structure['debut']];
                    if (preg_match($regex, $value, $capture)) {
                        $csv_date = $capture[1];
                        $date = \DateTime::createFromFormat($date_format, $csv_date);
                        $sql_date = date_format($date, 'Y-m-d');
                        $hour = array_key_exists(2, $capture) && $capture[2] == 'après-midi' ? '13:00:00' : '09:00:00';
                        $sql_debut = $sql_date . " " . $hour;
                        $msg = "La date de début $value a été convertie en $sql_debut en utilisant l'expression régulière $regex_number";
                        $this->importLog($line,$msg, self::DEBUG);
                        break;
                    }
                }
            } else {
                $msg = $regex_error;
                $this->importLog($line,$msg, self::ERROR);
                continue;
            }

            if ($sql_debut === '' || $sql_debut === '0') {
                $msg = "Impossible de définir une date de début à partir de la valeur $value";
                $this->importLog($line,$msg, self::ERROR);
                continue;
            }

            // Regex end
            $regex_number = 0;
            $sql_fin = '';
            $regex_error = $this->checkRegexes('AbsImport-ConvertEnd');
            if ($regex_error == '') {
                $end_regexes   = explode("\n", $this->config('AbsImport-ConvertEnd'));
                foreach ($end_regexes as $regex) {
                    $regex_number++;
                    $value = $tab[$structure['fin']];
                    if (preg_match($regex, $value, $capture)) {
                        $csv_date = $capture[1];
                        $date = \DateTime::createFromFormat($date_format, $csv_date);
                        $sql_date = date_format($date, 'Y-m-d');
                        $hour = array_key_exists(2, $capture) && $capture[2] == 'matin' ? '13:00:00' : '20:00:00';
                        $sql_fin = $sql_date . " " . $hour;
                        $msg = "La date de fin $value a été convertie en $sql_fin en utilisant l'expression régulière $regex_number";
                        $this->importLog($line,$msg, self::DEBUG);
                        break;
                    }
                }
            } else {
                $msg = $regex_error;
                $this->importLog($line,$msg, self::ERROR);
                continue;
            }

            if ($sql_fin === '' || $sql_fin === '0') {
                $msg = "Impossible de définir une date de fin à partir de la valeur $value";
                $this->importLog($line,$msg, self::ERROR);
                continue;
            }

            // Find agent id based on first column
            $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(array($agent_match => $id));
            if (!$agent) {
                $msg = "Impossible de trouver un agent qui a $id pour $agent_match";
                $this->importLog($line,$msg, self::ERROR);
                continue;
            }

            $perso_id  = $agent->getId();
            $firstname = $agent->getFirstname();
            $lastname  = $agent->getLastName();
            $msg = "Agent avec $id pour $agent_match trouvé: agent $perso_id ($firstname $lastname)";
            $this->importLog($line,$msg, self::DEBUG);


            // Check if absence already exists
            $absence_already_exists = $this->entityManager->getRepository(Absence::class)->findOneBy(
                [
                    'perso_id' => $perso_id,
                    'debut'    => new \DateTime($sql_debut),
                    'fin'      => new \DateTime($sql_fin),
                    'motif'    => $this->config('AbsImport-Reason'),
                ]
            );
            if ($absence_already_exists) {
                $msg = "L'absence pour $firstname $lastname existe déjà (absence " . $absence_already_exists->getId() . ")";
                $this->importLog($line,$msg, self::WARNING);
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $insert = array(
                ':perso_id'      => $perso_id,
                ':debut'         => $sql_debut,
                ':fin'           => $sql_fin,
                ':motif'         => $this->config('AbsImport-Reason'),
                ':commentaires'  => $this->program,
                ':demande'       => $now,
                ':valide'        => $loggedin_id,
                ':validation'    => $now,
                ':valide_n1'     => $loggedin_id,
                ':validation_n1' => $now,
                ':cal_name'      => '',
                ':ical_key'      => '',
            );

            $result = $dbi->execute($insert);
            if ($dbi->error) {
                $msg = "L'absence n'a pas pu être ajoutée: " . $dbi->error;
                $this->importLog($line,$msg, self::ERROR);
                continue;
            } else {
                $msg = "Absence ajoutée pour l'agent $perso_id ($firstname, $lastname)";
                $this->importLog($line,$msg, self::INFO);
            }
            $imported++;
        }
        $msg = "Fin d'import des absences du fichier CSV '$filename': $line lines traitées, $imported absences importées.";
        $this->importLog($line+1 ,$msg, self::INFO);


        return $this->importResults;
    }

    private function importLog($line, $message, $type): void
    {
        $this->log("$line, $type: $message", $this->program);
        $logline = array('line' => $line, 'message' => $message, 'type' => $type);
        $this->importResults[] = $logline;
    }
}
