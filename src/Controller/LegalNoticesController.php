<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalNoticesController extends BaseController
{

    /**
     * @Route("/legal-notices", name="legal-notices", methods={"GET"})
     */
    public function index(Request $request)
    {
        $session = $request->getSession();

        $show_menu = empty($session->get('loginId')) ? 0 : 1;

        $this->templateParams(array(
            'show_menu' => $show_menu,
        ));

        return $this->output('legalNotices/index.html.twig');
    }
}
