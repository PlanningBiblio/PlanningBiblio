<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends BaseController
{
    /**
     * @Route("/ajax/language/change", name="language.change", methods={"GET"})
     */
    public function change(Request $request)
    {
      $response = new Response();

      $locale = $request->get('language');

      if (!$locale) {
          $response->setContent('No language');
          $response->setStatusCode(400);

          return $response;
      }

      $request->getSession()->set('_locale', $locale);
      $request->setLocale($locale, 'en_US');

      $response->setContent('Language successfully changed');
      $response->setStatusCode(200);

      return $response;
    }
}
