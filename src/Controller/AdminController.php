<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends BaseController
{
    /**
     * @Route("/admin", name="admin.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $droits = $GLOBALS['droits'];

        // Manage models and working hours
        $access_model = false;
        $access_working_hours = false;

        for ($i=1; $i<=$this->config('Multisites-nombre'); $i++) {
            if (in_array((300 + $i), $droits)) {
                $access_model = true;
            }

            if (in_array((1100 + $i), $droits) or in_array((1200 + $i), $droits)) {
                $access_working_hours = true;
            }
        }

        $this->templateParams( array(
            'access_model' => $access_model,
            'access_working_hours' => $access_working_hours,
        ));

        return $this->output('admin/index.html.twig');
    }
}
