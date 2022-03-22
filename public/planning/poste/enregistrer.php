<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE

@file public/planning/poste/enregistrer.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'enregistrer un modèle de planning à partir de la page affichant le planning (planning/poste/index.php),
icône disquette.
Etape 1 : Affiche un formulaire permettant de saisir un nom, de choisir l'enregistement du jour ou de la semaine.
Etape 2 : Vérification de l'existance du nom : si le nom existe : confirmation avant remplacement
Etape 3 : enregistrement dans la base de données

Cette page est appelée par la fonction JavaScript Popup qui l'affiche dans un cadre flottant
*/

use App\Model\Model;
use App\Model\ModelAgent;

require_once "class.planning.php";

// Initialisation des variables
$confirm=filter_input(INPUT_GET, "confirm", FILTER_SANITIZE_STRING);
$CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);
$model = filter_input(INPUT_GET, "model", FILTER_SANITIZE_NUMBER_INT);
$nom=trim(filter_input(INPUT_GET, "nom", FILTER_SANITIZE_STRING));
$semaine=filter_input(INPUT_GET, "semaine", FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET, "site", FILTER_SANITIZE_NUMBER_INT);

// Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
$confirm=filter_var($confirm, FILTER_CALLBACK, array("options"=>"sanitize_on"));
$date=filter_var($date, FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$semaine=filter_var($semaine, FILTER_CALLBACK, array("options"=>"sanitize_on"));
$dateFr=dateFr($date);

// Sécurité
// Refuser l'accès aux agents n'ayant pas les droits de modifier le planning
if (!in_array((300+$site), $droits)) {
    echo "<div id='acces_refuse'>Accès refusé</div>";
    echo "<a href='javascript:popup_closed();'>Fermer</a>\n";
    exit;
}

echo "<div style='text-align:center'>\n";
echo "<b>Enregistrement du planning du $dateFr comme modèle.</b>\n";
echo "<br/><br/>\n";

// Etape 1 : Choix du nom du modèle
if (!$nom) {
    echo <<<EOD
  <form method='get' name='form' action='index.php'>
  
  <input type='hidden' name='page' value='planning/poste/enregistrer.php' />
  <input type='hidden' name='CSRFToken' value='$CSRFToken' />
  <input type='hidden' name='date' value='$date' />
  <input type='hidden' name='site' value='$site' />
  <input type='hidden' name='menu' value='off' />
  Nom du modèle&nbsp;&nbsp;
  <input type='text' name='nom' value='$date' />
  <br/><br/>
  Enregistrer toute la semaine&nbsp;&nbsp;
  <input type='checkbox' name='semaine' />
  <br/><br/>
  <input type='button' value='Annuler' onclick='popup_closed()' />
  &nbsp;&nbsp;
  <input type='submit' value='Enregistrer' />
EOD;
}
// Etape 2 : Vérifions si le nom n'est pas déjà utilisé
elseif (!$confirm) {
    $db=new db();
    $db->query("SELECT * FROM `{$config['dbprefix']}pl_poste_modeles_tab` WHERE `nom`= '$nom' AND `site`= '$site';");
    if ($db->result) {				// Si le nom existe, on propose de le remplacer
        $model = $db->result[0]['model_id'];
        echo "<b>Le modèle \"$nom\" existe<b><br/><br/>\n";
        echo "Voulez vous le remplacer ?<br/><br/>\n";
        echo "<a href='javascript:popup_closed();'>Non</a>&nbsp;&nbsp;\n";
        echo "<a href='index.php?page=planning/poste/enregistrer.php&amp;confirm=oui&amp;menu=off&amp;model=$model&amp;nom=$nom&amp;semaine=$semaine&amp;date=$date&amp;site=$site&amp;CSRFToken=$CSRFToken'>Oui</a>\n";
    }
    // Etape 2b : si le nom n'existe pas, on enregistre le planning du jour
    else {
        enregistre_modele($nom, $date, $semaine, $site, $CSRFToken);
    }
}
// Etape 3 : Si le nom existe et confirmation (=remplacement) : suppression des enregistements ecriture des nouveaux
else {
    $select=new db();
    $select->select2("pl_poste", "*", array("date"=>$date, "site"=>$site));
    if ($select->result) {
        $delete=new db();
        $delete->CSRFToken = $CSRFToken;
        $delete->delete("pl_poste_modeles", array("model_id" => $model));
        $delete=new db();
        $delete->CSRFToken = $CSRFToken;
        $delete->delete("pl_poste_modeles_tab", array("model_id" => $model));
        enregistre_modele($nom, $date, $semaine, $site, $CSRFToken);
    }
}

function enregistre_modele($nom, $date, $semaine, $site, $CSRFToken)
{
    $dbprefix=$GLOBALS['config']['dbprefix'];
    $d=new datePl($date);

    // Sélection des données entre le lundi et le dimanche de la semaine courante
    if ($semaine) {
        // Sélection des tableaux (structures)
        $tab_db=new db();
        $tab_db->select2("pl_poste_tab_affect", "*", array("date"=>"BETWEEN{$d->dates[0]}AND{$d->dates[6]}", "site"=>$site));

        // Sélection des agents placés dans les cellules
        $select=new db();
        $select->select2("pl_poste", "*", array("date"=>"BETWEEN{$d->dates[0]}AND{$d->dates[6]}", "site"=>$site));
    }
    // Sélection des données du jour courant
    else {
        // Sélection du tableau (structure)
        $tab_db=new db();
        $tab_db->select2("pl_poste_tab_affect", "*", array("date"=>$date, "site"=>$site));
        // Sélection des agents placés dans les cellules
        $select=new db();
        $select->select2("pl_poste", "*", array("date"=>$date, "site"=>$site));
    }
  
    if ($select->result and $tab_db->result) {

        // Model_id
        $db = new db();
        $db->query('select MAX(`model_id`) AS `model` FROM `pl_poste_modeles_tab`;');
        $model = $db->result ? $db->result[0]['model'] + 1 : 1;

        $values=array();
        foreach ($select->result as $elem) {
            $jour="";			// $jour reste nul si on n'importe pas une semaine
            if ($semaine) {
                $d=new datePl($elem['date']);
                $jour=$d->position;		// position du jour de la semaine (1=lundi , 2=mardi ...)
                if ($jour == 0) {
                    $jour = 7;
                }
            }
            $values[] = array(
                ':model_id' => $model,
                ':perso_id' => $elem['perso_id'],
                ':poste' => $elem['poste'],
                ':debut' => $elem['debut'],
                ':fin' => $elem['fin'],
                ':jour' => $jour,
                ':site' => $site,
            );
        }

        $dbh=new dbh();
        $dbh->CSRFToken = $CSRFToken;
        $dbh->prepare("INSERT INTO `{$dbprefix}pl_poste_modeles` (`model_id`, `perso_id`, `poste`, `debut`, `fin`, `jour`, `site`) VALUES (:model_id, :perso_id, :poste, :debut, :fin, :jour, :site);");
        foreach ($values as $value) {
            $dbh->execute($value);
        }

        foreach ($tab_db->result as $elem) {
            $jour=9;				// Si un seul jour, on met 9 pour ne pas fixer le jour de la semaine
            if ($semaine) {
                $d=new datePl($elem['date']);
                $jour=$d->position;		// position du jour de la semaine (1=lundi , 2=mardi ...)
                if ($jour == 0) {
                    $jour = 7;
                }
            }
            $insert=array("model_id" => $model, "nom"=>$nom, "jour"=>$jour, "tableau"=>$elem['tableau'], "site"=>$site);
            $db=new db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("pl_poste_modeles_tab", $insert);
        }
    }
    echo "Modèle \"$nom\" enregistré<br/><br/>\n";
    echo "<a href='javascript:popup_closed();'>Fermer</a>\n";
}

?>
</form>
</div>