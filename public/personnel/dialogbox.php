<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2019 Jérôme Combes

Fichier : public/personnel/dialogbox.php
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la boite de dialogue permettant la modification en masse des fiches agents
Cette page est appelée par le fichier public/personnel/index.php
*/

require_once(__DIR__.'/../activites/class.activites.php');

$db=new db();
$db->select2("select_statuts", null, null, "order by rang");
$statuts=$db->result;

$contrats=array("Titulaire","Contractuel");

// Liste des services
$services = array();
$db=new db();
$db->select2("select_services", null, null, "ORDER BY `rang`");
if ($db->result) {
    foreach ($db->result as $elem) {
        $services[]=$elem;
    }
}

$hours = array();
for ($i=1;$i<40;$i++) {
    if ($config['Granularite']==5) {
        $hours[]=array($i,$i."h00");
        $hours[]=array($i.".08",$i."h05");
        $hours[]=array($i.".17",$i."h10");
        $hours[]=array($i.".25",$i."h15");
        $hours[]=array($i.".33",$i."h20");
        $hours[]=array($i.".42",$i."h25");
        $hours[]=array($i.".5",$i."h30");
        $hours[]=array($i.".58",$i."h35");
        $hours[]=array($i.".67",$i."h40");
        $hours[]=array($i.".75",$i."h45");
        $hours[]=array($i.".83",$i."h50");
        $hours[]=array($i.".92",$i."h55");
    } elseif ($config['Granularite']==15) {
        $hours[]=array($i,$i."h00");
        $hours[]=array($i.".25",$i."h15");
        $hours[]=array($i.".5",$i."h30");
        $hours[]=array($i.".75",$i."h45");
    } elseif ($config['Granularite']==30) {
        $hours[]=array($i,$i."h00");
        $hours[]=array($i.".5",$i."h30");
    } else {
        $hours[]=array($i,$i."h00");
    }
}

// Toutes les activités
$a=new activites();
$a->fetch();
$activites=$a->elements;

foreach ($activites as $elem) {
    $postes_completNoms[]=array($elem['nom'],$elem['id']);
}
// traduction en JavaScript du tableau postes_completNoms pour les fonctions seltect_add* et select_drop
$postes_completNoms_json = json_encode($postes_completNoms);
echo "<script type='text/JavaScript'>\n<!--\n";
echo "complet = JSON.parse('$postes_completNoms_json');\n";
echo "\n-->\n</script>\n";

echo <<<EOD
<div id="dialog-form" title="{$lang['edit_selected_users']}" class='noprint' style='display:none;'>
    <p class="validateTips"></p>
    <form>

    <div class='ui-tabs'>
        <ul>
            <li><a href='#main'>Infos générales</a></li>
            <li><a href='#qualif'>Activités</a></li>
        </ul>

    <div id='main' style='margin-left:70px;padding-top:30px;'>

        <table class='tableauFiches'>

        <tr><td><label for="statut">Statut</label></td>
        <td>
        <select name='statut' id='statut' style='text-align:center;'>
        <option value='-1'>{$lang['do_not_change']}</option>
        <option value=''>Aucun</option>
EOD;

        foreach ($statuts as $elem) {
            echo "<option value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
        }

echo <<<EOD
        <tr><td><label for='contrat'>Contrat</label></td>
        <td>
            <select name='contrat' id='contrat' style='text-align:center;'>
                <option value='-1'>{$lang['do_not_change']}</option>
                <option value=''>Aucun</option>
EOD;
        foreach ($contrats as $elem) {
            echo "<option value='{$elem}'>{$elem}</option>\n";
        }
    
echo <<<EOD
        </select>
        </td></tr>

        <tr><td><label for='service'>Service de rattachement</label></td>
        <td style='white-space:nowrap'>
        <select name='service' id='service' style='text-align:center;'>
            <option value='-1'>{$lang['do_not_change']}</option>
            <option value=''>Aucun</option>
EOD;
        foreach ($services as $elem) {
            echo "<option value='{$elem['valeur']}'>{$elem['valeur']}</option>\n";
        }

echo <<<EOD
        </select>
        </td></tr>

        <tr><td><label for="heures_hebdo">Heures de service public</label></td>
        <td><select id='heures_hebdo' name='heures_hebdo' style='text-align:center;'>
        <option value='-1'>{$lang['do_not_change']}</option>
        <option value='0'>&nbsp;</option>
EOD;
        for ($i=1;$i<101;$i++) {
            echo "<option value='$i%'>$i%</option>\n";
        }

        foreach ($hours as $elem) {
            echo "<option value='{$elem[0]}'>{$elem[1]}</option>\n";
        }

echo <<<EOD
        </select>
        </td></tr>

        <tr>
            <td><label for='heures_travail'>Heures de travail par semaine</label></td>
            <td>
            <select name='heures_travail' id='heures_travail' style='text-align:center;'>
                <option value='-1'>{$lang['do_not_change']}</option>
                <option value='0'>&nbsp;</option>
EOD;

        foreach ($hours as $elem) {
            echo "<option value='{$elem[0]}'>{$elem[1]}</option>\n";
        }

echo <<<EOD
                    </select>
                </td>
            </tr>

            <tr>
                <td><label for='actif'>Service public / Administratif</label></td>
                <td>
                    <select name='actif' id='actif' style='text-align:center;'>
                        <option value='-1'>{$lang['do_not_change']}</option>
                        <option value='Actif'>Service public</option>
                        <option value='Inactif'>Administratif</option>
                    </select>
                </td>
            </tr>

        </table>

    </div> <!-- main -->

    <div id='qualif' style='margin-left:70px;padding-top:30px;'>
        <input type='hidden' name='postes' id='postes' value='-1'/>
        <table style='width:90%;'>
            <tr style='vertical-align:top;'>
                <td>
                    <b>Activités disponibles</b><br/>
                    <div id='dispo_div'>
                        <select id='postes_dispo' name='postes_dispo' style='width:300px;' size='20' multiple='multiple'>
EOD;
                        foreach ($activites as $elem) {
                            echo "<option value='{$elem['id']}'>{$elem['nom']}</option>\n";
                        }

echo <<<EOD
                        </select>
                    </div>
                </td>

                <td style='text-align:center;padding-top:100px;'>
                    <input type='button' style='width:200px' value='Attribuer >>' onclick='select_add("postes_dispo","postes_attribues","postes",300);' /><br/><br/>
                    <input type='button' style='width:200px' value='Attribuer Tout >>' onclick='select_add_all("postes_dispo","postes_attribues","postes",300);' /><br/><br/>
                    <input type='button' style='width:200px' value='<< Supprimer' onclick='select_drop("postes_dispo","postes_attribues","postes",300);' /><br/><br/>
                    <input type='button' style='width:200px' value='<< Supprimer Tout' onclick='select_drop_all("postes_dispo","postes_attribues","postes",300);' /><br/><br/>
                </td>

                <td>
                    <b>Activités attribu&eacute;es</b><br/>
                    <div id='attrib_div'>
                        <select id='postes_attribues' name='postes_attribues' style='width:300px;' size='20' multiple='multiple'>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </div> <!-- qualif -->

    </div> <!-- ui-tabs -->

    </form>
</div>
EOD;

?>