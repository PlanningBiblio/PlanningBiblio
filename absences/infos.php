<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/infos.php
Création : mai 2011
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'ajouter une information relative à la gestion des absences : Formulaire, confirmation, validation

Page appelée par la page index.php
*/

require_once "class.absences.php";

//	Initialisation des variables
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$debut=filter_input(INPUT_GET, "debut", FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET, "fin", FILTER_SANITIZE_STRING);
$suppression=filter_input(INPUT_GET, "suppression", FILTER_SANITIZE_NUMBER_INT);
$validation=filter_input(INPUT_GET, "validation", FILTER_SANITIZE_NUMBER_INT);
$texte=trim(filter_input(INPUT_GET, "texte", FILTER_SANITIZE_STRING));
$CSRFToken=trim(filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING));

// Contrôle sanitize_dateFr en 2 temps pour éviter les erreurs CheckMarx
$debut=filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);

echo "<h3>Informations sur les absences</h3>\n";
//			----------------		Suppression							-------------------------------//
if ($suppression and $validation) {
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete("absences_infos", array("id"=>$id));
    echo "<b>L'information a été supprimée</b>";
    echo "<br/><br/><a href='index.php?page=absences/index.php'>Retour</a>\n";
} elseif ($suppression) {
    echo "<h4>Etes vous sûr de vouloir supprimer cette information ?</h4>\n";
    echo "<form method='get' action='#' name='form'>\n";
    echo "<input type='hidden' name='page' value='absences/infos.php'/>\n";
    echo "<input type='hidden' name='CSRFToken' value='$CSRFSession'/>\n";
    echo "<input type='hidden' name='suppression' value='1'/>\n";
    echo "<input type='hidden' name='validation' value='1'/>\n";
    echo "<input type='hidden' name='id' value='$id'/>\n";
    echo "<input type='button' value='Non' onclick='history.back();' class='ui-button'/>\n";
    echo "<input type='submit' value='Oui' class='ui-button' style='margin-left:30px;'/>\n";
    echo "</form>\n";
}
//			----------------		FIN Suppression							-------------------------------//
//			----------------		Validation du formulaire							-------------------------------//
elseif ($validation) {		//		Validation
  echo "<b>Votre demande a été enregistrée</b>\n";
    echo "<br/><br/><a href='index.php?page=absences/index.php'>Retour</a>\n";
    if ($id) {
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->update("absences_infos", array("debut"=>$debutSQL,"fin"=>$finSQL,"texte"=>$texte), array("id"=>$id));
    } else {
        $db=new db();
        $db->CSRFToken = $CSRFToken;
        $db->insert("absences_infos", array("debut"=>$debutSQL,"fin"=>$finSQL,"texte"=>$texte));
    }
} elseif ($debut) {		//		Vérification
    $fin=$fin?$fin:$debut;
    echo "<h4>Confirmation</h4>";
    echo "Du $debut au $fin";
    echo "<br/>";
    echo $texte;
    echo "<br/><br/>";
    echo "<form method='get' action='index.php' name='form'>";
    echo "<input type='hidden' name='page' value='absences/infos.php'/>\n";
    echo "<input type='hidden' name='CSRFToken' value='$CSRFSession'/>\n";
    echo "<input type='hidden' name='debut' value='$debut'/>\n";
    echo "<input type='hidden' name='fin' value='$fin'/>\n";
    echo "<input type='hidden' name='texte' value='$texte'/>\n";
    echo "<input type='hidden' name='id' value='$id'/>\n";
    echo "<input type='hidden' name='validation' value='1'/>\n";
    echo "<input type='button' value='Annuler' onclick='history.back();' class='ui-button'/>";
    echo "<input type='submit' value='Valider' class='ui-button' style='margin-left:30px;'/>\n";
    echo "</form>";
}
//			----------------		FIN Validation du formulaire							-------------------------------//
else {
    if ($id) {
        $db=new db();
        $db->select2("absences_infos", "*", array("id"=>$id));
        $debut=dateFr3($db->result[0]['debut']);
        $fin=dateFr3($db->result[0]['fin']);
        $texte=$db->result[0]['texte'];
        echo "<h4>Modifications des informations sur les absences</h4>\n";
    } else {
        $debut=null;
        $fin=null;
        $texte=null;
        echo "<h4>Ajout d'une information</h4>\n";
    }

    echo "
  <form method='get' action='index.php' name='form' onsubmit='return verif_form(\"debut=date1;fin=date2;texte\");'>\n
  <input type='hidden' name='page' value='absences/infos.php'/>\n
  <input type='hidden' name='id' value='$id'/>\n
  <table class='tableauFiches'>
  <tr><td><label class='intitule'>Date de début</label>
  </td><td>
  <input type='text' name='debut' value='$debut' style='width:100%;' class='datepicker'/>
  </td></tr>
  <tr><td><label class='intitule'>Date de fin</label>
  </td><td>
  <input type='text' name='fin' value='$fin' style='width:100%;' class='datepicker'/>
  </td></tr>
  <tr><td><label class='intitule'>Texte</label>
  </td><td>
  <textarea name='texte' rows='3' cols='25' class='ui-widget-content ui-corner-all'>".$texte."</textarea>
  </td></tr><tr><td>&nbsp;
  </td></tr>
  <tr><td colspan='2' style='text-align:center;'>\n";
    if ($id) {
        echo "<a href='index.php?page=absences/infos.php&amp;id=$id&amp;suppression=1' class='ui-button' >Supprimer</a>";
    }
    echo "<a href='index.php?page=absences/index.php' class='ui-button' style='margin-left:30px;'>Annuler</a>";
    echo "<input type='submit' value='Valider' class='ui-button' style='margin-left:30px;'/>
  </td></tr></table>
  </form>";
}
