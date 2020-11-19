<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends BaseController
{

    /**
     * @Route("/maintenance", name="maintenance", methods={"GET"})
     */
    public function maintenance(Request $request)
    {
        return $this->output('maintenance.html.twig');
    }
}