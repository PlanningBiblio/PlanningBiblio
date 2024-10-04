<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnsubscribeController extends BaseController
{
    #[Route(path: '/unsubscribe', name: 'unsubscribe.interactive', methods: ['GET'])]
    public function interactiveUnsubscription(Request $request){

        $session = $request->getSession();

        $show_menu = empty($session->get('loginId')) ? 0 : 1;

        $this->templateParams(array(
            'show_menu' => $show_menu,
        ));

        return $this->output('unsubscribe/index.html.twig');
    }

    #[Route(path: '/unsubscribe', name: 'unsubscribe.nonInteractive', methods: ['POST'])]
    public function nonInteractiveUnsubscription(Request $request) {

        // When the mail client uses List-Unsubscribe=One-Click
        if ($request->get('List-Unsubscribe') == 'One-Click') {
            // Unsubscribe here
        }

        $response = new Response();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        $response->setStatusCode(200);
        return $response;

    }

    #[Route(path: '/unsubscribe', name: 'unsubscribe.preflight', methods: ['OPTIONS'])]
    public function returnPreflight(Request $request) {
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Allow', 'OPTIONS, GET, POST');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        return $response;

    }

}
