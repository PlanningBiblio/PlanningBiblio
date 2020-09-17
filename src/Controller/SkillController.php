<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Skill;
use App\Model\Agent;
use App\Model\Position;

require_once(__DIR__.'/../../public/activites/class.activites.php');

class SkillController extends BaseController
{
    /**
     * @Route("/skill", name ="skill.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        //        Recherche des activites

        $query = $this->entityManager->getRepository(Skill::class)->findBy(array('supprime' => NULL));
        $activites = array();

        if($query){
            foreach ($query as $elem){
                $activites[] = $elem;
            }
        }

        //        Contrôle si l'activité est attribuée à un agent pour en interdire la suppression
        $activites_utilisees = array();
        $tab = array();


        $db = $this->entityManager->createQueryBuilder();
        $db->select('p.activites')
           ->from(Position::class, 'p')
           ->where('p.supprime IS NULL')
           ->groupBy('p.activites');

        $res = $db->getQuery();
        $result = $res->getResult();

        if ($result){
           foreach ($result as $elem){
               $tab[] = $elem['activites'];
           }
        }


      //        Contrôle si l'activité est attribuée à un agent pour en interdire la suppression

        $db = $this->entityManager->createQueryBuilder();
        $db->select('a.postes')
           ->from(Agent::class, 'a')
           ->where('a.supprime <> 2')
           ->groupBy('a.postes');

        $res = $db->getQuery();
        $result = $res->getResult();

        if ($result){
            foreach ($result as $elem){
                $tab[] = html_entity_decode($elem['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            }
        }

        if(!empty($tab)){
            foreach($tab as $elem){
                if(is_array ($elem)){
                    foreach ($elem as $act){
                        if (!in_array ($act, $activites_utilisees)){
                            $activites_utilisees[] = $act;
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

        if(!$nom){
            $session->getFlashbag()->add('error',"Le nom ne peut pas être vide");
            if(!$id){
                return $this->redirectToRoute('skill.add');
            } else {
                return $this->redirectToRoute('skill.edit', array('id' => $id));
            }
        } else {
            if(!$id){
                $skill = new Skill;
                $skill->nom($nom);
                try{
                    $this->entityManager->persist($skill);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }
                if (isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'ajout de l'activité " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice', "L'activité a été ajoutée avec succès");
                }
            }else{
                $skill = $this->entityManager->getRepository(Skill::class)->find($id);
                try{
                    $this->entityManager->persist($skill);
                    $this->entityManager->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }
                if(isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de la modification de l'activité " );
                    $this->logger->error($error);
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

    public function delete_skill(Request $request, Session $session){

        $id = $request->get('id');

        $skill = $this->entityManager->getRepository(Skill::class)->find($id);
        $skill->disable();

        try{
            $this->entityManager->persist($skill);
            $this->entityManager->flush();
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }
        if(isset($error)) {
            $session->getFlashBag()->add('error', "Une erreur est survenue lors de la suppression de l'activité " );
            $this->logger->error($error);
        } else {
            $session->getFlashBag()->add('notice',"L'activité a bien été supprimée");
            return $this->json("Ok");
        }


    }

}

?>
