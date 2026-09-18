<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccessibilityController extends BaseController
{
    #[Route(path: '/accessibility', name: 'accessibility', methods: ['GET'])]
    public function index(Request $request)
    {
        $session = $request->getSession();

        $this->templateParams(array(
            'show_menu' => empty($session->get('loginId')) ? 0 : 1,
            'title'     => 'Accessibility statement',
        ));

        return $this->output('accessibility/index.html.twig');
    }
}