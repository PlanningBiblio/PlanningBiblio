<?php
/**
Planning Biblio, Version 2.7.05
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/db.php
Création : mai 2011
Dernière modification : 28 novembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe db permet d'effectuer des opérations sur la base de données MySQL :
INSERT, UPDATE, DELETE et autres requetes avec la fonction db::query($requete);

Page appelée par le fichier include/config.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    echo "Acc&egrave;s refus&eacute;\n";
    exit;
}

require_once "function.php";
require_once "sanitize.php";

class db
{
    public $host = null;
    public $dbname = null;
    public $dbprefix = null;
    public $user = null;
    public $password = null;
    public $conn = null;
    public $result = null;
    public $nb = null;
    public $error = null;
    public $msg = null;
    public $CSRFToken = false;
    public $sanitize_string = true;
      
    public function __construct()
    {
        $this->host=$GLOBALS['config']['dbhost'];
        $this->dbname=$GLOBALS['config']['dbname'];
        $this->user=$GLOBALS['config']['dbuser'];
        $this->password=$GLOBALS['config']['dbpass'];
        $this->error=false;
        $this->conn=null;
        $this->dbprefix=$GLOBALS['config']['dbprefix'];
    }

    public function connect()
    {
        $this->conn=mysqli_connect($this->host, $this->user, $this->password, $this->dbname);
        $this->conn->set_charset("utf8mb4");

        mysqli_query($this->conn, "SET SESSION sql_mode = ''");
        if (mysqli_connect_errno($this->conn)) {
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
    public function escapeString($str)
    {
        if (!$this->conn) {
            $this->connect();
        }
        $str=mysqli_real_escape_string($this->conn, $str);
        return $str;
    }

    public function query($requete)
    {
        if (!$this->conn) {
            $this->connect();
        }

        $req=mysqli_query($this->conn, $requete);

        if (!$req) {
            $this->error=true;
            $this->error=mysqli_error($this->conn);
        } elseif (strtolower(substr(trim($requete), 0, 6))=="select" or strtolower(substr(trim($requete), 0, 4))=="show") {
            $this->nb=mysqli_num_rows($req);
            for ($i=0;$i<$this->nb;$i++) {
                $result=array();
                $tab=mysqli_fetch_assoc($req);
                foreach ($tab as $key => $value) {
                    if (isset($isCryptedPassword) and $isCryptedPassword===true) {
                        $result[$key]=filter_var($value, FILTER_UNSAFE_RAW);
                    } else {
                        $result[$key]=filter_var($value, FILTER_SANITIZE_STRING);
                    }
                    $isCryptedPassword=($key=="type" and $value=="password")?true:false;
                }
                $this->result[]=$result;
            }
        }
        $this->disconnect();
    }

    public function disconnect()
    {
        mysqli_close($this->conn);
    }

    public function select($table, $infos=null, $where=null, $options=null)
    {
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
    public function select2($table, $infos="*", $where="1", $options=null)
    {
        $this->connect();
        $dbprefix=$this->dbprefix;

        if ($infos===null) {
            $infos="*";
        }
        if (is_array($infos)) {
            $tmp=array();
            foreach ($infos as $elem) {
                if (is_array($elem)) {
                    $tmp[]="{$elem['name']} AS `{$elem['as']}`";
                } else {
                    $tmp[]=$elem;
                }
            }
            $infos=join(",", $tmp);
        }

        // Filtre Where
        // Par défaut, recherche tout
        if ($where===null) {
            $where="1";
        }
        // Si tableau, pour chaque entrée ...
        if (is_array($where)) {
            $tmp=array();
            foreach ($where as $key => $value) {
                $tmp[]=$this->makeSearch($key, $value);
            }
            $where=join(" AND ", $tmp);
        }

        $requete="SELECT $infos FROM `{$dbprefix}$table` WHERE $where $options";
        $this->query($requete);
    }


    /**
    Fonction permettant de rechercher des infos dans la base de données en utilisant une jointure INNER JOIN avec MySQLi
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
    public function selectInnerJoin(
        $table1=array(),
        $table2=array(),
        $table1Fields=array(),
        $table2Fields=array(),
        $table1Where=array(),
        $table2Where=array(),
        $options=null
  ) {
        if (empty($table1) or empty($table2)) {
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
        foreach ($table1Fields as $elem) {
            if (is_string($elem)) {
                $info[]="`$table1Name`.`$elem` AS `$elem`";
            } elseif (is_array($elem)) {
                $info[]="`$table1Name`.`{$elem['name']}` AS `{$elem['as']}`";
            }
        }
        foreach ($table2Fields as $elem) {
            if (is_string($elem)) {
                $info[]="`$table2Name`.`$elem` AS `$elem`";
            } elseif (is_array($elem)) {
                $info[]="`$table2Name`.`{$elem['name']}` AS `{$elem['as']}`";
            }
        }
        $info=join(", ", $info);

        // Construction de la requête
        // Filtre "Where" et options
        $where=array();
        foreach ($table1Where as $key => $value) {
            $key="`$table1Name`.`$key`";
            $where[]=$this->makeSearch($key, $value);
        }
        foreach ($table2Where as $key => $value) {
            $key="`$table2Name`.`$key`";
            $where[]=$this->makeSearch($key, $value);
        }
        $where=join(" AND ", $where);
  
        // Construction de la requête
        // Assemblage
        $query="SELECT $info FROM `$table1Name` INNER JOIN `$table2Name` ON `$table1Name`.`$table1Index`=`$table2Name`.`$table2Index` ";
        $query.="WHERE $where $options";

        // Execution de la requête
        $this->query($query);
    }

    /**
    Fonction permettant de rechercher des infos dans la base de données en utilisant une jointure LEFT JOIN avec MySQLi
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
    public function selectLeftJoin(
        $table1=array(),
        $table2=array(),
        $table1Fields=array(),
        $table2Fields=array(),
        $table1Where=array(),
        $table2Where=array(),
        $options=null
  ) {
        if (empty($table1) or empty($table2)) {
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
        foreach ($table1Fields as $elem) {
            if (is_string($elem)) {
                $info[]="`$table1Name`.`$elem` AS `$elem`";
            } elseif (is_array($elem)) {
                $info[]="`$table1Name`.`{$elem['name']}` AS `{$elem['as']}`";
            }
        }
        foreach ($table2Fields as $elem) {
            if (is_string($elem)) {
                $info[]="`$table2Name`.`$elem` AS `$elem`";
            } elseif (is_array($elem)) {
                $info[]="`$table2Name`.`{$elem['name']}` AS `{$elem['as']}`";
            }
        }
        $info=join(", ", $info);

        // Construction de la requête
        // Filtre "Where" et options
        $where=array();
        foreach ($table1Where as $key => $value) {
            $key="`$table1Name`.`$key`";
            $where[]=$this->makeSearch($key, $value);
        }
        foreach ($table2Where as $key => $value) {
            $key="`$table2Name`.`$key`";
            $where[]=$this->makeSearch($key, $value);
        }
        $where=join(" AND ", $where);
  
        // Construction de la requête
        // Assemblage
        $query="SELECT $info FROM `$table1Name` LEFT JOIN `$table2Name` ON `$table1Name`.`$table1Index`=`$table2Name`.`$table2Index` ";
        $query.="WHERE $where $options";

        // Execution de la requête
        $this->query($query);
    }

    public function update($table, $set, $where="1")
    {
        if (!$this->CSRFToken or !isset($_SESSION['oups']['CSRFToken']) or $this->CSRFToken !== $_SESSION['oups']['CSRFToken']) {
            $this->error = "CSRF Token Exception {$_SERVER['SCRIPT_NAME']}";
            error_log($this->error);
            return false;
        }
  
        $this->connect();
        $dbprefix=$this->dbprefix;

    
        if (is_array($set)) {
            $tmp=array();
            $fields=array_keys($set);
            foreach ($fields as $field) {
                // SET field = NULL
                if ($set[$field]===null) {
                    $tmp[]="`{$field}`=NULL";
                }
                // SET field = SYSDATE()
                elseif ($set[$field]==="SYSDATE") {
                    $tmp[]="`{$field}`=SYSDATE()";
                } elseif (substr($set[$field], 0, 7)==="CONCAT(") {
                    $tmp[]="`{$field}`={$set[$field]}";
                } else {
                    $set[$field]=mysqli_real_escape_string($this->conn, $set[$field]);
                    $tmp[]="`{$field}`='{$set[$field]}'";
                }
            }
            $set=join(",", $tmp);
        }

        if (is_array($where)) {
            $tmp=array();
            foreach ($where as $key => $value) {
                $tmp[]=$this->makeSearch($key, $value);
            }
            $where=join(" AND ", $tmp);
        }
        $requete="UPDATE `{$dbprefix}$table` SET $set WHERE $where;";
        $this->query($requete);
    }

    public function delete($table, $where="1")
    {
        if (!$this->CSRFToken or !isset($_SESSION['oups']['CSRFToken']) or $this->CSRFToken !== $_SESSION['oups']['CSRFToken']) {
            $this->error = "CSRF Token Exception {$_SERVER['SCRIPT_NAME']}";
            error_log($this->error);
            return false;
        }

        $this->connect();
        $dbprefix=$this->dbprefix;

        if (is_array($where)) {
            $keys=array_keys($where);
            $tmp=array();
            foreach ($keys as $key) {
                $value=mysqli_real_escape_string($this->conn, $where[$key]);
                $tmp[]=$this->makeSearch($key, $value);
            }
            $where=join(" AND ", $tmp);
        }

        $requete="DELETE FROM `{$dbprefix}$table` WHERE $where";
        $this->query($requete);
    }

    public function insert($table, $values, $options=null)
    {
        if (!$this->CSRFToken or !isset($_SESSION['oups']['CSRFToken']) or $this->CSRFToken !== $_SESSION['oups']['CSRFToken']) {
            $this->error = "CSRF Token Exception {$_SERVER['SCRIPT_NAME']}";
            error_log($this->error);
            return false;
        }
  

        $this->connect();
        $dbprefix=$this->dbprefix;
        $table=$dbprefix.$table;

        $tab=array();
        if (array_key_exists(0, $values)) {
            $fields=array_keys($values[0]);
            for ($i=0;$i<count($values);$i++) {
                foreach ($fields as $elem) {
                    $values[$i][$elem]=mysqli_real_escape_string($this->conn, $values[$i][$elem]);
                }
            }
            $fields=join("`,`", $fields);

            foreach ($values as $elem) {
                $tab[]="'".join("','", $elem)."'";
            }
        } else {
            $fields=array_keys($values);
            foreach ($fields as $elem) {
                $values[$elem]=mysqli_real_escape_string($this->conn, $values[$elem]);
            }
            $fields=join("`,`", $fields);
            $tab[]="'".join("','", $values)."'";
        }

        $values=join("),(", $tab);
        $this->query("INSERT INTO `$table` (`$fields`) VALUES ($values);");
    }


    public function makeSearch($key, $value)
    {
        // Trim des valeurs et opérateurs
        if ($value!==null) {
            $value=trim($value);
        }
        // Par défaut, opérateur =
        $operator="=";
    
        if (!strstr($key, "`") and !strstr($key, ".")) {
            $key="`$key`";
        }

        // BETWEEN
        if (substr($value, 0, 7)=="BETWEEN") {
            $tmp=trim(substr($value, 7));
            $tmp=explode("AND", $tmp);
            $value1=htmlentities(trim($tmp[0]), ENT_QUOTES | ENT_IGNORE, "UTF-8", false);
            $value1=$this->escapeString($value1);
            $value2=htmlentities(trim($tmp[1]), ENT_QUOTES | ENT_IGNORE, "UTF-8", false);
            $value2=$this->escapeString($value2);
            return "{$key} BETWEEN '$value1' AND '$value2'";
        }

        // IN
        elseif (substr($value, 0, 2)=="IN") {
            $tmp=trim(substr($value, 2));
            $tmp=explode(",", $tmp);

            $values=array();
            foreach ($tmp as $elem) {
                $values[]=$this->escapeString(htmlentities(trim($elem), ENT_QUOTES | ENT_IGNORE, "UTF-8", false));
            }
            $values=join("','", $values);

            return "{$key} IN ('$values')";
        }
    
        // NULL
        elseif ($value===null) {
            $operator=" IS NULL";
        }

        // Opérateurs =, >, <, >=, <=, <>
        elseif (substr($value, 0, 2)==">=") {
            $operator=">=";
            $value=trim(substr($value, 2));
        } elseif (substr($value, 0, 2)=="<=") {
            $operator="<=";
            $value=trim(substr($value, 2));
        } elseif (substr($value, 0, 2)=="<>") {
            $operator="<>";
            $value=trim(substr($value, 2));
        } elseif (substr($value, 0, 1)=="=") {
            $operator="=";
            $value=trim(substr($value, 1));
        } elseif (substr($value, 0, 1)==">") {
            $operator=">";
            $value=trim(substr($value, 1));
        } elseif (substr($value, 0, 1)=="<") {
            $operator="<";
            $value=trim(substr($value, 1));
        // Losrsqu'une chaîne contient < directement suivi d'un caractère alpha, la chaîne est supprimée.
    // On permet donc l'utilisation du signe < suivi d'un espace
        } elseif (substr($value, 0, 2)=="< ") {
            $operator="<";
            $value=trim(substr($value, 2));
        } elseif (substr($value, 0, 4)=="LIKE") {
            $operator="LIKE";
            $value=trim(substr($value, 4));
        }

        if ($value===null) {
            return "{$key}{$operator}";
        } elseif (in_array($value, array('CURDATE','SYSDATE'))) {
            // Losrsqu'une chaîne contient < directement suivi d'un caractère alpha, la chaîne est supprimée.
            // On permet donc l'utilisation du signe < suivi d'un espace
            return "{$key}{$operator} {$value}()";
        } else {
            $value=htmlentities($value, ENT_QUOTES | ENT_IGNORE, "UTF-8", false);
            $value=$this->escapeString($value);
            return "{$key}{$operator}'$value'";
        }
    }
}

class dbh
{
    public $CSRFToken = false;
    public $dbhost = null;
    public $dbname = null;
    public $dbuser = null;
    public $dbpass = null;
    public $dbprefix = null;
    public $error = null;
    public $msg = null;
    public $pdo = null;
    public $stmt = null;
    public $result = null;


    public function __construct()
    {
        $this->dbhost=$GLOBALS['config']['dbhost'];
        $this->dbname=$GLOBALS['config']['dbname'];
        $this->dbuser=$GLOBALS['config']['dbuser'];
        $this->dbpass=$GLOBALS['config']['dbpass'];
        $this->dbprefix=$GLOBALS['config']['dbprefix'];
        $this->result=array();

        $this->pdo=new PDO(
            "mysql:host={$this->dbhost};dbname={$this->dbname};charset=utf8mb4",
            $this->dbuser,
            $this->dbpass,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=''")
    );
    }

    public function exec($sql)
    {
        $this->pdo->exec($sql);
    }

    public function prepare($sql)
    {
        $this->stmt=$this->pdo->prepare($sql);
    }

    public function execute($data=array())
    {
        if (!$this->CSRFToken or !isset($_SESSION['oups']['CSRFToken']) or $this->CSRFToken !== $_SESSION['oups']['CSRFToken']) {
            $this->error = "CSRF Token Exception {$_SERVER['SCRIPT_NAME']}";
            $this->msg = "CSRF Token Exception {$_SERVER['SCRIPT_NAME']}";
            error_log($this->error);
            return false;
        }

        $this->stmt->execute($data);
        $tmp=$this->stmt->fetchAll();
        $this->nb=count($tmp);

        for ($i=0;$i<$this->nb;$i++) {
            $result=array();
            foreach ($tmp[$i] as $key => $value) {
                $result[$key]=filter_var($value, FILTER_SANITIZE_STRING);
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
    public function select($table, $infos="*", $where="1", $options=null)
    {
        $table=$this->dbprefix.$table;

        if (is_array($infos)) {
            $infos=join(",", $infos);
        }

        $data=array();
        if (is_array($where)) {
            $fields=array();
            $keys=array_keys($where);
            foreach ($keys as $key) {
                $data[":$key"]=$where[$key];
                $fields[]="$key=:$key";
            }
            $where=join(" AND ", $fields);
        }

        $requete="SELECT $infos FROM `$table` WHERE $where $options";
        $this->prepare($requete);
        $this->execute($data);
    }
}
