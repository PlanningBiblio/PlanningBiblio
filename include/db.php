<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/db.php
Création : mai 2011
Dernière modification : 10 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Classe db permet d'effectuer des opérations sur la base de données MySQL : 
INSERT, UPDATE, DELETE et autres requetes avec la fonction db::query($requete);

Page appelée par le fichier include/config.php
*/

require_once "function.php";
require_once "sanitize.php";

// pas de $version=acces direct au fichier => Accès refusé
if(!isset($version)){
  include_once "accessDenied.php";
}

class db{
  var $host;
  var $dbname;
  var $dbprefix;
  var $user;
  var $password;
  var $conn;
  var $result;
  var $nb;
  var $error;
  var $msg;
  
  function db(){
    $this->host=$GLOBALS['config']['dbhost'];
    $this->dbname=$GLOBALS['config']['dbname'];
    $this->user=$GLOBALS['config']['dbuser'];
    $this->password=$GLOBALS['config']['dbpass'];
    $this->error=false;
    $this->conn=null;
    $this->dbprefix=$GLOBALS['config']['dbprefix'];
  }

  function connect(){
    $this->conn=mysqli_connect($this->host,$this->user,$this->password,$this->dbname);
    if(mysqli_connect_errno($this->conn)){
      $this->error=true;
      $this->msg=mysqli_connect_error();
    }
  }
  
  /**
    * fonction de protection des caracteres speciaux
    * @param string $str string à échapper
    * @return string
    * @access public
    */
  public function escapeString($str){
    $this->connect();
    $str=mysqli_real_escape_string($this->conn,$str);
    return $str;
  }

  function query($requete){
    if(!$this->conn){
      $this->connect();
    }

    $req=mysqli_query($this->conn,$requete);

    if(!$req){
      $this->error=true;
      $this->error=mysqli_error($this->conn);
    }
    elseif(strtolower(substr(trim($requete),0,6))=="select" or strtolower(substr(trim($requete),0,4))=="show"){
      $this->nb=mysqli_num_rows($req);
      for($i=0;$i<$this->nb;$i++){
	$result=array();
	$tab=mysqli_fetch_assoc($req);
	foreach($tab as $key => $value){
	  if(isset($isCryptedPassword) and $isCryptedPassword===true){
	    $result[$key]=filter_var($value,FILTER_UNSAFE_RAW);
	 }elseif(is_serialized($value)){
	    $result[$key]=filter_var($value,FILTER_UNSAFE_RAW);
	  }else{
	    $result[$key]=filter_var($value,FILTER_SANITIZE_STRING);
	  }
	  $isCryptedPassword=($key=="type" and $value=="password")?true:false;
	}
	$this->result[]=$result;
      }
    }
    $this->disconnect();
  }

  function disconnect(){
    mysqli_close($this->conn);
  }

  function select($table,$infos=null,$where=null,$options=null){
    $infos=$infos?$infos:"*";
    $where=$where?$where:"1";
    $requete="SELECT $infos FROM `{$this->dbprefix}$table` WHERE $where $options";
    $this->query($requete);
  }

  /**
  Fonction permettant de rechercher des infos dans la base de données en utilisant MySQLi
  @param string $table : nom de la table à interroger
  @param string / array infos : valeurs qu'on souhaite récupérer. 
    Si string, nom des champs séparés par des virgules
    Si array : array(champ1, champ2, ...)
    Si array : array(array(name=> name, as => as), ...)
  @param string / array where : filtre de recherche. 
    Si string : champ1=valeur1 AND champ2=valeur2 ...,
    Si array : array(champ1=>valeur1, champ2=>valeur2, ...)
  @param string option : permet d'ajouter des options de recherche après where, ex : order by 
  */
  function select2($table,$infos="*",$where="1",$options=null){
    $this->connect();
    $dbprefix=$this->dbprefix;

    if($infos===null){
      $infos="*";
    }
    if(is_array($infos)){
      $tmp=array();
      foreach($infos as $elem){
	if(is_array($elem)){
	  $tmp[]="{$elem['name']} AS `{$elem['as']}`";
	}else{
	  $tmp[]=$elem;
	}
      }
      $infos=join(",",$tmp);
    }

    // Filtre Where
    // Par défaut, recherche tout
    if($where===null){
      $where="1";
    }
    // Si tableau, pour chaque entrée ...
    if(is_array($where)){
      $tmp=array();
      foreach($where as $key => $value){
	$tmp[]=$this->makeSearch($key,$value);
      }
      $where=join(" AND ",$tmp);
    }

    $requete="SELECT $infos FROM `{$dbprefix}$table` WHERE $where $options";
    $this->query($requete);
  }


  /**
  Fonction permettant de rechercher des infos dans la base de données en utilisant une jointure avec MySQLi
  @param array table1 : tableau contenant le nom de la première table et son index à utiliser pour la jointure
  @param array table2 : tableau contenant le nom de la seconde table et son index à utiliser pour la jointure
  @param array table1Fields : champs de la première table à afficher.
    Les valeurs peuvent être des chaînes de caractères (nom des champs)
    Ou des tableaux ayant pour index "name" => le nom du champ et "as" => l'alias voulu
  @param array table2Fields : champs de la seconde table à afficher.
    Les valeurs peuvent être des chaînes de caractères (nom des champs)
    Ou des tableaux ayant pour index "name" => le nom du champ et "as" => l'alias voulu
  @param array table1Where : Filtre à appliquer sur la première table
  @param array table2Where : Filtre à appliquer sur la première table
  @param string options : permet d'ajouter des options de recherche après where, ex : order by 
  */
  public function selectInnerJoin($table1=array(), $table2=array(), $table1Fields=array(), 
    $table2Fields=array(), $table1Where=array(), $table2Where=array(), $options=null){

    if(empty($table1) or empty($table2)){
      $this->error=true;
      return false;
    }

    // Connection à la base de données
    $this->connect();

    // Initilisation des variables
    $table1Name="{$this->dbprefix}".$table1[0];
    $table2Name="{$this->dbprefix}".$table2[0];
    $table1Index=$table1[1];
    $table2Index=$table2[1];

    // Construction de la requête
    // Valeurs à retourner
    $info=array();
    foreach($table1Fields as $elem){
      if(is_string($elem)){
	$info[]="`$table1Name`.`$elem` AS `$elem`";
      }elseif(is_array($elem)){
	$info[]="`$table1Name`.`{$elem['name']}` AS `{$elem['as']}`";
      }
    }
    foreach($table2Fields as $elem){
      if(is_string($elem)){
	$info[]="`$table2Name`.`$elem` AS `$elem`";
      }elseif(is_array($elem)){
	$info[]="`$table2Name`.`{$elem['name']}` AS `{$elem['as']}`";
      }
    }
    $info=join(", ",$info);

    // Construction de la requête
    // Filtre "Where" et options
    $where=array();
    foreach($table1Where as $key => $value){
      $key="`$table1Name`.`$key`";
      $where[]=$this->makeSearch($key,$value);
    }
    foreach($table2Where as $key => $value){
      $key="`$table2Name`.`$key`";
      $where[]=$this->makeSearch($key,$value);
    }
    $where=join(" AND ",$where);
  
    // Construction de la requête
    // Assemblage
    $query="SELECT $info FROM `$table1Name` INNER JOIN `$table2Name` ON `$table1Name`.`$table1Index`=`$table2Name`.`$table2Index` ";
    $query.="WHERE $where $options";

    // Execution de la requête
    $this->query($query);
  }

  function update($table,$set,$where=1){
    $requete="UPDATE `{$this->dbprefix}$table` SET $set WHERE $where";
    $this->query($requete);
  }

  function update2($table,$set,$where="1"){
    $this->connect();
    $dbprefix=$this->dbprefix;

    $tmp=array();
    $fields=array_keys($set);
    foreach($fields as $field){
      if(!is_serialized($set[$field]))
	$set[$field]=htmlentities($set[$field],ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
      $set[$field]=mysqli_real_escape_string($this->conn,$set[$field]);
      if(substr($set[$field],0,7)=="CONCAT("){
	$tmp[]="`{$field}`={$set[$field]}";
      }
      else{
	$tmp[]="`{$field}`='{$set[$field]}'";
      }
    }
    $set=join(",",$tmp);
    if(is_array($where)){
      $tmp=array();
      foreach($where as $key => $value){
	$escapedValue=htmlentities($value,ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
	$escapedValue=mysqli_real_escape_string($this->conn,$escapedValue);
	$tmp[]="`$key`='$escapedValue'";
      }
      $where=join(" AND ",$tmp);
    }
    $requete="UPDATE `{$dbprefix}$table` SET $set WHERE $where;";
    $this->query($requete);
  }

  function update2latin1($table,$set,$where){
    $this->connect();
    $tmp=array();
    $fields=array_keys($set);
    foreach($fields as $field){
      if(!is_serialized($set[$field]))
	$set[$field]=htmlentities($set[$field],ENT_QUOTES | ENT_IGNORE,"ISO-8859-1",false);
      $set[$field]=mysqli_real_escape_string($this->conn,$set[$field]);
      $tmp[]="`{$field}`='{$set[$field]}'";
    }
    $set=join(",",$tmp);
    $key=array_keys($where);
    $where="`".$key[0]."`='".$where[$key[0]]."'";
    $requete="UPDATE `{$this->dbprefix}$table` SET $set WHERE $where;";
    $this->query($requete);
  }

  function delete($table,$where=1){
    $requete="DELETE FROM `{$this->dbprefix}$table` WHERE $where";
    $this->query($requete);
  }

  function delete2($table,$where="1"){
    $this->connect();
    $dbprefix=$this->dbprefix;

    if(is_array($where)){
      $keys=array_keys($where);
      $tmp=array();
      foreach($keys as $key){
	$value=mysqli_real_escape_string($this->conn,$where[$key]);
	$tmp[]=$this->makeSearch($key,$value);
      }
      $where=join(" AND ",$tmp);
    }

    $requete="DELETE FROM `{$dbprefix}$table` WHERE $where";
    $this->query($requete);
  }

  function insert($table,$values,$fields=null){
    $fields=$fields?"($fields)":null;
    if(is_array($values)){
      $values=join("),(",$values);
    }
    $requete="INSERT INTO `{$this->dbprefix}$table` $fields VALUES ($values);";
    $this->query($requete);
  }

  function insert2($table,$values,$options=null){
    $this->connect();
    $dbprefix=$this->dbprefix;
    $table=$dbprefix.$table;

    $tab=array();
    if(array_key_exists(0,$values)){
      $fields=array_keys($values[0]);
      for($i=0;$i<count($values);$i++){
	foreach($fields as $elem){
	  if(!is_serialized($values[$i][$elem])){
	    $values[$i][$elem]=htmlentities($values[$i][$elem],ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
	    }
	  $values[$i][$elem]=mysqli_real_escape_string($this->conn,$values[$i][$elem]);
	}
      }
      $fields=join("`,`",$fields);

      foreach($values as $elem){
	$tab[]="'".join("','",$elem)."'";
      }
    }
    else{
      $fields=array_keys($values);
      foreach($fields as $elem){
	if(!is_serialized($values[$elem])){
	  $values[$elem]=htmlentities($values[$elem],ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
	}
	$values[$elem]=mysqli_real_escape_string($this->conn,$values[$elem]);
      }
      $fields=join("`,`",$fields);
      $tab[]="'".join("','",$values)."'";
    }

    $values=join("),(",$tab);
    $this->query("INSERT INTO `$table` (`$fields`) VALUES ($values);");
  }


  function makeSearch($key,$value){
    // Trim des valeurs et opérateurs
    $value=trim($value);
    // Par défaut, opérateur =
    $operator="=";

    // BETWEEN
    if(substr($value,0,7)=="BETWEEN"){
      $tmp=trim(substr($value,7));
      $tmp=explode("AND",$tmp);
      $value1=htmlentities(trim($tmp[0]),ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
      $value1=$this->escapeString($value1);
      $value2=htmlentities(trim($tmp[1]),ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
      $value2=$this->escapeString($value2);
      return "{$key} BETWEEN '$value1' AND '$value2'";
    }

    // IN
    elseif(substr($value,0,2)=="IN"){
      $tmp=trim(substr($value,2));
      $tmp=explode(",",$tmp);

      $values=array();
      foreach($tmp as $elem){
	$values[]=$this->escapeString(htmlentities(trim($elem),ENT_QUOTES | ENT_IGNORE,"UTF-8",false));
      }
      $values=join("','",$values);

      return "{$key} IN ('$values')";
    }

    // Opérateurs =, >, <, >=, <=, <>
    elseif(substr($value,0,2)==">="){
      $operator=">=";
      $value=trim(substr($value,2));
    }elseif(substr($value,0,2)=="<="){
      $operator="<=";
      $value=trim(substr($value,2));
    }elseif(substr($value,0,2)=="<>"){
      $operator="<>";
      $value=trim(substr($value,2));
    }elseif(substr($value,0,1)=="="){
      $operator="=";
      $value=trim(substr($value,1));
    }elseif(substr($value,0,1)==">"){
      $operator=">";
      $value=trim(substr($value,1));
    }elseif(substr($value,0,1)=="<"){
      $operator="<";
      $value=trim(substr($value,1));
    }

    $value=htmlentities($value,ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
    $value=$this->escapeString($value);
    return "{$key}{$operator}'$value'";
  }


}

class dbh{
  var $dbhost;
  var $dbname;
  var $dbuser;
  var $dbpass;
  var $dbprefix;
  var $error;
  var $msg;
  var $pdo;
  var $stmt;
  var $result;


  function dbh(){
    $this->dbhost=$GLOBALS['config']['dbhost'];
    $this->dbname=$GLOBALS['config']['dbname'];
    $this->dbuser=$GLOBALS['config']['dbuser'];
    $this->dbpass=$GLOBALS['config']['dbpass'];
    $this->dbprefix=$GLOBALS['config']['dbprefix'];

    $this->pdo=new PDO("mysql:host={$this->dbhost};dbname={$this->dbname}",$this->dbuser,$this->dbpass);
  }

  function exec($sql){
    $this->pdo->exec($sql);
  }

  function prepare($sql){
    $this->stmt=$this->pdo->prepare($sql);
  }

  function execute($data=array()){
    $this->stmt->execute($data);
    $tmp=$this->stmt->fetchAll();
    $this->nb=count($tmp);

    for($i=0;$i<$this->nb;$i++){
      $result=array();
      foreach($tmp[$i] as $key => $value){
	if(is_serialized($value)){
	  $result[$key]=filter_var($value,FILTER_UNSAFE_RAW);
	}else{
	  $result[$key]=filter_var($value,FILTER_SANITIZE_STRING);
	}
      }
      $this->result[]=$result;
    }


    $this->nb=count($this->result);
    $errors=$this->stmt->errorInfo();

    $this->error=$errors[1];
    $this->msg=$errors;
  }

  /**
  Fonction permettant de rechercher des infos dans la base de données en utilisant PDO_MySQL
  @param string $table : nom de la table à interroger
  @param string / array infos : valeurs qu'on souhaite récupérer. 
    Si string, nom des champs séparés par des virgules
    Si array : array(champ1, champ2, ...)
  @param string / array where : filtre de recherche. 
    Si string : champ1=valeur1 AND champ2=valeur2 ..., à éviter car les valeurs ne sont pas échappées dans ce cas
    Si array : array(champ1=>valeur1, champ2=>valeur2, ...) à utiliser de préférence car les valeurs sont échapées par PDO_MySQL
  @param string option : permet d'ajouter des options de recherche après where, ex : order by 
  */
  function select($table,$infos="*",$where="1",$options=null){
    $table=$this->dbprefix.$table;

    if(is_array($infos)){
      $infos=join(",",$infos);
    }

    $data=array();
    if(is_array($where)){
      $fields=array();
      $keys=array_keys($where);
      foreach($keys as $key){
	$data[":$key"]=$where[$key];
	$fields[]="$key=:$key";
      }
      $where=join(" AND ",$fields);
    }

    $requete="SELECT $infos FROM `$table` WHERE $where $options";
    $this->prepare($requete);
    $this->execute($data);
  }
}

?>