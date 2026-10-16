<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class LegalNoticesController extends BaseController
{
    #[Route(path: '/legal-notices', name: 'legal-notices', methods: ['GET'])]
    public function index(Request $request, Session $session): Response
    {
        $this->templateParams([
            'hideMenu' => empty($session->get('loginId')),
            'title' => 'Legal notice',
        ]);

        return $this->output('legalNotices/index.html.twig');
    }
}
