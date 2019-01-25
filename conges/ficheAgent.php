<?php
/**
Planning Biblio, Plugin Congés Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ficheAgent.php
Création : 26 juillet 2013
Dernière modification : 16 février 2018
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié

Description :
Fichier permettant d'ajouter les informations sur les congés dans la fiche des agents
Inclus dans le fichier personnel/modif.php
*/

require_once "class.conges.php";

// Recherche des informations sur les congés
$c=new conges();
$c->perso_id=$id;
$c->fetchCredit();
$conges=$c->elements;

// Affichage

echo "<div id='conges' style='margin-left:80px;padding-top:30px;'>\n";
// Nombre d'heures de congés par an
echo "<table class='tableauFiches'><tr><td>";
echo "Nombre d'heures de congés par an :";
echo "</td><td style='text-align:right;'>";
if ($admin) {
    echo "<input type='text' name='conges_annuel' value='{$conges['annuelHeures']}'  style='width:70px;text-align:right;'>\n";
    echo "<label style='text-align:center;padding:5px;'>h</label>";
    echo "<select name='conges_annuel_min' style='width:50px;'>\n";

    for ($min=0;$min<1;$min=$min+(5/60)) {
        $minutes=sprintf("%02s", $min*60);
        $selected=$minutes==$conges['annuelCents']?"selected='selected'":null;
        echo "<option value='". $min ."' $selected>$minutes</option>\n";
    }
    echo "</select>\n";
} else {
    echo heure4($conges['annuel']);
}
echo "</td></tr>";

// Crédit d'heures de congés
echo "<tr><td>";
echo "Crédit d'heures de congés actuel :";
echo "</td><td style='text-align:right;'>";
if ($admin) {
    echo "<input type='text' name='conges_credit' value='{$conges['creditHeures']}'  style='width:70px;text-align:right;'>\n";
    echo "<label style='text-align:center;padding:5px;'>h</label>";
    echo "<select name='conges_credit_min' style='width:50px;'>\n";

    for ($min=0;$min<1;$min=$min+(5/60)) {
        $minutes=sprintf("%02s", $min*60);
        $selected=$minutes==$conges['creditCents']?"selected='selected'":null;
        echo "<option value='".$min . "' $selected>$minutes</option>\n";
    }
    echo "</select>\n";
} else {
    echo heure4($conges['credit']);
}
echo "</td></tr>";

// Reliquat
echo "<tr><td>";
echo "Reliquat de congés :";
echo "</td><td style='text-align:right;'>";
if ($admin) {
    echo "<input type='text' name='conges_reliquat' value='{$conges['reliquatHeures']}'  style='width:70px;text-align:right;'>\n";
    echo "<label style='text-align:center;padding:5px;'>h</label>";
    echo "<select name='conges_reliquat_min' style='width:50px;'>\n";

    for ($min=0;$min<1;$min=$min+(5/60)) {
        $minutes=sprintf("%02s", $min*60);
        $selected=$minutes==$conges['reliquatCents']?"selected='selected'":null;
        echo "<option value='".$min ."' $selected>$minutes</option>\n";
    }
    echo "</select>\n";
} else {
    echo heure4($conges['reliquat']);
}
echo "</td></tr>";

// Anticipation
echo "<tr><td>";
echo "Solde débiteur :";
echo "</td><td style='text-align:right;'>";
if ($admin) {
    echo "<input type='text' name='conges_anticipation' value='{$conges['anticipationHeures']}'  style='width:70px;text-align:right;'>\n";
    echo "<label style='text-align:center;padding:5px;'>h</label>";
    echo "<select name='conges_anticipation_min' style='width:50px;'>\n";

    for ($min=0;$min<1;$min=$min+(5/60)) {
        $minutes=sprintf("%02s", $min*60);
        $selected=$minutes==$conges['anticipationCents']?"selected='selected'":null;
        echo "<option value='".$min ."' $selected>$minutes</option>\n";
    }
    echo "</select>\n";
} else {
    echo heure4($conges['anticipation']);
}
echo "</td></tr>";

// Récupération
echo "<tr><td>";
echo "{$lang['comp_time']} :";
echo "</td><td style='text-align:right;'>";
if ($admin) {
    echo "<input type='text' name='recup' value='{$conges['recupHeures']}'  style='width:70px;text-align:right;'>\n";
    echo "<label style='text-align:center;padding:5px;'>h</label>";
    echo "<select name='recup_min' style='width:50px;'>\n";

    for ($min=0;$min<1;$min=$min+(5/60)) {
        $minutes=sprintf("%02s", $min*60);
        $selected=$minutes==$conges['recupCents']?"selected='selected'":null;
        echo "<option value='".$min ."' $selected>$minutes</option>\n";
    }
    echo "</select>\n";
} else {
    echo heure4($conges['recup_samedi']);
}
echo "</td></tr>";
?>
</table>
</div>
<!--	FIN Droits d'accès		-->