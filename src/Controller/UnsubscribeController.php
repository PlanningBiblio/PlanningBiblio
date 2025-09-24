<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnsubscribeController extends BaseController
{
    #[Route(path: '/unsubscribe/{token}', name: 'unsubscribe.interactive', requirements: ['token' => '.+'], methods: ['GET'])]
    public function interactiveUnsubscription(Request $request, String $token){

        $session = $request->getSession();

        $show_menu = empty($session->get('loginId')) ? 0 : 1;

        $mail = decrypt($token);

        $this->templateParams(array(
            'show_menu' => $show_menu,
            'mail' => $mail,
        ));

        return $this->output('unsubscribe/index.html.twig');
    }

    #[Route(path: '/unsubscribe/{token}', name: 'unsubscribe.nonInteractive', requirements: ['token' => '.+'], methods: ['POST'])]
    public function nonInteractiveUnsubscription(Request $request, String $token): \Symfony\Component\HttpFoundation\Response {

        // When the mail client uses List-Unsubscribe=One-Click
        if ($request->get('List-Unsubscribe') == 'One-Click') {
            $mail = decrypt($token);
        }

        $response = new Response();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        $response->setStatusCode(200);
        return $response;

    }

    #[Route(path: '/unsubscribe/{token}', name: 'unsubscribe.preflight', requirements: ['token' => '.+'],  methods: ['OPTIONS'])]
    public function returnPreflight(Request $request): \Symfony\Component\HttpFoundation\Response {
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Allow', 'OPTIONS, GET, POST');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', '*');

        return $response;

    }

}
