<?php
/*
Planning Biblio, Version 1.8.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : include/db.php
Création : mai 2011
Dernière modification : 11 septembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe db permet d'effectuer des opérations sur la base de données MySQL : 
INSERT, UPDATE, DELETE et autres requetes avec la fonction db::query($requete);

Page appelée par le fichier include/config.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

include_once "function.php";

class db{
  var $host;
  var $dbname;
  var $user;
  var $password;
  var $conn;
  var $result;
  var $nb;
  var $error;
  
  function db(){
    $this->host=$GLOBALS['config']['dbhost'];
    $this->dbname=$GLOBALS['config']['dbname'];
    $this->user=$GLOBALS['config']['dbuser'];
    $this->password=$GLOBALS['config']['dbpass'];
  }

  function connect(){
    $this->conn=mysqli_connect($this->host,$this->user,$this->password,$this->dbname);
    if(mysqli_connect_errno($this->conn)){
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
  }
  
  function query($requete){
    $this->connect();
    $req=mysqli_query($this->conn,$requete);

    if(!$req){
      echo "<br/><br/>### ERREUR SQL ###<br/><br/>$requete<br/><br/>";
      echo mysqli_error($this->conn);
      echo "<br/><br/>";
      $this->error=true;
    }
    elseif(strtolower(substr(trim($requete),0,6))=="select" or strtolower(substr(trim($requete),0,4))=="show"){
      $this->nb=mysqli_num_rows($req);
      for($i=0;$i<$this->nb;$i++)
	$this->result[]=mysqli_fetch_array($req);
    }
    $this->disconnect();
  }

  function disconnect(){
    mysqli_close($this->conn);
  }

  function select($table,$infos=null,$where=null,$options=null){
    $infos=$infos?$infos:"*";
    $where=$where?$where:"1";
    $requete="SELECT $infos FROM `{$GLOBALS['config']['dbprefix']}$table` WHERE $where $options";
    $this->query($requete);
  }

  function update($table,$set,$where=1){
    $requete="UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET $set WHERE $where";
    $this->query($requete);
  }

  function update2($table,$set,$where="1"){
    $this->connect();
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
      $key=array_keys($where);
      $where="`".$key[0]."`='".$where[$key[0]]."'";
    }
    $requete="UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET $set WHERE $where;";

    $req=mysqli_query($this->conn,$requete);
    $this->disconnect();
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
    $requete="UPDATE `{$GLOBALS['config']['dbprefix']}$table` SET $set WHERE $where;";
    $req=mysqli_query($this->conn,$requete);
    $this->disconnect();
  }

  function delete($table,$where=1){
    $requete="DELETE FROM `{$GLOBALS['config']['dbprefix']}$table` WHERE $where";
    $this->query($requete);
  }

  function delete2($table,$where=array()){
    if(!empty($where)){
      $keys=array_keys($where);
      $tmp=array();
      foreach($keys as $key){
	$tmp[]="`".$key."`='".$where[$key]."'";
      }
      $where=join(" AND ",$tmp);
    }else{
      $where=1;
    }
    $requete="DELETE FROM `{$GLOBALS['config']['dbprefix']}$table` WHERE $where";
    $this->query($requete);
  }

  function insert($table,$values,$fields=null){
    $this->connect();
    $fields=$fields?"($fields)":null;
    if(is_array($values)){
      $values=join("),(",$values);
    }
    $requete="INSERT INTO `{$GLOBALS['config']['dbprefix']}$table` $fields VALUES ($values);";
    $req=mysqli_query($this->conn,$requete);
    if(mysqli_error($this->conn)){
      echo mysqli_error($this->conn);
      echo "<br/>".$requete;
    }
    $this->disconnect();
  }

  function insert2($table,$values,$options=null){
    $this->connect();
    $tab=array();
    if(array_key_exists(0,$values)){
      $fields=array_keys($values[0]);
      for($i=0;$i<count($values);$i++){
	    foreach($fields as $elem){
	      if(!is_serialized($values[$i][$elem]))
		$values[$i][$elem]=htmlentities($values[$i][$elem],ENT_QUOTES | ENT_IGNORE,"UTF-8",false);
	      $values[$i][$elem]=mysqli_real_escape_string($this->conn,$values[$i][$elem]);
	    }
      }
      $fields=join(",",$fields);

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
      $fields=join(",",$fields);
      $tab[]="'".join("','",$values)."'";
    }

    $this->insert($table,$tab,$fields);
  }

}

class dbh{
  var $dbhost;
  var $dbname;
  var $dbuser;
  var $dbpass;
  var $pdo;
  var $stmt;
  var $result;


  function dbh(){
    $this->dbhost=$GLOBALS['config']['dbhost'];
    $this->dbname=$GLOBALS['config']['dbname'];
    $this->dbuser=$GLOBALS['config']['dbuser'];
    $this->dbpass=$GLOBALS['config']['dbpass'];

    $this->pdo=new PDO("mysql:host={$this->dbhost};dbname={$this->dbname}",$this->dbuser,$this->dbpass);
  }

  function exec($sql){
    $this->pdo->exec($sql);
  }

  function prepare($sql){
    $this->stmt=$this->pdo->prepare($sql);
  }

  function execute($data){
    $this->stmt->execute($data);
    $this->result=$this->stmt->fetchAll();
  }
}

?>