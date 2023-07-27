<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\UserPreference;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PreferencesController extends BaseController
{
    /**
     * @Route("/preferences", name="preferences.index", methods={"POST"})
     */
    public function index(Request $request)
    {
        $module = 'Preferences';

        if (!$this->csrf_protection($request)) {
            return $this->returnError('Forbidden CSRF', $module, 403);
        }

        $perso_id = $request->get('perso_id');
        $pref = $request->get('pref');

        if (!$perso_id || !$pref) {
            return $this->returnError('Missing parameter(s)', $module, 400);
        }

        $preference = new UserPreference();
        $preference->perso_id($perso_id);
        $preference->pref($pref);
        $preference->value(1);
        $this->entityManager->persist($preference);
        $this->entityManager->flush();

        return $this->returnError('User preference successfully updated', $module, 200);
    }
}
