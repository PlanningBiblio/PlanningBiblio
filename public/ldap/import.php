<?php
/**
Planning Biblio, Version 2.7.15
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ldap/import.php
Création : 2 juillet 2014
Dernière modification : 27 septembre 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet l'import des agents à partir d'un annuaire LDAP.
Affiche un formulaire de recherche et la liste des agents correspondants à la recherche.

Fichier appelé par la page personnel/import.php
*/
include_once "class.ldap.php";

$rechercheLdap=filter_input(INPUT_GET, "recherche-ldap", FILTER_SANITIZE_STRING);

echo "<h3>Importation des agents à partir de l'annuaire LDAP</h3>\n";
echo "<div id='import-div' style='position:relative; margin:30px 0 0 0;'>\n";
echo "<div id='ldap' style='margin-left:80px;'>\n";
echo "<form method='get' action='index.php'>\n";

//		Formulaire de recherche
echo "Importation de nouveaux agents &agrave; partir de l'annuaire LDAP<br/><br/>\n";
echo "<input type='text' name='recherche-ldap' value='$rechercheLdap' />\n";
?>
<input type='hidden' name='import-type' value='ldap' />
<input type='hidden' name='page' value='personnel/import.php' />
<input type='submit' value='Rechercher' class='ui-button' style='margin-left:30px;'/>
</form>
<br/>

<?php
//		Recherche dans l'annuaire si le formulaire est validé
if ($rechercheLdap) {
    $infos=array();
    if (!$config['LDAP-Port']) {
        $config['LDAP-Port']="389";	//	port par defaut
    }
    if (!$config['LDAP-Filter']) {
        $filter="(objectclass=inetorgperson)";	//	filtre par defaut
    } elseif ($config['LDAP-Filter'][0]!="(") {
        $filter="({$config['LDAP-Filter']})";
    } else {
        $filter=$config['LDAP-Filter'];
    }

    //	Ajout des infos de recherche dans le filtre
    if ($rechercheLdap) {
        $filter="(&{$filter}(|({$config['LDAP-ID-Attribute']}=*$rechercheLdap*)(givenname=*$rechercheLdap*)(sn=*$rechercheLdap*)(mail=*$rechercheLdap*)))";
    }

    //	Connexion au serveur LDAP
    $url = "{$config['LDAP-Protocol']}://{$config['LDAP-Host']}:{$config['LDAP-Port']}";
    $ldapconn = ldap_connect($url)
    or die("Impossible de joindre le serveur LDAP");
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
    if ($ldapconn) {
        $ldapbind=ldap_bind($ldapconn, $config['LDAP-RDN'], decrypt($config['LDAP-Password']))
      or die("Impossible de se connecter au serveur LDAP");
    }
    if ($ldapbind) {
        $justthese=array("dn",$config['LDAP-ID-Attribute'],"sn","givenname","userpassword","mail");

        if (!empty($config['LDAP-Matricule'])) {
            $justthese = array_merge($justthese, array($config['LDAP-Matricule']));
        }

        $sr=ldap_search($ldapconn, $config['LDAP-Suffix'], $filter, $justthese);
        $infos=ldap_get_entries($ldapconn, $sr);
    }

    //	Recherche des agents existants
    $agents_existants=array();
    $db=new db();
    $db->query("SELECT `login` FROM `{$dbprefix}personnel` WHERE `supprime`<>'2' ORDER BY `login`;");
    if ($db->result) {
        foreach ($db->result as $elem) {
            $agents_existants[]=$elem['login'];
        }
    }

    //	Suppression des agents existant du tableau LDAP
    $tab=array();
    if (!empty($infos)) {
        foreach ($infos as $info) {
            if (!in_array($info[$config['LDAP-ID-Attribute']][0], $agents_existants) and !empty($info)) {
                $tab[]=$info;
            }
        }
        $infos=$tab;
    }

    //	Affichage du tableau
    if (!empty($infos)) {
        usort($infos, "cmp_ldap");
        $i=0;
        echo "<form name='form' method='post' action='index.php'>\n";
        echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
        echo "<input type='hidden' name='page' value='personnel/import.php' />\n";
        echo "<input type='hidden' name='import-type' value='ldap' />\n";
        echo "<input type='hidden' name='recherche' value='$rechercheLdap' />\n";
        echo "<table id='tableLdapImport' class='CJDataTable' data-sort='[[1,\"asc\"],[2,\"asc\"]]' data-length='50' >\n";
        echo "<thead>\n";
        echo "<tr>\n";
        echo "<th class='dataTableNoSort aLeft'><input type='checkbox' class='CJCheckAll' /></th>\n";
        echo "<th>Nom</th><th>Pr&eacute;nom</th><th>e-mail</th><th>Login</th><th>Matricule</th></tr>\n";
        echo "</thead><tbody>\n";

        foreach ($infos as $info) {
            $sn=array_key_exists('sn', $info)?$info['sn'][0]:null;
            $givenname=array_key_exists('givenname', $info)?$info['givenname'][0]:null;
            $mail=array_key_exists('mail', $info)?$info['mail'][0]:null;

            $matricule = null;
            if (!empty($config['LDAP-Matricule']) and !empty($info[$config['LDAP-Matricule']])) {
                $matricule = is_array($info[$config['LDAP-Matricule']]) ? $info[$config['LDAP-Matricule']][0] : $info[$config['LDAP-Matricule']];
            }

            echo "<tr>\n";
            echo "<td><input type='checkbox' name='chk[]' value='".utf8_decode($info[$config['LDAP-ID-Attribute']][0])."' /></td>\n";
            echo "<td>$sn</td>\n";
            echo "<td>$givenname</td>\n";
            echo "<td>$mail</td>\n";
            echo "<td>{$info[$config['LDAP-ID-Attribute']][0]}</td>\n";
            echo "<td>$matricule</td>\n";
            echo "</tr>\n";
            $i++;
        }
        echo "</tbody>\n";
        echo "</table><br/>\n";
        echo "<input type='submit' value='Importer' class='ui-button' />\n";
        echo "</form>\n";
    }
}
echo "<br/><a href='/agent'>Retour &agrave; la liste des agents</a><br/>\n";
echo "</div> <!-- ldap -->\n";
echo "</div>\n";
?>