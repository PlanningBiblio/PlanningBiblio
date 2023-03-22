<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\Agent;
use App\Model\Site;
use App\Model\SiteMail;



class SiteController extends BaseController
{
    /**
     * @Route("/site", name ="site.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        //        Recherche des sites

        $mailsTab = array();
        $sites = array();
        $s = $GLOBALS['entityManager']->getRepository(Site::class)->findBy(array("supprime" => NULL));

        foreach($s as $site){
            $sites[$site->id()] = $site->nom();

            // Récupère les mails de ce site

            $db = new \db();
            $db->select2("site_mail", "*", array("site_id" => $site->id()));
            if ($db->result) {
                $mailsTab[$site->id()] = '';
                foreach ($db->result as $elem) {
                    $mailsTab[$site->id()] .= $elem["mail"] ."; ";
                }
            }
        }


        $sites_Tab = array();

        foreach ($mailsTab as $site_id => $mails) {
            // Affichage des 3 premièrs sites dans le tableau, tous les sites dans l'infobulle

            $mails_tab = explode(";", $mails);
            $mailsAffiches = array();

            if (is_array($mails_tab)) {
                foreach ($mails_tab as $mail) {
                    if (count($mailsAffiches)<3) {
                        $mailsAffiches[] = $mail;
                    }
                }
            }
            $mails = implode(";", $mails_tab);
            $mailsAffiches = implode(";", $mailsAffiches);
            if (count($mails_tab)>3) {
                $mailsAffiches.=" ...";
            }

            $new['nom'] =  $sites[$site_id];
            $new['mails'] = $mails;
            $new['mailsAffiches'] = $mailsAffiches;
            $new['id'] = $site_id;

            $sites_Tab[] = $new;
        }

        $CSRFSession = $GLOBALS['CSRFSession'];

        $this->templateParams(array(
            'sites'=> $sites_Tab,
            'CSRFSession' => $CSRFSession
        ));

        return $this->output('site/index.html.twig');
    }

    /**
     * @Route("/site/add", name ="site.add", methods={"GET"})
     */
    public function add(Request $request, Session $session)
    {
        $this->templateParams(array(
            'id' => null,
            'site_name' => null,
            'mails' => null
       ));

        return $this->output('site/edit.html.twig');

    }

    /**
     * @Route("/site/{id}", name = "site.edit", methods={"GET"})
     */
    public function edit(Request $request, Session $session)
    {
        $id =  $request->get('id');

        $site = $GLOBALS['entityManager']->getRepository(Site::class)->find($id);

        $db = new \db();
        $db->select2("site_mail", "*", array("site_id" => $site->id()));
        $mails = array();
        foreach($db->result as $elem){
            $mails[] = $elem['mail'];
        }

        $this->templateParams(array(
            'site_name'=>$site->nom(),
            'id' => $id,
            'mails' => $mails
        ));

        return $this->output('site/edit.html.twig');

    }


    /**
     * @Route("/site", name = "site.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $id = $request->get('id');
        $nom = $request->get('nom');
        $i = 1;
        $mails = array();
        while($request->get("mail_$i") != null){
            $mails[] = $request->get("mail_$i");
            $i += 1;
        }

        $db=new \db();
        $db->CSRFToken = $CSRFToken;

        if(!$nom){
            $session->getFlashbag()->add('error',"Le nom ne peut pas être vide");
            if(!$id){
                return $this->redirectToRoute('site.add');
            } else {
                return $this->redirectToRoute('site.edit', array('id' => $id));
            }
        } else {
            if(!$id){
                $site = new Site();
                $site->nom($nom);
                try{
                    $GLOBALS['entityManager']->persist($site);
                    $GLOBALS['entityManager']->flush();
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }

                $this->delete_mails($site->id(), $CSRFToken);

                foreach($mails as $mail){
                    try{
                        $db->insert("site_mail", array('site_id' => $site->id(), 'mail' => $mail));
                    }
                    catch(Exception $e){
                        $error = $e->getMessage();
                    }
                }

                if (isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de l'ajout du site " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice', "Le site a été ajouté avec succès");
                }
            }else{
                $site = $GLOBALS['entityManager']->getRepository(Site::class)->find($id);
                $site->nom($nom);
                try{
                    $GLOBALS['entityManager']->persist($site);
                    $GLOBALS['entityManager']->flush();;
                }
                catch(Exception $e){
                    $error = $e->getMessage();
                }

                $this->delete_mails($id,$CSRFToken);

                foreach($mails as $mail){
                    try{
                        $db->insert("site_mail", array('site_id' => $id, 'mail' => $mail));
                    }
                    catch(Exception $e){
                        $error = $e->getMessage();
                    }
                }

                if(isset($error)) {
                    $session->getFlashBag()->add('error', "Une erreur est survenue lors de la modification du site " );
                    $this->logger->error($error);
                } else {
                    $session->getFlashBag()->add('notice',"Le site a été modifiée avec succès");
                }
            }
        }

        return $this->redirectToRoute('site.index');
    }

    /**
     * @Route("/site", name="site.delete", methods={"DELETE"})
     */

    public function delete_site(Request $request, Session $session)
    {
        $id = $request->get('id');

        $site = $GLOBALS['entityManager']->getRepository(Site::class)->find($id);
        $site->disable();

        try{
            $GLOBALS['entityManager']->persist($site);
            $GLOBALS['entityManager']->flush();
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }

        if(isset($error)) {
            $session->getFlashBag()->add('error', "Une erreur est survenue lors de la suppression du site " );
            $this->logger->error($error);
        } else {
            $session->getFlashBag()->add('notice',"Le site a bien été supprimée");
            return $this->json("Ok");
        }

    }

    public function delete_mails($site_id,$CSRFToken)
    {
        $db=new \db();
        $db->CSRFToken = $CSRFToken;
        try{
            $db->delete('site_mail', array('site_id'=>$site_id));
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }

        if(isset($error)) {
            $session->getFlashBag()->add('error', "Une erreur est survenue lors de la suppression des mails " );
            $this->logger->error($error);
        }else {
            return;
        }
    }

}

?>
