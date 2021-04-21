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

        // Manage models
        $access_model = false;
        for ($i=1; $i<=$this->config('Multisites-nombre'); $i++) {
            if (in_array((300+$i), $droits)) {
                $access_model = true;
                break;
            }
        }

        $this->templateParams( array('access_model' => $access_model) );

        return $this->output('admin/index.html.twig');
    }
}
