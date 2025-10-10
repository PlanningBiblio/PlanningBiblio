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

require_once(__DIR__ . '/../../vendor/autoload.php');

use App\PlanningBiblio\Framework;

require_once __DIR__ . '/../../public/include/function.php';
require_once(__DIR__ . '/../../legacy/Class/class.absences.php');
require_once(__DIR__ . '/../../legacy/Class/class.postes.php');

#[AsCommand(
    name: 'app:planning:control',
    description: 'Scans upcoming site schedules, flags unvalidated or unfilled shifts, and emails a summary report',
)]
class PlanningControlCommand extends Command
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
        $CSRFToken = CSRFToken();

        if (empty($remindersEnabled)) {
            $message = 'Rappels désactivés';
            logs($message, 'Rappels', $CSRFToken);
            $io->warning($message);
            return Command::SUCCESS;
        }

        // Gestion des sites
        $sites=array();
        $multiSiteCount = $config['Multisites-nombre'];
        for ($i=1;$i<=$multiSiteCount;$i++) {
            $multiSiteName = $config['Multisites-site'.$i];
            $sites[]=array($i,$multiSiteName);
        }

        // Recherche la date du jour et les $reminderDays suivants
        $dates=array();
        for ($i=0;$i<=$reminderDays;$i++) {
            $time=strtotime("+ $i days");
            $jour_semaine=date("w", $time);

            // Si le jour courant est un dimanche et que la bibliothèque n'ouvre pas les dimanches, on ne l'ajoute pas
            if ($jour_semaine!=0 or !empty($sunday)) {
                $dates[]=date("Y-m-d", $time);
            }

            // Si le jour courant est un samedi, nous recherchons 2 jours supplémentaires pour avoir le bon nombre de jours ouvrés.
            // Nous controlons également le samedi et le dimanche
            if ($jour_semaine==6) {
                $reminderDays=$reminderDays+2;
            }
        }

        // Listes des postes
        $p=new \postes();
        $p->fetch();
        $postes=$p->elements;

        // Création du message qui sera envoyé par e-mail
        $data=array();

        // Prépare la requête permettant de vérifier si les postes sont occupés
        // On utilide PDO pour de meilleurs performances car la même requête sera executée de nombreuses fois avec des valeurs différentes
        $dbh=new \dbh();
        $dbh->CSRFToken = $CSRFToken;
        $dbh->prepare("SELECT `id`,`perso_id`,`absent` FROM `{$config['dbprefix']}pl_poste`
        WHERE `date`=:date AND `site`=:site AND `poste`=:poste AND `debut`=:debut AND `fin`=:fin AND `absent`='0' AND `supprime`='0';");


        // Pour chaque date et pour chaque site
        foreach ($dates as $date) {
            foreach ($sites as $site) {

                // on créé un tableau pour stocker les éléments par dates et sites
                $data[$date][$site[0]]=array("date"=>dateFr($date), "site"=>$site[1]);

                // On recherche les plannings qui ne sont pas créés (aucune structure affectée)
                $db=new db();
                $db->select2("pl_poste_tab_affect", null, array("date"=>$date, "site"=>$site[0]));
                if (!$db->result) {
                    $data[$date][$site[0]]["message"]="Le planning {$site[1]} du <strong>".dateFr($date)." <span style='color:red;'>n'est pas cr&eacute;&eacute;</span></strong>\n";
                    continue;
                } else {
                    // Si le planning est créé, on récupère le numéro du tableau pour ensuite
                    // comparer la structure au planning complété afin de trouver les cellules vides
                    $tableauId=$db->result[0]['tableau'];

                    // On recherche les plannings qui ne sont pas validés
                    $db=new db();
                    $db->select2("pl_poste_verrou", null, array("date"=>$date, "site"=>$site[0], "verrou2"=>1));
                    if ($db->result) {
                        $data[$date][$site[0]]["message"]="Le planning {$site[1]} du <strong>".dateFr($date)."</strong> est valid&eacute;\n";
                    } else {
                        $data[$date][$site[0]]["message"]="Le planning {$site[1]} du <strong>".dateFr($date)." <span style='color:red;'>n'est pas valid&eacute;</span></strong>\n";
                    }
                }

                // On recherche les plannings qui ne sont pas complets (cellules vides)
                // Recherche des tableaux (structures)
                $t = new \Framework();
                $t->id=$tableauId;
                $t->get();
                $tableau=$t->elements;

                foreach ($tableau as $elem) {

                    // On stock dans notre tableau data les éléments date, site, tableau
                    $data[$date][$site[0]]['tableau'][$elem['nom']]["tableau"]=$elem['titre'];

                    // $tab = liste des postes/plages horaires non occupés, cellules grisées excluses, poste non obligatoires exclus selon config
                    $tab=array();
                    $i=-1;

                    // Pour chaque ligne du tableau (structure)
                    foreach ($elem['lignes'] as $l) {
                        // Ne regarde que les lignes "postes"
                        if ($l['type']=="poste") {
                            // Pour chaque créneau horaire du tableau (structure)
                            foreach ($elem['horaires'] as $key => $h) {
                                // Si cellule grisées, on l'exclus (donc continue)
                                if (in_array($l['ligne']."_".($key+1), $elem['cellules_grises'])) {
                                    continue;
                                }
                                // Si on ne veut pas des postes de renfort et si le poste n'est pas obligatoire, on l'exclus
                                if (empty($reinforcementReminders) and $postes[$l['poste']]['obligatoire']!="Obligatoire") {
                                    continue;
                                }

                                // On contrôle si le poste est occupé
                                // Pour ceci, on execute la requête préparée plus haut avec PDO
                                $sql=array(":date"=>$date, ":site"=>$site[0], ":poste"=>$l['poste'], ":debut"=>$h['debut'], ":fin"=>$h['fin']);
                                $dbh->result=array();
                                $dbh->execute($sql);
                                $result=$dbh->result;

                                // Contrôle des absences et des congés
                                // Si la dernière execution de la requête donne un résultat
                                // Vérifier qu'au moins un des agents issus de ce résultat n'est pas absent
                                $tousAbsents=true;
                                if (!empty($result)) {
                                    foreach ($result as $res) {
                                        // Contrôle des absences
                                        $absent=false;
                                        $a=new absences();
                                        if ($a->check($res['perso_id'], $date." ".$h['debut'], $date." ".$h['fin'])) {
                                            $absent=true;
                                        }

                                        // Contrôle des congés
                                        $conges=false;
                                        if (empty($holidayEnabled)) {
                                            require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
                                            $c=new \conges();
                                            if ($c->check($res['perso_id'], $date." ".$h['debut'], $date." ".$h['fin'])) {
                                                $conges=true;
                                            }
                                        }

                                        // Si l'agent n'est ni absent, ni en congés : on a une présence
                                        if (!$absent and !$conges) {
                                            $tousAbsents=false;
                                            break;
                                        }
                                    }
                                }

                                // Si la dernière execution de la requête ne donne pas de résultat ou que tous les agents issus du résultat sont absents
                                if (empty($result) or $tousAbsents) {
                                    // On enregistre dans le table les informations de la cellule

                                    // On regroupe les horaires qui se suivent sur un même poste
                                    if (!empty($tab) and $tab[$i]['fin']==$h['debut'] and $tab[$i]['poste_id']==$l['poste']) {
                                        $tab[$i]["fin"]=$h['fin'];
                                    } else {
                                        $i++;
                                        $tab[$i]=array("poste"=>$postes[$l['poste']]['nom'], "poste_id"=>$l['poste'], "debut"=>$h['debut'], "fin"=>$h['fin']);
                                    }
                                }
                            }
                        }
                    }
                    $data[$date][$site[0]]['tableau'][$elem['nom']]["data"]=$tab;
                }
            }
        }

        // Création du message
        $msg="Voici l&apos;&eacute;tat des plannings du ".dateFr($dates[0])." au ".dateFr($dates[count($dates)-1]);
        $msg.="<ul>\n";
        foreach ($data as $date) {
            foreach ($date as $site) {
                $msg.="<li style='margin-bottom:15px;'>\n";
                if (array_key_exists("message", $site)) {
                    $msg.=$site['message'];
                }
                if (array_key_exists("tableau", $site)) {
                    $msg.="<br/>\nLes postes suivants ne sont pas occup&eacute;s :\n<ul>\n";
                    foreach ($site['tableau'] as $tableau) {
                        $msg.="<li>Tableau <strong>{$tableau['tableau']}</strong> :\n<ul>\n";
                        foreach ($tableau['data'] as $poste) {
                            $msg.="<li>{$poste['poste']}, de ".heure2($poste['debut'])." &agrave; ".heure2($poste['fin'])."</li>\n";
                        }
                        $msg.="</ul>\n";
                    }
                    $msg.="</ul>\n";
                }
                $msg.="</li>\n";
            }
        }
        $msg.="</ul>\n";

        $subject="Plannings du ".dateFr($dates[0])." au ".dateFr($dates[count($dates)-1]);
        $to=explode(";", $planningMail);

        $m=new CJMail();
        $m->to=$to;
        $m->subject=$subject;
        $m->message=$msg;
        $m->send();
        if ($m->error) {
            logs($m->error, "Rappels", $CSRFToken);
        }

        if ($output->isVerbose()){
            $io->success('Planning check completed successfully; notification email sent.');
        }

        return Command::SUCCESS;
    }
}
