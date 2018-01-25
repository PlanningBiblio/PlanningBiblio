<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : notifications/index.php
Création : 16 janvier 2018
Dernière modification : 25 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet la sélection, agent par agent, des responsables hierarchiques pour les notifications et la validation niveau 1 des absences et congés
Cette page est appelée par le fichier index.php
*/

require_once __DIR__."/../personnel/class.personnel.php";

// Initialisation des variables
$actif=filter_input(INPUT_GET,"actif",FILTER_SANITIZE_STRING);

if(!$actif){
  $actif = isset($_SESSION['perso_actif']) ? $_SESSION['perso_actif'] : 'Actif';
}

$_SESSION['perso_actif']=$actif;

$option1 = $actif == 'Actif' ? "selected='selected'" : null;
$option2 = $actif == 'Inactif' ? "selected='selected'" : null;

?>

<form name='form2' action='index.php' method='get'>
<input type='hidden' name='page' value='notifications/index.php' />
<table style='margin-bottom:10px;'><tr style='vertical-align:center;'>
<td style='width:370px;'>
<h3 style='margin:0;'>Validations des absences et notifications</h3>
</td><td>
Voir les agents
</td><td style='width:150px;'>
<select name='actif' onchange='document.form2.submit();' class='ui-widget-content ui-corner-all'>
<option value='Actif' <?php echo $option1; ?> >Service public</option>
<option value='Inactif' <?php echo $option2; ?> >Administratif</option>
</select>
</td></tr></table>
</form>


<?php
//		Suppression des agents dont la date de départ est passée		//

$p=new personnel();
$p->supprime=array(0);
$p->responsablesParAgent = true;
$p->fetch("nom,prenom",$actif);
$agents=$p->elements;

echo "<form name='form' method='post' action='index.php' onsubmit='return confirm(\"Etes vous sûr de vouloir supprimer les agents sélectionnés ?\");'>\n";
echo "<table id='tableAgents' class='CJDataTable' data-sort='[[1,\"asc\"],[2,\"asc\"]]' >\n";
echo "<thead>\n";
echo "<tr><th class='dataTableNoSort aLeft' ><input type='checkbox' class='CJCheckAll'/></th>\n";

echo "<th>Nom</th>";
echo "<th>Pr&#233;nom</th>";
echo "<th>Service</th>";
echo "<th>Statut</th>";
if($config['Multisites-nombre']>1){
  echo "<th>Sites</th>\n";
}
echo "<th>Validations / notifications</th>";
echo "</thead>\n";
echo "<tbody>\n";
$i=0;
foreach($agents as $agent){
  $id=$agent['id'];
  
  $agent['service']=str_replace("`","'",$agent['service']);

  echo "<tr><td style='white-space:nowrap;'>\n";
  echo "<input type='checkbox' name='chk$i' value='$id' class='checkboxes'/>\n";
  echo "<a href='#' data-id='$id' class='edit-icon' ><span class='pl-icon pl-icon-edit' title='Modifier'></span></a>";
  echo "</td>";
  echo "<td>{$agent['nom']}</td>";
  echo "<td>{$agent['prenom']}</td>";
  echo "<td>{$agent['service']}</td>";
  echo "<td>{$agent['statut']}</td>";
  if($config['Multisites-nombre']>1){
    $tmp=array();
    if(!empty($agent['sites'])){
      foreach($agent['sites'] as $site){
	if($site){
	  $tmp[]=$config["Multisites-site{$site}"];
	}
      }
    }
    $sites=!empty($tmp)?join(", ",$tmp):null;
    echo "<td>$sites</td>";
  }

  $responsables = array();
  foreach($agent['responsables'] as $resp){
    if(!empty($resp['responsable']) and array_key_exists($resp['responsable'], $agents)){
      $notification = $resp['notification'] ? 1 : 0 ;
      $tmp = "<span class='resp_$id' data-resp='{$resp['responsable']}' data-notif='$notification' >";
      $tmp .= nom($resp['responsable'],$format="nom p", $agents);
      if($notification){
        $tmp .= ' - Notifications';
//       } elseif($resp['notification'] === '0') {
//         $tmp .= " - <span class='striped red'>Notifications</span>";
      }
      $tmp .= "</span>";
      $responsables[] = $tmp;
    }
  }

  if(!empty($responsables)){
    usort($responsables, 'cmp_strip_tags');
    $responsables = implode('<br/>', $responsables);
    echo "<td>$responsables</td>\n";
  } else {
    echo "<td>&nbsp;</td>\n";
  }

  echo "</tr>";
  $i++;
}

echo "</tbody>";
echo "</table>";
echo "<input type='hidden' name='CSRFToken' id='CSRFToken' value='$CSRFSession' />\n";
echo "<input type='button' value='Modifier la sélection' class='ui-button' id='update-button' />\n";
echo "</form>\n";
?>

<div id="update-form" title="Validations / Notifications" class='noprint' style='display:none;'>
  <p class="validateTips" style='text-align:justify;'>Choisissez les responsables qui ont le droit de valider les absences et congés des agents sélectionnés.<br/>Cochez la case s'ils doivent être notifiés lors de l'enregistrement d'une nouvelle absence ou demande de congés.</p>
  <form>
  <table class='tableauFiches'>

  <thead style='text-align: left;'>
  <tr>
    <th style='padding-bottom:10px;'>Responsables</th>
    <th style='padding-bottom:10px;'>Notifications</th>
  </tr>
  </thead>

  <tbody>
  <?php
  for($i = 0; $i < 5; $i++){
    echo "<tr><td>\n";
    echo "<select name='responsable-$i' id='responsable-$i' class='responsables' data-id='$i'>\n";
    echo "<option value=''>&nbsp;</option>\n";
    foreach($agents as $agent){
      echo "<option value='{$agent['id']}'>{$agent['nom']} {$agent['prenom']}</option>\n";
    }
    echo "</select>\n";
    echo "</td><td>\n";
    echo "<input type='checkbox' name='notification-$i' id='notification-$i' class='notifications' data-id='$i'/>\n";
    echo "</td></tr>\n";
  }
  ?>
  </tbody>
  </table>
    
    
  </form>
</div>