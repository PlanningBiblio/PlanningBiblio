<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : joursFeries/valid.php
Création : 25 juillet 2013
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant la validation des jours fériés et de fermeture.
Valide le formualire de la page joursFeries/index.php
*/

include "class.joursFeries.php";

$post = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
$CSRFToken = $post['CSRFToken'];

$j=new joursFeries();
$j->CSRFToken = $CSRFToken;
$j->update($post);
if($j->error){
  $msg = "Une erreur est survenue lors de la modification de la liste des jours fériés.";
  $msgType="error";
}else{
  $msg = "La liste des jours fériés a été modifée avec succès.";
  $msgType="success";
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=joursFeries/index.php&msg=$msg&msgType=$msgType';</script>\n";
?>