<?php
/**
Planning Biblio, Version 2.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/postes_cfg/index.php
Création : mai 2011
Dernière modification : 12 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Page d'index de gestion des tableaux. Affiche la liste des tableaux, des groupes de tableaux et les lignes de séparation.

Page appelée par le fichier index.php, accessible via le menu administration / Les tableaux
*/

require_once "class.tableaux.php";

use App\Model\PlanningTableConfig;

echo "<h2>Gestion des tableaux</h2>\n";


// Tables
$entityManager = $GLOBALS['entityManager'];
$table_objects = $entityManager->getRepository(PlanningTableConfig::class)
                        ->findBy(array(), array('nom' => 'asc'));

// Dernières utilisations des tableaux
$tabAffect=array();
$db=new db();
$db->select2("pl_poste_tab_affect", null, null, "order by `date` asc");
if ($db->result) {
    foreach ($db->result as $elem) {
        $tabAffect[$elem['tableau']]=$elem['date'];
    }
}

$one_year_ago = date("Y-m-d H:i:s", strtotime("- 1 year"));
$tables = array();
$deleted_tables = array();
foreach ($table_objects as $table) {
    $t = $table->properties();

    $t['last_use'] = 'Jamais';
    if (array_key_exists($table->tableau(), $tabAffect)) {
        $t['last_use'] = dateFr($tabAffect[$table->tableau()]);
    }

    if ($table->supprime()) {
        if (date_format($table->supprime(), 'Y-m-d H:i:s') >= $one_year_ago) {
            $deleted_tables[] = $t;
        }
        continue;
    }

    $site = "Multisites-site{$table->site()}";
    $t['site'] = '';
    if ($config['Multisites-nombre'] > 1) {
        $t['site'] = $config[$site];
    }

    $tables[] = $t;
}

// Affichage

// 1. Tables
echo $twig->render('admin/tables_config/tables_list.html.twig',
    array(
        'deleted_tables' => $deleted_tables,
        'tables' => $tables,
        'multisites_nombre' => $config['Multisites-nombre']
    )
);

echo "<div id='tableaux-groupes' class='tableaux-cfg' >\n";
//		Groupes
$t=new tableau();
$t->fetchAllGroups();
$groupes=$t->elements;

echo <<<EOD
<h3>Groupes</h3>

<p><input type='button' value='Nouveau groupe' class='ui-button' onclick='location.href="index.php?page=planning/postes_cfg/groupes.php";' /></p>

<table class='CJDataTable' id='table-groups' data-noExport='1'  data-sort='[[1,"asc"]]'>
<thead>
<tr><th class='dataTableNoSort'>&nbsp;</th>
EOD;
echo "<th>Nom</th>\n";
if ($config['Multisites-nombre']>1) {
    echo "<th>Site</th>\n";
}
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

if (is_array($groupes)) {
    foreach ($groupes as $elem) {
        echo "<tr id='tr-groupe-{$elem['id']}'><td><a href='index.php?page=planning/postes_cfg/groupes.php&amp;id={$elem['id']}'>\n";
        echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
        echo "<a href='javascript:supprimeGroupe({$elem['id']});'>\n";
        echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
        echo "</td>\n";
        echo "<td id='td-groupe-{$elem['id']}-nom'>{$elem['nom']}</td>\n";
        if ($config['Multisites-nombre']>1) {
            echo "<td>".$config["Multisites-site{$elem['site']}"]."</td>\n";
        }
        echo "</tr>\n";
    }
}
echo "</tbody>\n";
echo "</table>\n";

echo <<<EOD
</div> <!-- tableaux-groupes -->

<div id='tableaux-separations' class='tableaux-cfg'>
EOD;

//	2.	Lignes de separation

$db=new db();
$db->select("lignes", null, null, "order by nom");

echo <<<EOD
<h3>Lignes de s&eacute;paration</h3>

<form method='get' action='index.php'>
<input type='hidden' name='page' value='planning/postes_cfg/lignes_sep.php' />
<input type='hidden' name='action' value='ajout' />
<input type='hidden' name='cfg-type' value='lignes_sep' />
<p><input type='submit' value='Nouvelle ligne' class='ui-button'/></p>
</form>
EOD;

echo "<table class='CJDataTable' id='table-separations' data-noExport='1'  data-sort='[[1,\"asc\"]]'>\n";
echo "<thead>\n";
echo "<tr><th class='dataTableNoSort'>&nbsp;</th>\n";
echo "<th>Nom</th></tr>\n";
echo "</thead>\n";

echo "<tbody>\n";
if ($db->result) {
    foreach ($db->result as $elem) {
        $db2=new db();
        $db2->select("pl_poste_lignes", "*", "poste='{$elem['id']}' AND type='ligne'");
        $delete=$db2->result?false:true;

        echo "<tr id='tr-ligne-{$elem['id']}' >\n";
        echo "<td><a href='index.php?page=planning/postes_cfg/lignes_sep.php&amp;action=modif&amp;id={$elem['id']}'>\n";
        echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
        if ($delete) {
            echo "<a href='javascript:supprimeLigne({$elem['id']});'>\n";
            echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
        }
        echo "</td>\n";
        echo "<td id='td-ligne-{$elem['id']}-nom' >{$elem['nom']}</td></tr>\n";
    }
}

echo <<<EOD
</tbody>
</table>

</div> <!-- tableaux-separations -->

EOD;
