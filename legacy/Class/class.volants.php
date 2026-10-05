<?php
/**
Description :
Classe utilisée pour la gestion des agents volants

Cette page est appelée par la page planning/volants/index.php
*/


require_once __DIR__ . '/../Common/function.php';
require_once 'class.personnel.php';

class volants
{
    public $error;

    public function set($date, $ids, $CSRFToken): void
    {
        $db = new db();
        $db->CSRFToken = $CSRFToken;
        $db->delete('volants', array('date' => $date));
        if ($db->error) {
            $this->error = $db->error;
        }
  
        if (!empty($ids)) {
            $db = new dbh();
            $db->CSRFToken = $CSRFToken;

            $db->prepare("INSERT INTO `{$GLOBALS['dbprefix']}volants` (`date`, `perso_id`) VALUES (:date, :perso_id);");
            foreach ($ids as $elem) {
                $db->execute([
                    ':date' => $date,
                    ':perso_id' => $elem
                ]);
            }

            if ($db->error) {
                $this->error = $db->error;
            }
        }
    }
}
