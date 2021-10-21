<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . '/../../public/conges/class.conges.php');

class HolidayInfoController extends BaseController
{
    /**
     * @Route("/holiday-info", name="holiday_info.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        $CSRFSession = $GLOBALS['CSRFSession'];
        $dbprefix = $GLOBALS['dbprefix'];
        $admin = false;
        $today = date("Y-m-d");
        $information = null;

        if (!$this->isAdmin()) {
            return $this->redirectToRoute('access-denied');
        }

        $db = new \db();
        $db->sanitize_string = false;
        $db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$today' ORDER BY `debut`,`fin`;");

        if($db->result){
            $information  = $db->result;
        }

        $this->templateParams(
            array(
                'info' => $information,
                'admin' => $admin
            )
        );

        return $this->output('holidayInfo/index.html.twig');
    }

    /**
     * @Route("/holiday-info/add", name="holiday_info.add", methods={"GET"})
     */
    public function add(Request $request)
    {
        if(!$this->isAdmin()){
            return $this->redirectToRoute('access-denied');
        }

        $this->templateParams(
            array(
                'id'    => null,
                'debut' => null,
                'fin'   => null,
                'texte' => null,
            )
        );

        return $this->output('holidayInfo/edit.html.twig');
    }

    /**
     * @Route("/holiday-info/{id}", name="holiday_info.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {
        if(!$this->isAdmin()){
            return $this->redirectToRoute('access-denied');
        }

        $id = $request->get('id');

        $db = new \db();
        $db->sanitize_string = false;
        $db->select("conges_infos", "*", "id='$id'");
        $debut = dateFr($db->result[0]['debut']);
        $fin = dateFr($db->result[0]['fin']);
        $texte = $db->result[0]['texte'];

        $this->templateParams(
            array(
                'id'     => $id,
                'debut'  => $debut,
                'fin'    => $fin,
                'texte'  => $texte
            )
        );

        return $this->output('holidayInfo/edit.html.twig');
    }

    /**
     * @Route("/holiday-info", name="holiday_info.update", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        if(!$this->isAdmin()){
            return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->request->get('CSRFToken');

        $id = $request->get('id');
        $debut = $request->get('debut');
        $fin = $request->get('fin');

        if (empty($fin)) {
          $fin = $debut;
        }

        $texte = $request->get('texte');

        $db = new \db();
        $db->CSRFToken = $CSRFToken;

        if ($id) {
            $db->update("conges_infos", array("debut"=>dateSQL($debut),"fin"=>dateSQL($fin),"texte"=>$texte), array("id"=>$id));
            $flash = "L'information a bien été modifiée.";
        } else {
            $db->CSRFToken = $CSRFToken;
            $db->insert("conges_infos", array("debut"=>dateSQL($debut),"fin"=>dateSQL($fin),"texte"=>$texte));
            $flash = "L'information a bien été enregistrée.";
        }

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('holiday_info.index');
    }

    /**
     * @Route("/holiday-info", name="holiday_info.delete", methods={"DEL"})
     */
    public function delete(Request $request, Session $session)
    {
        if(!$this->isAdmin()){
            return $this->redirectToRoute('access-denied');
        }

        $CSRFToken = $request->request->get('CSRFToken');
        $id = $request->get('id');

        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete('conges_infos', array('id'=>$id));
        $flash = "L'information a bien été supprimée.";

        $session->getFlashBag()->add('notice', $flash);
        return $this->redirectToRoute('holiday_info.index');
    }

    private function isAdmin()
    {
        $droits = $GLOBALS['droits'];

        for ($i = 1; $i <= $this->config('Multisites-nombre') ; $i++) {
            if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
                return true;
            }
        }

        return false;
    }
}