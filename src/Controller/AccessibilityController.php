<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class AccessibilityController extends BaseController
{
    #[Route(path: '/accessibility', name: 'accessibility', methods: ['GET'])]
    public function index(Request $request, Session $session): Response
    {
        $this->templateParams([
            'hideMenu' => empty($session->get('loginId')),
            'title' => 'Accessibility statement',
        ]);

        return $this->output('accessibility/index.html.twig');
    }
}
