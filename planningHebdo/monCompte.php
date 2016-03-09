<?php
/**
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planningHebdo/monCompte.php
Création : 23 juillet 2013
Dernière modification : 22 juin 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant de modifier son mot de passe et son planning de présence hebdomadaire
*/

require_once "class.planningHebdo.php";

// Recherche de la config
$p=new planningHebdo();
$p->getConfig();
$configHebdo=$p->config;

// Initialisation des variables
// Plannings de présence
// Années universitaires (si utilisation des périodes définies)
$tmp=array();
$tmp[0]=date("n")<9?(date("Y")-1)."-".(date("Y")):(date("Y"))."-".(date("Y")+1);
$tmp[1]=date("n")<9?(date("Y"))."-".(date("Y")+1):(date("Y")+1)."-".(date("Y")+2);
$message=null;

// Contrôle si les périodes sont renseignées avant d'afficher les années universitaires dans le menu déroulant
$annees=array();
foreach($tmp as $elem){
  $p=new planningHebdo();
  $p->dates=array($elem);
  $p->getPeriodes();
  if($p->periodes[0][0] and $p->periodes[0][1] and $p->periodes[0][2] and $p->periodes[0][3]){
    $annees[]=$elem;
  }
}

// Informations sur l'agent
$p=new personnel();
$p->fetchById($_SESSION['login_id']);
$sites=$p->elements[0]['sites'];

// Crédits (congés, récupérations)
if(in_array("conges",$plugins)){
  $credits['annuel']=$p->elements[0]['congesAnnuel'];
  $credits['conges']=$p->elements[0]['congesCredit'];
  $credits['reliquat']=$p->elements[0]['congesReliquat'];
  $credits['anticipation']=$p->elements[0]['congesAnticipation'];
  $credits['recuperation']=$p->elements[0]['recupSamedi'];
  $credits['joursAnnuel']=number_format($credits['annuel']/7,2,","," ");
  $credits['joursConges']=number_format($credits['conges']/7,2,","," ");
  $credits['joursReliquat']=number_format($credits['reliquat']/7,2,","," ");
  $credits['joursAnticipation']=number_format($credits['anticipation']/7,2,","," ");
  $credits['joursRecuperation']=number_format($credits['recuperation']/7,2,","," ");
}

?>
<!--	Menu	-->
<h3>Mon Compte</h3>

<div class='ui-tabs'>
<ul>
<?php
if(in_array("conges",$plugins)){
  echo <<<EOD
    <li><a href='#planningPresence'>Mes plannings de présence</a></li>
    <li><a href='#credits'>Mes crédits</a></li>
    <li><a href='#motDePasse'>Mon mot de passe</a></li>
EOD;
}
else{
  echo <<<EOD
    <li><a href='#planningPresence'>Mes plannings de présence</a></li>
    <li><a href='#motDePasse'>Mon mot de passe</a></li>
EOD;
}
?>
</ul>

<!-- Planning de présence -->
<div id='planningPresence'>

<div style='display: inline-block; width:300px;'>
<h3>Planning de présence</h3>
</div>

<?php
if($config['PlanningHebdo-Agents']){
  echo <<<EOD
  <div style='display: inline-block; width:300px; position: absolute; right: 22px; text-align: right; margin-top:22px;'>
  <a href='index.php?page=planningHebdo/modif.php&retour=monCompte.php' class='ui-button'>
    Entrer un nouveau planning</a>
  </div>
EOD;
}
?>

<!-- Historique des plannings de présence -->
<div id='historique'>
Mes plannings de présence
<br/>
<table id='tablePresenceMonCompte' class='CJDataTable' data-sort='[[1],[2],[3]]'>
<thead>
  <tr>
    <th class='dataTableNoSort'>&nbsp;</th>
    <th class='dataTableDateFR'>Début</th>
    <th class='dataTableDateFR'>Fin</th>
    <th class='dataTableDateFR'>Saisie</th>
    <th>Validation</th>
    <th>Actuel</th>
    <th>Commentaires</th>
  </tr>
</thead>
<tbody>
<?php
$p=new planningHebdo();
$p->perso_id=$_SESSION['login_id'];
$p->fetch();
foreach($p->elements as $elem){
  $actuel=$elem['actuel']?"Oui":null;
  $validation="N'est pas validé";
  if($elem['valide']){
    $validation=nom($elem['valide']).", ".dateFr($elem['validation'],true);
  }
  $planningRemplace=$elem['remplace']==0?dateFr($elem['saisie'],true):$planningRemplace;
  $commentaires=$elem['remplace']?"Remplace le planning <br/>du $planningRemplace":null;
  $arrow=$elem['remplace']?"<font style='font-size:20pt;'>&rdsh;</font>":null;

  echo "<tr>";
  echo "<td style='white-space:nowrap;'>$arrow <a href='index.php?page=planningHebdo/modif.php&amp;id={$elem['id']}&amp;retour=monCompte.php'/>";
    echo "<span class='pl-icon pl-icon-edit' title='Voir'></span></a></td>";
  echo "<td>".dateFr($elem['debut'])."</td>";
  echo "<td>".dateFr($elem['fin'])."</td>";
  echo "<td>".dateFr($elem['saisie'],true)."</td>";
  echo "<td>$validation</td>";
  echo "<td>$actuel</td>";
  echo "<td>$commentaires</td>";
  echo "</tr>\n";
}

?>
</tbody>
</table>
</div> <!-- Historique' -->

</div> <!-- PlanningPresence -->

<!-- Crédits -->
<?php
if(in_array("conges",$plugins)){
  echo <<<EOD
  <div id='credits' style='margin-left:80px;display:none;'>
  <h3>Crédits</h3>
  <table class='tableauFiches'>
  <tr><td style='font-weight:bold;' colspan='2'>Congés</td></tr>
EOD;
  echo "<tr><td>Crédit annuel</td><td style='text-align:right;'>".heure4($credits['annuel'])."</td><td style='text-align:right;'>{$credits['joursAnnuel']} jours</td></tr>\n";
  echo "<tr><td>Crédit restant</td><td style='text-align:right;'>".heure4($credits['conges'])."</td><td style='text-align:right;'>{$credits['joursConges']} jours</td></tr>\n";
  echo "<tr><td>Reliquat</td><td style='text-align:right;'>".heure4($credits['reliquat'])."</td><td style='text-align:right;'>{$credits['joursReliquat']} jours</td></tr>\n";
  echo "<tr><td>Solde débiteur</td><td style='text-align:right;'>".heure4($credits['anticipation'])."</td><td style='text-align:right;'>{$credits['joursAnticipation']} jours</td></tr>\n";
  echo "<tr><td style='font-weight:bold;padding-top:20px;' colspan='2'>Récupérations</td></tr>\n";
  echo "<tr><td>Crédit</td><td style='text-align:right;'>".heure4($credits['recuperation'])."</td><td style='text-align:right;'>{$credits['joursRecuperation']} jours</td></tr>\n";
  echo "</table>\n";
  echo "<p style='font-style:italic;margin:30px 0 0 10px;'>Le nombre de jours est calculé sur la base de 7 heures par jour.</p>\n";
  echo "</div>\n";
}
?>
<!-- Crédits-->

<!-- Mot de Passe -->
<div id='motDePasse' style='margin-left:80px;display:none;'>
<?php
// Mot de passe modifiable seulement si authentification SQL
if($_SESSION['oups']['Auth-Mode']=="SQL"){
  include "personnel/password.php";
}
else{
  echo "<h3>Modification du mot de passe</h3>\n";
  echo "Vous utilisez un système d'authentification centralisé.<br/>\n";
  echo "Votre mot de passe ne peut pas être modifié à partir du planning.<br/>\n";
}
?>
</div> <!-- motDePasse -->
</div> <!-- ui-tabs -->