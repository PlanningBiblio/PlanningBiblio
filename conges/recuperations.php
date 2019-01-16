<?php
/*
Planning Biblio, Plugin Congés Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/recuperations.php
Création : 27 août 2013
Dernière modification : 9 janvier 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de voir les demandes de récupération
*/

include_once "class.conges.php";
include_once "personnel/class.personnel.php";

// Initialisation des variables
$admin=in_array(2, $droits)?true:false;
$annee=filter_input(INPUT_GET, "annee", FILTER_SANITIZE_STRING);
$reset=filter_input(INPUT_GET, "reset", FILTER_CALLBACK, array("options"=>"sanitize_on"));
$perso_id=filter_input(INPUT_GET, "perso_id", FILTER_SANITIZE_NUMBER_INT);

if ($admin and $perso_id===null) {
    $perso_id=isset($_SESSION['oups']['recup_perso_id'])?$_SESSION['oups']['recup_perso_id']:$_SESSION['login_id'];
} elseif ($perso_id===null) {
    $perso_id=$_SESSION['login_id'];
}

if (!$annee) {
    $annee=isset($_SESSION['oups']['recup_annee'])?$_SESSION['oups']['recup_annee']:(date("m")<9?date("Y")-1:date("Y"));
}

if ($reset) {
    $annee=date("m")<9?date("Y")-1:date("Y");
    $perso_id=$_SESSION['login_id'];
}

$_SESSION['oups']['recup_annee']=$annee;
$_SESSION['oups']['recup_perso_id']=$perso_id;

$debut=$annee."-09-01";
$fin=($annee+1)."-08-31";
$admin=in_array(2, $droits)?true:false;
$message=null;

// Recherche des demandes de récupérations enregistrées
$c=new conges();
$c->admin=$admin;
$c->debut=$debut;
$c->fin=$fin;
if ($perso_id!=0) {
    $c->perso_id=$perso_id;
}
$c->getRecup();
$recup=$c->elements;

// Recherche des agents
$p=new personnel();
$p->fetch();
$agents=$p->elements;

// Années universitaires
$annees=array();
for ($d=date("Y")+2;$d>date("Y")-11;$d--) {
    $annees[]=array($d,$d."-".($d+1));
}

// Affichage
echo "<h3 class='print_only'>Liste des congés de ".nom($perso_id, "prenom nom").", année $annee-".($annee+1)."</h3>\n";
echo <<<EOD
<h3 class='noprint'>Récupérations</h3>

<div id='liste'>
<h4 class='noprint'>Liste des demandes de récupération</h4>
<form name='form' method='get' action='index.php' class='noprint'>
<p>
<input type='hidden' name='page' value='conges/recuperations.php' />
Ann&eacute;e : <select name='annee'>
EOD;
foreach ($annees as $elem) {
    $selected=$annee==$elem[0]?"selected='selected'":null;
    echo "<option value='{$elem[0]}' $selected >{$elem[1]}</option>";
}
echo "</select>\n";

if ($admin) {
    echo "&nbsp;&nbsp;Agent : ";
    echo "<select name='perso_id'>";
    $selected=$perso_id==0?"selected='selected'":null;
    echo "<option value='0' $selected >Tous</option>";
    foreach ($agents as $agent) {
        $selected=$agent['id']==$perso_id?"selected='selected'":null;
        echo "<option value='{$agent['id']}' $selected >{$agent['nom']} {$agent['prenom']}</option>";
    }
    echo "</select>\n";
}
echo <<<EOD
&nbsp;&nbsp;<input type='submit' value='OK' id='button-OK' class='ui-button'/>
&nbsp;&nbsp;<input type='button' value='Reset' id='button-Effacer' class='ui-button' onclick='location.href="index.php?page=conges/recuperations.php&reset=on"' />
</p>
</form>
<table id='tableRecup' class='CJDataTable' data-sort='[[1]]'>
<thead>
<tr><th class='dataTableNoSort' >&nbsp;</th>
EOD;
echo "<th class='dataTableDateFR'>Date</th>\n";
if ($admin) {
    echo "<th>Agent</th>";
}
echo "<th>Heures</th><th>Commentaires</th><th>Validation</th><th>Crédits</th></tr>\n";
echo "</thead>\n";
echo "<tbody>\n";

foreach ($recup as $elem) {
    $validation="Demand&eacute;e, ".dateFr($elem['saisie'], true);
    $validationStyle="font-weight:bold;";
    if ($elem['saisie_par'] and $elem['saisie_par']!=$elem['perso_id']) {
        $validation.=" par ".nom($elem['saisie_par']);
    }
    $credits=null;
    if ($elem['valide']>0) {
        $validation=nom($elem['valide']).", ".dateFr($elem['validation'], true);
        $validationStyle=null;
        if ($elem['solde_prec']!=null and $elem['solde_actuel']!=null) {
            $credits=heure4($elem['solde_prec'])." &rarr; ".heure4($elem['solde_actuel']);
        }
    } elseif ($elem['valide']<0) {
        $validation="Refus&eacute;, ".nom(-$elem['valide']).", ".dateFr($elem['validation'], true);
        $validationStyle="color:red;font-weight:bold;";
    }

    echo "<tr>";
    echo "<td><a href='index.php?page=conges/recuperation_modif.php&amp;id={$elem['id']}'><span class='pl-icon pl-icon-edit' title='Modifier'></span></a></td>\n";
    $date2=($elem['date2'] and $elem['date2']!="0000-00-00")?" &amp; ".dateFr($elem['date2']):null;
    echo "<td>".dateFr($elem['date'])."$date2</td>\n";
    if ($admin) {
        echo "<td>".nom($elem['perso_id'])."</td>";
    }
    echo "<td>".heure4($elem['heures'])."</td>\n";
    echo "<td>".str_replace("\n", "<br/>", $elem['commentaires'])."</td><td style='$validationStyle'>$validation</td><td>$credits</td></tr>\n";
}

echo <<<EOD
</tbody>
</table>
</div> <!-- liste -->

<div class='noprint'>
<br/><button id='dialog-button' class='ui-button'>Nouvelle demande</button>
</div>

<div id="dialog-form" title="Nouvelle demande" class='noprint'>
  <p class="validateTips">Veuillez sélectionner le jour concerné par votre demande et le nombre d'heures à récuperer et un saisir un commentaire.</p>
  <form>
  <fieldset>
    <table class='tableauFiches'>
EOD;
if ($admin) {
    echo <<<EOD
    <tr><td><label for="agent">Agent</label></td>
    <td><select id='agent' name='agent' style='text-align:center;'>
      <option value=''>&nbsp;</option>
EOD;
    foreach ($agents as $elem) {
        $selected=$elem['id']==$perso_id?"selected='selected'":null;
        echo "<option value='{$elem['id']}' $selected >".nom($elem['id'])."</option>\n";
    }
    echo "</select></td></tr>\n";
}

$label=($config['Recup-DeuxSamedis'])?"Date (1<sup>er</sup> samedi)":"Date";

echo <<<EOD
    <tr><td><label for="date">$label</label></td>
    <td><input type="text" name="date" id="date" class="text ui-widget-content ui-corner-all datepicker"/></td></tr>
EOD;

  if ($config['Recup-DeuxSamedis']) {
      echo <<<EOD
      <tr><td><label for="date2">Date (2<sup>ème</sup> samedi) (optionel)</label></td>
      <td><input type="text" name="date2" id="date2" class="text ui-widget-content ui-corner-all datepicker"/></td></tr>
EOD;
  }

echo <<<EOD
    <tr><td><label for="heures">Heures</label></td>
    <td><select id='heures' name='heures' style='text-align:center;'>
      <option value=''>&nbsp;</option>
EOD;
    for ($i=0;$i<17;$i++) {
        echo "<option value='{$i}.00' >{$i}h00</option>\n";
        echo "<option value='{$i}.25' >{$i}h15</option>\n";
        echo "<option value='{$i}.50' >{$i}h30</option>\n";
        echo "<option value='{$i}.75' >{$i}h45</option>\n";
    }
echo <<<EOD
      </select></td></tr>
      <tr><td><label for="commentaires">Commentaire</label></td>
      <td><textarea name="commentaires" id="commentaires" ></textarea></td></tr>
    </table>
  </fieldset>
  </form>
</div>
EOD;
?>

<script type='text/JavaScript'>
<?php
// Delai limite pour les demandes de récupération
echo "var limitDefaut='{$config['Recup-DelaiDefaut']}';";
echo "var limitTitulaire1='{$config['Recup-DelaiTitulaire1']}';";
echo "var limitTitulaire2='{$config['Recup-DelaiTitulaire2']}';";
echo "var limitContractuel1='{$config['Recup-DelaiContractuel1']}';";
echo "var limitContractuel2='{$config['Recup-DelaiContractuel2']}';";
echo "var perso_id=$perso_id;";
echo "var categories=new Array();";
foreach ($agents as $elem) {
    echo "categories[{$elem['id']}]='{$elem['categorie']}';";
}
// Samedis seulement
echo "var samediSeulement=false;";
if ($config['Recup-SamediSeulement']) {
    echo "var samediSeulement=true;";
}
?>
$(function() {
  var date = $( "#date" ),
    date2 = $( "#date2" ),
    heures = $( "#heures" ),
    commentaires = $( "#commentaires" ),
    allFields = $( [] ).add( date ).add( heures );

  $( "#dialog-form" ).dialog({
    autoOpen: false,
    height: 480,
    width: 650,
    modal: true,
    buttons: {
      "Enregistrer": function() {
	// Calcul du delai limit pour la demande de récup en fonction de la catégorie de l'agent
	var admin=false;
	if($("#agent option:selected").val()){
	  perso_id=$("#agent option:selected").val();
	  admin=true;
	}
	if(categories[perso_id]=="Titulaire"){
	  if($("#date2").val()){
	    if(limitTitulaire2=="Défaut"){
	      limitJours=limitDefaut;
	    }else{
	      limitJours=limitTitulaire2*30;
	    }
	  }else{
	    if(limitTitulaire1=="Défaut"){
	      limitJours=limitDefaut;
	    }else{
	      limitJours=limitTitulaire1*30;
	    }
	  }
	}
	else if(categories[perso_id]=="Contractuel"){
	  if($("#date2").val()){
	    if(limitContractuel2=="Défaut"){
	      limitJours=limitDefaut;
	    }else{
	      limitJours=limitContractuel2*7;
	    }
	  }else{
	    if(limitContractuel1=="Défaut"){
	      limitJours=limitDefaut;
	    }else{
	      limitJours=limitContractuel1*7;
	    }
	  }
	}
	else{
	  limitJours=limitDefaut;
	}

	var bValid = true;
	allFields.removeClass( "ui-state-error" );
 	bValid = bValid && checkRegexp( date, /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/i, "La date doit être au format JJ/MM/AAAA" );
	if(samediSeulement){
	  bValid = bValid && checkSamedi(date,"Vous devez choisir un samedi");
	}
	if($("#date2").val()){
	  bValid = bValid && checkRegexp( date2, /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/i, "La date doit être au format JJ/MM/AAAA" );
	  bValid = bValid && checkDate2(date, date2,"La 2ème date doit être supérieure à la première");
	  if(samediSeulement){
	    bValid = bValid && checkSamedi(date2,"Vous devez choisir un samedi");
	  }
	}
	bValid = bValid && checkLength( heures, "heures", 4, 5 );
	if(admin && checkDateAge( date, limitJours, "La demande de récupération doit être effectuée dans les "+limitJours+" jours",false)==false){
	  res=confirm("Attention, la demande de récupération doit être effectuée dans les "+limitJours+" jours.\nEn tant qu'administrateur, vous pouvez outrepasser cette règle.\nVoulez-vous continuer ?");
	  bValid = bValid && res;
	}
	else{
	  bValid = bValid && checkDateAge( date, limitJours, "La demande de récupération doit être effectuée dans les "+limitJours+" jours");
	}

	bValid = bValid && verifRecup($("#date"));

	<?php
    if ($config['Recup-DeuxSamedis']) {
        echo "if($(\"#date2\").val())\n";
        echo "bValid = bValid && verifRecup($(\"#date2\"));\n";
    }
    ?>

	if ( bValid ) {
	  // Enregistre la demande
	  $.ajax({
	    url: "conges/ajax.enregistreRecup.php",
	    dataType: "json",
	    data: {date: date.val(), date2: date2.val(), heures: heures.val(), commentaires: commentaires.val(), perso_id: perso_id, CSRFToken: $('#CSRFSession').val()},
	    type: "post",
	    success: function(result){
	      if(result[0]=="Demande-OK"){
	      
		// Préparation de l'affichage des erreurs et confirmations
		var msg=encodeURIComponent("Votre demande a été enregistrée");
		
		var msg2=null;
		var msg2Type=null;
		if(result[1]!=undefined){
		  msg2=encodeURIComponent(result[1]);
		  msg2Type="error";
		}
		
		// Affiche la liste des demandes après enregistrement
		document.location.href="index.php?page=conges/recuperations.php&msgType=success&msg="+msg+"&msg2Type="+msg2Type+"&msg2="+msg2;
		// Ferme le dialog
		$( this ).dialog( "close" );
	      }else{
		updateTips("Erreur lors de l'enregistrement de la récupération");
	      }
	    },
	    error: function (result){
	      updateTips("Erreur lors de l'enregistrement de la récupération");
	    },
	  });
	}
      },

      Annuler: function() {
	$( this ).dialog( "close" );
      }
    },

    close: function() {
      allFields.val( "" ).removeClass( "ui-state-error" );
    }
  });

  $( "#dialog-button" )
    .click(function() {
      date.datepicker("disable");
      $( "#dialog-form" ).dialog( "open" );
      date.datepicker("enable");
      return false;
    });

});
</script>