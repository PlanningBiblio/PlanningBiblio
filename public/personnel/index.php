<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

Fichier : public/personnel/index.php
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Chritophe Le Guennec <christophe.leguennec@u-pem.fr>

Description :
Affiche le tableau des agents avec les filtres "service public - administratif - supprimé" et le filtre "Rechercher" du tableau
Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

// Initialisation des variables
$actif=filter_input(INPUT_GET, "actif", FILTER_SANITIZE_STRING);

if (!$actif) {
    $actif = isset($_SESSION['perso_actif']) ? $_SESSION['perso_actif'] : 'Actif';
}

$_SESSION['perso_actif']=$actif;

?>

<form name='form2' action='index.php' method='get'>
<input type='hidden' name='page' value='personnel/index.php' />
<table style='margin-bottom:10px;'><tr style='vertical-align:center;'>
<td style='width:270px;'>
<h3 style='margin:0;'>Liste des agents</h3>
</td><td>
Voir les agents
</td><td style='width:150px;'>
<select name='actif' onchange='document.form2.submit();'  class='ui-widget-content ui-corner-all'>
<option value='Actif'>Service public</option>
<option value='Inactif'>Administratif</option>
<?php
if (in_array(21, $droits)) {
    echo "<option value='Supprim&eacute;'>Supprim&eacute;</option>\n";
}
?>
</select>
</td><td style='width:80px;'>
</td><td>
<?php
if (in_array(21, $droits)) {
    echo "<input type='button' value='Ajouter' onclick='location.href=\"{$config['URL']}/agent/add\";' class='ui-button'/>\n";
    if ($config['LDAP-Host'] and $config['LDAP-Suffix']) {
        echo "<input type='button' value='Import LDAP' onclick='location.href=\"index.php?page=personnel/import.php\";' class='ui-button' style='margin-left:20px;'/>\n";
    }
}
?>
</td></tr></table>
</form>


<?php
//		Suppression des agents dont la date de départ est passée		//
$tab=array(0);
$db=new db();
$db->CSRFToken = $CSRFSession;
$db->update('personnel', array('supprime'=>'1', 'actif'=>'Supprim&eacute;'), "`depart`<CURDATE() AND `depart`<>'0000-00-00' and `actif` NOT LIKE 'Supprim%'");

echo "<script type='text/JavaScript'>document.form2.actif.value='$actif';</script>";

$p=new personnel();
$p->supprime=strstr($actif, "Supprim")?array(1):array(0);
$p->fetch("nom,prenom", $actif);
$agents=$p->elements;

echo "<table id='tableAgents' class='CJDataTable' data-sort='[[1,\"asc\"],[2,\"asc\"]]' >\n";
echo "<thead>\n";
echo "<tr>\n";

echo "<th class='dataTableNoSort aLeft' >\n";
if (in_array(21, $droits)){
    echo "<input type='checkbox' class='CJCheckAll'/>\n";
}
echo "</th>\n";

echo "<th>Nom</th>";
echo "<th>Pr&#233;nom</th>";
echo "<th class='dataTableHeureFR'>Heures</th>";
echo "<th>Statut</th>";
echo "<th>Service</th>";
if ($config['Multisites-nombre']>1) {
    echo "<th>Sites</th>\n";
}
echo "<th class='dataTableDateFR' >Arriv&#233;e</th>";
echo "<th class='dataTableDateFR' >D&#233;part</th>";
echo "<th class='dataTableDateFR' >Acc&egrave;s</th>";
echo "</thead>\n";
echo "<tbody>\n";
$i=0;
foreach ($agents as $agent) {
    $id=$agent['id'];

    $arrivee = dateFr($agent['arrivee']);
    $depart = dateFr($agent['depart']);
    $last_login=date_time($agent['last_login']);

    $heures=$agent['heures_hebdo']?$agent['heures_hebdo']:null;
    $heures=heure4($heures);
    if (is_numeric($heures)) {
        $heures.="h00";
    }
    $agent['service']=str_replace("`", "'", $agent['service']);

    echo "<tr><td style='white-space:nowrap;'>\n";
    if (in_array(21, $droits)){
        echo "<input type='checkbox' name='chk$i' value='$id' class='checkbox' />\n";
    }
    echo "<a href='{$config['URL']}/agent/$id'><span class='icones' title='Modifier'><i class='fa fa-pencil-square fa-2x'></i></span></a>";
    if (in_array(21, $droits) and $id!=$_SESSION['login_id'] and $id >1) {
        echo "<a href='javascript:popup(\"personnel/suppression.php&amp;id=".$id."\",450,240);'><span class='icones' title='Supprimer'><i class='fa fa-trash fa-2x'></i></span></a>";
    }
    echo "</td>";
    echo "<td>{$agent['nom']}</td>";
    echo "<td>{$agent['prenom']}</td>";
    echo "<td>$heures</td>";
    echo "<td>{$agent['statut']}</td>";
    echo "<td>{$agent['service']}</td>";
    if ($config['Multisites-nombre']>1) {
        $tmp=array();
        if (!empty($agent['sites'])) {
            foreach ($agent['sites'] as $site) {
                if ($site) {
                    $tmp[]=$config["Multisites-site{$site}"];
                }
            }
        }
        $sites=!empty($tmp)?join(", ", $tmp):null;
        echo "<td>$sites</td>";
    }
    echo "<td>$arrivee</td>";
    echo "<td>$depart</td>";
    echo "<td>$last_login</td>";
    echo "</tr>";
    $i++;
}

echo "</tbody>";
echo "</table>";

if (in_array(21, $droits)) {
    echo <<<EOD
        <select name='action' id='action' style='width:200px;'>
            <option value=''></option>
            <option value='edit'>Modifier la sélection</option>
            <option value='delete'>Supprimer la sélection</option>
        </select>

        <input type='button' value='Valider' class='ui-button' style='margin-left:20px;' onclick='agent_list()'/>
EOD;
}

include('dialogbox.php');
?>