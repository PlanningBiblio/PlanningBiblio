<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__.'/../../public/activites/class.activites.php');

class SkillController extends BaseController
{
    /**
     * @Route("/skill", name ="skill.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        //        Recherche des activites

        $a = new \activites();
        $a->fetch();
        $activites = $a->elements;

        //        Contrôle si l'activité est attribuée à un agent pour en interdire la suppression
        $activites_utilisees = array();
        $tab = array();
        $db = new \db();
        $db->select2("postes", "activites", array("supprime"=>null), "GROUP BY `activites`");

        if ($db->result){
            foreach ($db->result as $elem){
                $tab[]=json_decode(html_entity_decode($elem['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            }
        }

        //        Contrôle si l'activité est attribuée à un agent pour en interdire la suppression
        $db = new \db();
        $db->select2("personnel", "postes", array("supprime"=>"<>2"), "GROUP BY `postes`");
        if ($db->result){
            foreach ($db->result as $elem){
                $tab[]=json_decode(html_entity_decode($elem['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            }
        }

        if(!empty($tab)){
            foreach($tab as $elem){
                if(is_array ($elem)){
                    foreach ($elem as $act){
                        if (!in_array ($act, $activites_utilisees)){
                            $activites_utilisees[]=$act;
                        }
                    }
                }
            }
        }

        $CSRFSession = $GLOBALS['CSRFSession'];

        $this->templateParams(array(
            'usedSkills' => $activites_utilisees,
            'skills' => $activites,
            'CSRFSession' => $CSRFSession

        ));

        return $this->output('skill/index.html.twig');
    }

    /**
     * @Route("/skill/add", name ="skill.add", methods={"GET"})
     */
    public function add(Request $request, Session $session){

        $CSRFSession = $request->get('CSRFSession');
        $this->templateParams(array(
            'skill_name'=>'',
            'CSRFSession' => $CSRFSession,
       ));

        return $this->output('skill/edit.html.twig');
    }

    /**
     * @Route("/skill/{id}", name = "skill.edit", methods={"GET"})
     */
    public function edit(Request $request, Session $session){
        $id =  $request->get('id');
        $CSRFSession = $request->get('CSRFSession');
        $db = new \db();
        $db->select2("activites", "*", array("id" => $id)) ;
        $nom=$db->result[0]['nom'];
        $this->templateParams(array(
            'skill_name'=>$nom,
            'CSRFSession' => $CSRFSession,
            'id' => $id,
        ));

        return $this->output('skill/edit.html.twig');
    }


    /**
     * @Route("/skill", name = "skill.save", methods={"POST"})
     */
    public function save(Request $request, Session $session){
        $id = $request->get('id');
        $nom = $request->get('nom');
        $CSRFToken = $request->get('CSRFToken');
        if(!$nom){
            $session->getFlashbag()->add('error',"Le nom ne peut pas être vide");
            if(!$id){
                return $this->redirectToRoute('skill.add');
            } else {
                return $this->redirectToRoute('skill.edit', array('id' => $id));
            }
        } else {
            if(!$id){
                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->insert("activites", array("nom"=>$nom));
                if ($db->error){
                    $session->getFlashBag()->add('error',"L'activité n'a pas pu être ajoutée");
                } else {
                    $session->getFlashBag()->add('notice',"L'activité a été ajoutée avec succès");
                }
            }else{
                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update("activites", array("nom"=>$nom), array("id"=>$id));
                if ($db->error){
                    $session->getFlashBag()->add('error',"L'activité n'a pas pu être modifiée");
                } else {
                    $session->getFlashBag()->add('notice',"L'activité a été modifiée avec succès");
                }
            }
        }

        return $this->redirectToRoute('skill.index');
    }

    /**
     * @Route("/skill", name="skill.delete", methods={"DELETE"})
     */

    public function delete_skill(Request $request){

        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');
        $a = new \activites();

        $a->CSRFToken = $CSRFToken;
        $a->id=$id;
        $a->delete();

        return $this->json("Ok");
    }

}

?>
