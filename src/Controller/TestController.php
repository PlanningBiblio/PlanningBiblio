<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
use Jumbojett\OpenIDConnectClient;

class TestController extends BaseController
{
    /**
     * @Route("/test", name="test")
     */
    public function index()
    {

        $oidc = new OpenIDConnectClient('https://login.microsoftonline.com/e70a4302-015f-4980-9814-a6225f6a1c0a',
                                'd2bc7187-0d76-4fab-aba4-b6e6363ca287',
                                'ClientSecretHere');
        $oidc->setCertPath('/home/planno/MSAzureCA.crt');
        $oidc->authenticate();
        $name = $oidc->requestUserInfo('given_name');
    }
}
