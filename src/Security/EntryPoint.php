<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Security\PlannoAuthenticator;

class EntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(Private UrlGeneratorInterface $urlGenerator){}

    public function start(Request $request, AuthenticationException $authException = null) : Response
    {
        $route = $request->attributes->get('_route');
        return new RedirectResponse(
            $this->urlGenerator->generate(PlannoAuthenticator::LOGIN_ROUTE,
                array('redirURL' => $route)));
    }
}
