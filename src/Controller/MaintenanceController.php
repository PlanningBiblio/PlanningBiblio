<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Migration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends BaseController
{

    /**
     * @Route("/maintenance", name="maintenance", methods={"GET"})
     */
    public function maintenance(Request $request)
    {
        $migration = new Migration;
        $check = $migration->check();
        $display_add = array();
        $display_delete = array();

        if ($check > 0){
            $display_add = $migration->toUp();
        }

        if ($check < 0){
            $display_delete = $migration->toDown();
        }

        $this->templateParams(
            array(
                "toAdd"      =>   $display_add,
                "toDelete"   =>   $display_delete
            )
        );
        return $this->output('maintenance.html.twig');
    }
}