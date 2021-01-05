<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class PasswordController extends BaseController {

    /**
     * @Route("/password", name="password.save", methods={"GET, POST"})
     */
    public function savePassword(Request $request){
        $ancien = filter_input(INPUT_GET, "ancien", FILTER_UNSAFE_RAW);
        $confirm = filter_input(INPUT_GET, "confirm", FILTER_UNSAFE_RAW);
        $nouveau = filter_input(INPUT_GET, "nouveau", FILTER_UNSAFE_RAW);

        $identifiants = array("name" => $_SESSION['login_prenom'], "surname" => $_SESSION['login_nom']);

        if(!$nouveau){
            $this->templateParams("login" => $identifiants);
            return $this->output('password.html.twig');
        }

        $db = new \db();
        $db->query("select login,password,mail from {$dbprefix}personnel where id=".$_SESSION['login_id'].";");
        $login = $db->result[0]['login'];
        $mail = $db->result[0]['mail'];
        if (!password_verify($ancien, $db->result[0]['password'])) {
            //traitement en jquery
            /*echo "Ancien mot de passe incorrect";
            echo "<br/><br/>\n";
            echo "<a href='javascript:history.back();'>Retour</a>\n";*/
        } elseif ($nouveau! = $confirm) {
            /*echo "Les nouveaux mots de passes ne correspondent pas";
            echo "<br/><br/>\n";
            echo "<a href='javascript:history.back();'>Retour</a>\n";*/
        } else {
            $mdp = $nouveau;
            $mdp_crypt = password_hash($mdp, PASSWORD_BCRYPT);
            $db = new db();
            $db->query("update {$dbprefix}personnel set password='".$mdp_crypt."' where id=".$_SESSION['login_id'].";");
            /*echo "Le mot de passe a été changé";
            echo "<br/><br/>\n";
            echo "<a href='index.php'>Retour au planning</a>\n";*/

            $message="Votre mot de passe Planning Biblio a &eacute;t&eacute; modifi&eacute;";
            $message.="<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";
        
            // Envoi du mail
            $m = new \CJMail();
            $m->subject = "Modification du mot de passe";
            $m->message = $message;
            $m->to = $mail;
            $m->send();

            // Si erreur d'envoi de mail, affichage de l'erreur
            if ($m->error_CJInfo) {
                echo "<script type='text/javascript'>CJInfo(\"{$m->error_CJInfo}\",\"error\");</script>\n";
            }
        }

    }