<?php
/*
Planning Biblio, Version 1.5.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : include/function.php
Création : mai 2011
Dernière modification : 29 août 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Page contenant les fonctions PHP communes
Page appelée par les fichiers index.php, setup/index.php et planning/poste/menudiv.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

class datePl{
  var $dates;
  var $jour;
  var $jour_complet;
  var $sam;
  var $sem;
  var $semaine;
  var $position;
  
  function datePl($date){
    $yyyy=substr($date,0,4);
    $mm=substr($date,5,2);
    $dd=substr($date,8,2);
    $this->semaine=date("W", mktime(0, 0, 0, $mm, $dd, $yyyy));
    $this->sem=($this->semaine%2);
    $this->sam="semaine";
    $position=date("w", mktime(0, 0, 0, $mm, $dd, $yyyy));
    $this->position=$position;
    switch($position){
      case 1 : $this->jour="lun";	$this->jour_complet="lundi";		break;
      case 2 : $this->jour="mar";	$this->jour_complet="mardi";		break;
      case 3 : $this->jour="mer";	$this->jour_complet="mercredi";		break;
      case 4 : $this->jour="jeu";	$this->jour_complet="jeudi";		break;	
      case 5 : $this->jour="ven";	$this->jour_complet="vendredi";		break;
      case 6 : $this->jour="sam";	$this->jour_complet="samedi";	$this->sam="samedi";			break;
      case 0 : $this->jour="dim";	$this->jour_complet="dimanche";	$this->sam="dimanche";	$position=7;	break;
    }
    
    $j1=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+1-$position, $yyyy));
    $j2=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+2-$position, $yyyy));
    $j3=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+3-$position, $yyyy));
    $j4=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+4-$position, $yyyy));
    $j5=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+5-$position, $yyyy));
    $j6=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+6-$position, $yyyy));
    $j7=date("Y-m-d", mktime(0, 0, 0, $mm, $dd+7-$position, $yyyy));
    
    $this->dates=array($j1,$j2,$j3,$j4,$j5,$j6,$j7);


    // Calcul du numéro de la semaine pour l'utilisation d'un seul planning hebdomadaire : toujours 1
    if($GLOBALS['config']['nb_semaine']==1){
      $this->semaine3=1;
    }
    // Calcul du numéro de la semaine pour l'utilisation de 2 plannings hebdomadaires
    if($GLOBALS['config']['nb_semaine']==2){
      $this->semaine3=$this->semaine%2?1:2;
    }
    // Calcul du numéro de la semaine pour l'utilisation de 3 plannings hebdomadaires
    if($GLOBALS['config']['nb_semaine']==3){
      $position=date("w", strtotime($GLOBALS['config']['dateDebutPlHebdo']))-1;
      $position=$position==-1?6:$position;
      $dateFrom=new dateTime($GLOBALS['config']['dateDebutPlHebdo']);
      $dateFrom->sub(new DateInterval("P{$position}D"));

      $position=date("w", strtotime($date))-1;
      $position=$position==-1?6:$position;
      $dateNow=new dateTime($date);
      $dateNow->sub(new DateInterval("P{$position}D"));

      $interval=$dateNow->diff($dateFrom);
      $interval=$interval->format("%a");
      $interval=$interval/7;
      if(!($interval%3)){
	$this->semaine3=1;
      }
      if(!(($interval+2)%3)){
	$this->semaine3=2;
      }
      if(!(($interval+1)%3)){
	$this->semaine3=3;
      }
    }
  }
}

function absents($date,$tables){
  $tables=explode(",",$tables);
  $liste="";
  $tab=array();
  foreach($tables as $table){
    $etat=($table=="conges" ? "and etat='Accepté'" : "");
    $db=new db();
    $db->query("select perso_id from $table where debut<='$date' and fin >='$date' $etat;");
    if(is_array($db->result))
    foreach($db->result as $elem){
      $tab[]=$elem['perso_id'];
    }
  }
  $liste=join($tab,",");
  
  if(!$liste){
    $liste=0;
  }
  
  return $liste;
}

function authSQL($login,$password){
  $auth=false;
  $db=new db();
  $db->select("personnel","id,nom,prenom","login='$login' AND password=MD5('$password') AND actif NOT LIKE 'Supprime';");
  if($db->nb==1 and $login!=null){
    $auth=true;
    $_SESSION['oups']['Auth-Mode']="SQL";
  }
  return $auth;
}

function cmp_0($a,$b){
  $a[0] > $b[0];
}

function cmp_0desc($a,$b){
  return $a[0] < $b[0];
}

function cmp_01($a,$b){
  return $a[0][1] > $b[0][1];
}

function cmp_02($a,$b){
  return $a[0][2] > $b[0][2];
}

function cmp_03($a,$b){
  return $a[0][3] > $b[0][3];
}

function cmp_03desc($a,$b){
  return $a[0][3] < $b[0][3];
}

function cmp_1($a,$b){
  return $a[1] > $b[1];
}

function cmp_1desc($a,$b){
  return $a[1] < $b[1];
}

function cmp_2($a,$b){
  return $a[2] > $b[2];
}

function cmp_2desc($a,$b){
  return $a[2] < $b[2];
}

function cmp_heure($a,$b){
  return $a['heure'] > $b['heure'];
}

function cmp_jour($a,$b){
  return $a['jour'] > $b['jour'];
}

function cmp_nom($a,$b){
  return $a['nom'] > $b['nom'];
}

function cmp_semaine($a,$b){
  return $a['semaine'] > $b['semaine'];
}
	
function cmp_semainedesc($a,$b){
  $a['semaine'] < $b['semaine'];
}

function compte_jours($date1, $date2, $jours){
  $current = $date1;
  $datetime2 = date_create($date2);
  $count = 0;
  while(date_create($current) <= $datetime2){
    $count++;
    $tab=explode("-",$current);
    if(in_array("{$tab[2]}/{$tab[1]}",$GLOBALS['config']['joursFeries']) and ($jours=="ouvrés" or $jours=="ouvrables")){
      $count--;
    }
    elseif(date("w", mktime(0, 0, 0, $tab[1], $tab[2], $tab[0]))==0 and ($jours=="ouvrés" or $jours=="ouvrables")){
      $count--;
    }
    elseif(date("w", mktime(0, 0, 0, $tab[1], $tab[2], $tab[0]))==6 and $jours=="ouvrés"){
      $count--;
    }    
    $current=date("Y-m-d", mktime(0, 0, 0, $tab[1], $tab[2]+1, $tab[0]));
  }
  return $count;
}

function date_time($date){
  if($date=="0000-00-00 00:00:00")
    return null;
  else{
    $a=substr($date,0,4);
    $m=substr($date,5,2);
    $j=substr($date,8,2);
    $h=substr($date,11,2);
    $min=substr($date,14,2);
    $today=date("d/m/Y");
    if($today=="$j/$m/$a")
      $date="$h:$min";
    else
      $date="$j/$m/$a $h:$min";
    return $date;
  }
}

function dateAlpha($date){
  $tmp=explode("-",$date);
  $dayOfMonth=($tmp[2]=="01")?"1er":intval($tmp[2]);
  switch($tmp[1]){
    case "01" : $month="janvier" ; break;
    case "02" : $month="février" ; break;
    case "03" : $month="mars" ; break;
    case "04" : $month="avril" ; break;
    case "05" : $month="mai" ; break;
    case "06" : $month="juin" ; break;
    case "07" : $month="juillet" ; break;
    case "08" : $month="août" ; break;
    case "09" : $month="septembre" ; break;
    case "10" : $month="octobre" ; break;
    case "11" : $month="novembre" ; break;
    case "12" : $month="décembre" ; break;
  }
  $day=date("w", mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]));
  switch($day){
    case 1 : $day="Lundi"; break;
    case 2 : $day="Mardi"; break;
    case 3 : $day="Mercredi"; break;
    case 4 : $day="Jeudi"; break;
    case 5 : $day="Vendredi"; break;
    case 6 : $day="Samedi"; break;
    case 0 : $day="Dimanche"; break;
  }
  return $day." ".$dayOfMonth." ".$month." ".$tmp[0];
}

function dateAlpha2($date){
  $tmp=explode("-",$date);
  $dayOfMonth=($tmp[2]=="01")?"1er":intval($tmp[2]);
  switch($tmp[1]){
    case "01" : $month="janvier" ; break;
    case "02" : $month="février" ; break;
    case "03" : $month="mars" ; break;
    case "04" : $month="avril" ; break;
    case "05" : $month="mai" ; break;
    case "06" : $month="juin" ; break;
    case "07" : $month="juillet" ; break;
    case "08" : $month="août" ; break;
    case "09" : $month="septembre" ; break;
    case "10" : $month="octobre" ; break;
    case "11" : $month="novembre" ; break;
    case "12" : $month="décembre" ; break;
  }
  $day=date("w", mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]));
  switch($day){
    case 1 : $day="Lundi"; break;
    case 2 : $day="Mardi"; break;
    case 3 : $day="Mercredi"; break;
    case 4 : $day="Jeudi"; break;
    case 5 : $day="Vendredi"; break;
    case 6 : $day="Samedi"; break;
    case 0 : $day="Dimanche"; break;
  }
  return $day."<br/>".$dayOfMonth." ".$month;
}

function dateFr($date,$heure=null){
  if($date=="0000-00-00" or $date=="00/00/0000" or $date=="" or !$date)
    return null;
  if(substr($date,4,1)=="-"){
    $dateFr=substr($date,8,2)."/".substr($date,5,2)."/".substr($date,0,4);
    if($heure and substr($date,13,1)==":" and substr($date,11,8)!="00:00:00" and substr($date,11,8)!="23:59:59"){
      $dateFr.=" ".substr($date,11,2)."h".substr($date,14,2);
    }
    return $dateFr;
  }
  else{
    $dateEn=substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    return $dateEn;
  }
}

function dateFr2($date){
  if($date=="0000-00-00" or $date=="00/00/0000" or $date=="" or !$date)
    return null;
  if(substr($date,4,1)=="-"){
    $j=substr($date,8,2);
    $m=substr($date,5,2);
    $a=substr($date,0,4);
    switch($m){
      case "01" : $m=" janvier "; break;
      case "02" : $m=" fevrier "; break;
      case "03" : $m=" mars "; break;
      case "04" : $m=" avril "; break;
      case "05" : $m=" mai "; break;
      case "06" : $m=" juin "; break;
      case "07" : $m=" juillet "; break;
      case "08" : $m=" août "; break;
      case "09" : $m=" septembre "; break;
      case "10" : $m=" octobre "; break;
      case "11" : $m=" novembre "; break;
      case "12" : $m=" décembre "; break;
    }
    return $j.$m.$a;
  }
  else{
    $dateEn=substr($date,6,4)."-".substr($date,3,2)."-".substr($date,0,2);
    return $dateEn;
  }
}

function dateTimeFr($date){
  $tmp=explode(" ",$date);
  $date=dateFr($tmp[0]);
  $time=substr($tmp[1],0,5);
  return $date." ".$time;
}

function decode($n){
  if(is_array($n)){
    return array_map("decode",$n);
  }
  return utf8_decode($n);
}

function decrypt($str){  
  $key="AB0972FA445DDE66178ADF76";
  $str = mcrypt_decrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);

  $block = mcrypt_get_block_size('des', 'ecb');
  $pad = ord($str[($len = strlen($str)) - 1]);
  return substr($str, 0, strlen($str) - $pad);
}

function encrypt($str){
  $key="AB0972FA445DDE66178ADF76";
  $block = mcrypt_get_block_size('tripledes', 'ecb');
  $pad = $block - (strlen($str) % $block);
  $str .= str_repeat(chr($pad), $pad);

  return mcrypt_encrypt(MCRYPT_3DES, $key, $str, MCRYPT_MODE_ECB);
}

function gen_trivial_password($len = 6){
  $r = '';
  for($i=0; $i<$len; $i++){
      $r .= chr(rand(0, 25) + ord('a'));
  }
  return $r;
}

function heure($heure){
  $heure=str_replace("h",":",$heure);
  $heure=$heure.":00";
  return $heure;
}
	
function heure2($heure){
  $heure=explode(":",$heure);
  if(!array_key_exists(1,$heure))
    return false;

  $h=$heure[0];
  $m=$heure[1];
  $heure=$h."h".$m;
  return $heure;
}

function heure3($heure){
  $heure=str_replace(":","h",$heure);
  $heure=substr($heure,0,5);
  if(substr($heure,3,2)=="00")
    $heure=substr($heure,0,3);
  if(substr($heure,0,1)=="0")
    $heure=substr($heure,1,strlen($heure));
  return $heure;
}

function heure4($heure){
  if(stripos($heure,"h")){
    $heure=str_replace(array("h00","h15","h30","h45"),array(".00",".25",".50",".75"),$heure);
  }
  else{
    $heure=number_format($heure, 2, '.', ' ');
    $heure=str_replace(array(".00",".25",".50",".75"),array("h00","h15","h30","h45"),$heure);
  }
  return $heure;
}

function HrToMin($heure){
  if(!$heure)
    $minutes=0;
  else{
    $heure=explode(":",$heure);
    $h=intval($heure[0]);
    $m=intval($heure[1]);
    $minutes=($h*60)+$m;
  }
  return $minutes;
}

function is_serialized($string){
  if(is_array(@unserialize($string))){
    return true;
  }
  return false;
}

function mail2($To,$Sujet,$Message){
  require_once("phpmailer/class.phpmailer.php");
  $mail = new PHPMailer();
  if($GLOBALS['config']['Mail-IsMail-IsSMTP']=="IsMail")
    $mail->IsMail();
  else
    $mail->IsSMTP();
  $mail->CharSet="utf-8";
  $mail->WordWrap =$GLOBALS['config']['Mail-WordWrap'];
  $mail->Hostname =$GLOBALS['config']['Mail-Hostname'];
  $mail->Host =$GLOBALS['config']['Mail-Host'];
  $mail->Port =$GLOBALS['config']['Mail-Port'];
  $mail->SMTPSecure = $GLOBALS['config']['Mail-SMTPSecure'];
  $mail->SMTPAuth =$GLOBALS['config']['Mail-SMTPAuth'];
  $mail->Username =$GLOBALS['config']['Mail-Username'];
  $mail->Password =decrypt($GLOBALS['config']['Mail-Password']);
  $mail->From =$GLOBALS['config']['Mail-From'];
  $mail->FromName =$GLOBALS['config']['Mail-FromName'];

  $mail->IsHTML();
  $mail->Body = $Message;
  if(is_array($To)){
    foreach($To as $elem){
      $mail->AddAddress($elem);
    }
  }
  else{
    $mail->AddAddress($To);
  }

  $mail->Subject = $Sujet;
  $mail->Send();
}

function MinToHr($minutes){
  if($minutes!=0){
    $heure=$minutes/60;
    $h=intval($heure);
    if(strlen($h)==1){
      $h="0".$h;
    }
    $m=$heure-$h;
    $m=$m*60;
    if(strlen($m)==1){
      $m="0".$m;
    }
    $heure=$h.":".$m.":00";
  }
  else{
    $heure="00:00:00";
  }
  return $heure;
}

function nom($id,$format="nom p"){
  $db=new db();
  $db->query("select nom,prenom from {$GLOBALS['config']['dbprefix']}personnel where id=$id;");
  $nom=$db->result[0]['nom'];
  $prenom=$db->result[0]['prenom'];
  switch($format){
    case "nom prenom": $nom="$nom $prenom";	break;
    case "prenom nom": $nom="$prenom $nom";	break;
    default : $nom="$nom ".substr($prenom,0,1);	break;
  }
  return $nom;
}
	
function php2js( $php_array, $js_array_name ){
  // contrôle des parametres d'entrée
  if( !is_array( $php_array ) ) {
    trigger_error( "php2js() => 'array' attendu en parametre 1, '".gettype($array)."' fourni !?!");
    return false;
  }
  if( !is_string( $js_array_name ) ) {
    trigger_error( "php2js() => 'string' attendu en parametre 2, '".gettype($array)."' fourni !?!");
    return false;
  }

  // Création du tableau en JS
  $script_js = "var $js_array_name = new Array();\n";
  // on rempli le tableau JS à partir des valeurs de son homologue PHP
  foreach( $php_array as $key => $value ) {
    // pouf, on tombe sur une dimension supplementaire
    if( is_array($value) ) {
      // On va demander la création d'un tableau JS temporaire
      $temp = uniqid('temp_'); // on lui choisi un nom bien barbare
      $t = php2js( $value, $temp ); // et on creer le script JS
      // En cas d'erreur, remonter l'info aux récursions supérieures
      if( $t===false ) return false;

      // Ajout du script de création du tableau JS temporaire
      $script_js.= $t;
      // puis on applique ce tableau temporaire à celui en cours de construction
      $script_js.= "{$js_array_name}['{$key}'] = {$temp};\n";
    }
    // Si la clef est un entier, pas de guillemets
    elseif( is_int($key) ) $script_js.= "{$js_array_name}[{$key}] = '{$value}';\n";
    // sinon avec les guillemets
    else $script_js.= "{$js_array_name}['{$key}'] = '{$value}';\n";
  }
  // Et retourn le script JS
  return $script_js;
} 

function pl_stristr($haystack,$needle){
  if(stristr(removeAccents($haystack),removeAccents(trim($needle))))
    return true;
  return false;
}

function removeAccents($string){
  if(is_array($string)){
    return array_map("removeAccents",$string);
  }
  $string=html_entity_decode($string,ENT_QUOTES|ENT_IGNORE,"UTF-8");
  $pairs=array("À"=>"A","Á"=>"A","Â"=>"A","Ã"=>"A","Ä"=>"A","Å"=>"A","à"=>"a","á"=>"a","â"=>"a",
    "ã"=>"a","ä"=>"a","å"=>"a","Ò"=>"O","Ó"=>"O","Ô"=>"O","Õ"=>"O","Õ"=>"O","Ö"=>"O","Ø"=>"O",
    "ò"=>"o","ó"=>"o","ô"=>"o","õ"=>"o","ö"=>"o","ø"=>"o","È"=>"E","É"=>"E","Ê"=>"E","Ë"=>"E",
    "è"=>"e","é"=>"e","ê"=>"e","ë"=>"e","ð"=>"e","Ç"=>"C","ç"=>"c","Ð"=>"d","Ì"=>"I","Í"=>"I",
    "Î"=>"I","Ï"=>"I","ì"=>"i","í"=>"i","î"=>"i","ï"=>"i","Ù"=>"U","Ú"=>"U","Û"=>"U","Ü"=>"U",
    "ù"=>"u","ú"=>"u","û"=>"u","ü"=>"u","Ñ"=>"N","ñ"=>"n","ÿ"=>"y","ý"=>"y","ŷ"=>"y","ỳ"=>"y",
    "Ÿ"=>"Y","Ỳ"=>"Y","Ŷ"=>"Y");
  $string=strtr($string,$pairs);
  return $string;
}
	
function selectHeure($min,$max,$blank=false,$quart=false,$selectedValue=null){
  if($blank)
    echo "<option value=''>&nbsp;</option>\n";
  for($i=$min;$i<$max+1;$i++){
    if($i<10)
      $i="0".$i;

    $selected=$selectedValue==$i.":00:00"?"selected='selected'":null;
    echo "<option value='".$i.":00:00' $selected>".$i."h00</option>\n";
    if($quart){
      $selected=$selectedValue==$i.":15:00"?"selected='selected'":null;
      echo "<option value='".$i.":15:00' $selected>".$i."h15</option>\n";
    }
    $selected=$selectedValue==$i.":30:00"?"selected='selected'":null;
    echo "<option value='".$i.":30:00' $selected>".$i."h30</option>\n";
    if($quart){
      $selected=$selectedValue==$i.":45:00"?"selected='selected'":null;
      echo "<option value='".$i.":45:00' $selected>".$i."h45</option>\n";
    }
  }
}

function selectTemps($jour,$i,$periodes=null,$class=null){
  $temps=null;
  $select1=null;
  $select2=null;
  $select3=null;
  $select4=null;
  $class=$class?"class='$class'":null;
  if(array_key_exists("temps",$GLOBALS)){
    $temps=$GLOBALS['temps'];
  }
  if($periodes){
    $select="<select name='temps{$periodes}[$jour][$i]' $class>\n";
  }
  else{
    $select="<select name='temps[$jour][$i]' $class>\n";
  }
  $select.="<option value=''>&nbsp;</option>\n";
  for($j=8;$j<23;$j++){
    $z=$j<10?"0":"";
    if($temps){
      $select1=$temps[$jour][$i]==$z.$j.":00:00"?"selected='selected'":"";
      $select2=$temps[$jour][$i]==$z.$j.":15:00"?"selected='selected'":"";
      $select3=$temps[$jour][$i]==$z.$j.":30:00"?"selected='selected'":"";
      $select4=$temps[$jour][$i]==$z.$j.":45:00"?"selected='selected'":"";
    }
    $select.="<option value='$z$j:00:00' $select1 >".$z.$j."h00</option>\n";
    $select.="<option value='$z$j:15:00' $select2 >".$z.$j."h15</option>\n";
    $select.="<option value='$z$j:30:00' $select3 >".$z.$j."h30</option>\n";
    $select.="<option value='$z$j:45:00' $select4 >".$z.$j."h45</option>\n";
  }
  $select.="</select>\n";
  return $select;
}

function sendmail($Sujet,$Message,$destinataires,$alert=true){
  if(!$GLOBALS['config']['Mail-IsEnabled'])
    return false;
  $destinataires=explode(";",$destinataires);
  if($destinataires[0]){
    $Entete="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
    $Entete.="<html><head><title>Planning</title></head><body>";
    $Message=$Entete.$Message;
    $Message.="<br/><br/>{$GLOBALS['config']['Mail-Signature']}<br/><br/>";
    $Message.="</body></html>";

    $Sujet = stripslashes($Sujet);
    $Sujet = "Planning : $Sujet";
    $Message = stripslashes($Message);
    $Message= eregi_replace("\n|\r\n\n|\r\n", "<br/>", $Message) ;
    $to=array();
    foreach($destinataires as $destinataire){
      if(verifmail($destinataire)){
	$to[]=$destinataire;
// 	mail2($destinataire,$Sujet,$Message);
      }
      elseif($alert){
	echo "<script type='text/JavaScript'>alert('Adresse mail invalide (\"$destinataire\")');</script>";
      }
    }
    if(!empty($to)){
      mail2($to,$Sujet,$Message);
    }
  }
}

function soustrait_tab($tab1,$tab2){
  $tab=array();
  foreach($tab1 as $elem1){
    $exist=false;
    foreach($tab2 as $elem2)
      if($elem1==$elem2)
	$exist=true;
    if(!$exist)
      $tab[]=$elem1;
  }
  return $tab;
}

function tabAjoutLigne($tableau,$ligne,$contenu){
	 // REMPLISSAGE PREMIER TABLEAU TEMP1
  $temp1=array();
  $temp2=array();
  
  $limit = $ligne + 1;
  for($i=0;$i<$limit;$i++)
    $temp1[] = $tableau[$i];

  // REMPLISSAGE SECOND TABLEAU TEMP2
  for($i=$limit;$i<count($tableau);$i++)
    $temp2[] = $tableau[$i];

  //DESTRUCTION DU TABLEAU D'ORIGINE
  unset($tableau);

  // RECREATION DU TABLEAU D'ORIGINE AVEC LES VALEURS DE TEMP1
  for($i=0;$i<count($temp1);$i++)
    $tableau[] = $temp1[$i];

  //ajout d'une ligne vide
  $tableau[]= $contenu;
  // RECREATION DU TABLEAU D'ORIGINE AVEC LES VALEURS DE TEMP2
  for($i=0;$i<count($temp2);$i++)
    $tableau[] = $temp2[$i];
  
  return $tableau;
}

function tableau($liste){
  $tab=explode(",",$liste);
  $tableau=array();

  foreach($tab as $elem){
    $tab2=explode("=",$elem);
    $tab3=array("heure" => $tab2[0],"nom" => $tab2[1]);
    array_push($tableau,$tab3);
  }
  
  usort($tableau, "cmp_heure");
  
  for($i=0;$i<10;$i++){
    if($tableau[$i]['heure']==""){
      $tableau[$i]['heure']="&nbsp;";
    }
    if($tableau[$i]['nom']==""){
      $tableau[$i]['nom']="&nbsp;";
    }
  }
  return $tableau;
}

function tri($tab){
  sort($tab);
  for($i=0;$i<21;$i++){
    if($tab[$i]=="")
	    $tab[$i]="&nbsp;";
  }
  return $tab;
}
	
function verifmail($texte){
  $resultats = ereg("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $texte, $poubelle);
  return $resultats;
}
?>