<?php


$req="SELECT * FROM `config` WHERE `nom`='Multisites-nombre';";

$db = new db();
$db->query($req);

$nombre = $db->result['0']['valeur'];

for ($i = 1 ; $i <= $nombre; $i++){

    //Insertion des sites
    $req= "SELECT * FROM `config` WHERE `nom`='Multisites-site$i';";
    $db = new db();
    $db->query($req);
    if($db->result){
        $valeur = $db->result[0]['valeur'];
        $nom = $db->result[0]['nom'];
        $sql[] = "INSERT INTO `{$dbprefix}site` (`id`,`nom`,`supprime`) VALUES ('$i','$valeur',NULL);";

        //Insertion des mails
        $req= "SELECT * FROM `config` WHERE `nom`='Multisites-site$i-mail';";
        $db = new db();
        $db->query($req);
        if($db->result){
            $m = $db->result[0]['valeur'];
            $mails = explode(";", $m);

            foreach ($mails as $mail){
                $sql[] = "INSERT INTO `{$dbprefix}site_mail` (`site_id`, `mail`) VALUES ('$i','$mail')";
            }
        }

        $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-site$i';";
        $sql[]="DELETE FROM `{$dbprefix}config` WHERE `nom`='Multisites-site$i-mail';";

    }

}
