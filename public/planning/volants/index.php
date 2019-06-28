<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/volants/index.php
Création : 7 avril 2018
Dernière modification : 7 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>


Description :
Gestion des agents volants

Cette page est appelée par la page index.php
*/

require_once 'class.volants.php';

// Sélection de la date / de la semaine
$date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);

if (!$date) {
    if (!empty($_SESSION['oups']['volants_date'])) {
        $date = $_SESSION['oups']['volants_date'];
    } else {
        $date = date('Y-m-d');
    }
}

$_SESSION['oups']['volants_date'] = $date;

$d = new datePl($date);

$date = $d->dates[0];
$w = $d->semaine;
$week = dateFr($d->dates[0])." au ".dateFr($d->dates[6]);

// Semaine précédente
$date1 = date('Y-m-d', strtotime($date.' -1 week'));

// Semaine suivante
$date2 = date('Y-m-d', strtotime($date.' +1 week'));


// Agents disponibles et sélectionnés
$v = new volants();
$v->fetch($date);
$selected = $v->selected;
$tous = $v->tous;

?>

<!-- Affichage du titre -->
<div id='date_planning' class='volants-title'>
  <h2>Sélection des agents volants</h2>
  <?php echo "<h2 class='important'>semaine $w, du $week</h2>"; ?>
  <br/>
  <?php echo "<a href='?page=planning/volants/index.php&amp;date=$date1' > Semaine précédente </a>"; ?>
  <?php echo "<a href='?page=planning/volants/index.php&amp;date=$date2' style='margin-left:200px;'> Semaine suivante </a>"; ?>
</div>


<!-- Affichage du calendrier -->
<div id='pl-calendar' class='datepicker volants-calendar'></div>
<input type='hidden' name='date' id='date' value='<?php echo $date; ?>' />

<div id='volants-content'>

<!-- Affichage des agents disponibles -->
<div id='volants-dispo-div'>
<p><strong>Agents disponibles</strong></p>
<select id='volants-dispo' name='dispo' multiple='multiple'>
<?php
foreach ($tous as $elem) {
    $style = in_array($elem['id'], $selected) ? "style='display:none;'" : null;
    echo "<option value='{$elem['id']}' class='volants-dispo dispo_{$elem['id']}' data-id='{$elem['id']}' $style >{$elem['nom']} {$elem['prenom']}</option>\n";
}
?>
</select>
</div>

<!-- Affichage des bouttons -->
<div id='volants-buttons-div'>
<input type='button' class='ui-button' id='volants-add' value='Ajouter >>' /><br/><br/>
<input type='button' class='ui-button' id='volants-add-all' value='Ajouter Tout >>' /><br/><br/>
<input type='button' class='ui-button' id='volants-remove' value='<< Supprimer' /><br/><br/>
<input type='button' class='ui-button' id='volants-remove-all' value='<< Supprimer Tout' /><br/><br/>
</div>

<!-- Affichage des agents sélectionnés -->
<div id='volants-selectionnes-div'>
<p><strong>Agents sélectionnés</strong></p>
<select id='volants-selectionnes' name='selectionnes' multiple='multiple'>
<?php
foreach ($tous as $elem) {
    $style = ! in_array($elem['id'], $selected) ? "style='display:none;'" : null;
    echo "<option value='{$elem['id']}' class='volants-selectionnes selected_{$elem['id']}' data-id='{$elem['id']}' $style >{$elem['nom']} {$elem['prenom']}</option>\n";
}
?>
</select>
</div>

<!-- Validation -->
<div id='volants-validation'>
<input type='button' class='ui-button' id='submit' value='Valider' />
</div>

</div>