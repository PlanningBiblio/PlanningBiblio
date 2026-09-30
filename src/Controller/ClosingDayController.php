<?php

namespace App\Controller;

use App\Entity\PublicHoliday;
use App\Planno\ClosingDay;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ClosingDayController extends BaseController
{
    #[Route(path: '/closingday', name: 'closingday.index', methods: ['GET'])]
    public function index(Request $request, Session $session): Response
    {
        // Initalisation des variables
        $yearCurrent = date('n') < 9 ? (date('Y')-1) . '-' . (date('Y')) : (date('Y')) . '-' . (date('Y')+1);
        $yearNext = date('n') < 9 ? (date('Y')) . '-' . (date('Y')+1) : (date('Y')+1) . '-' . (date('Y')+2);

        $yearSession = $session->get('ClosingDayYear') ?? $yearCurrent;
        $yearSelected = $request->query->getString('annee', $yearSession);

        preg_match('/(\d+)-(\d+)/', $yearSelected, $matches);
        $yearSelected = $matches[0] ?? $yearCurrent;
        $session->set('ClosingDayYear', $yearSelected);

        $j = new ClosingDay();
        $j->fetchYears();
        $annees = $j->elements;

        if (!in_array($yearNext, $annees)) {
            $annees[] = $yearNext;
        }
        if (!in_array($yearCurrent, $annees)) {
            $annees[] = $yearCurrent;
        }

        sort($annees);

        // Recherche des jours fériés enregistrés dans la base de données et avec la fonction jour_ferie
        $j = new ClosingDay();
        $j->annee = $yearSelected;
        $j->auto = false;;
        $j->fetch();
        $jours = $j->elements;

        $nbDays = count($jours);
        $nbExtra = $nbDays + 15;
        $days = [];
        // Affichage des jours fériés enregistrés
        $i = 0;
        foreach ($jours as $elem) {
            $ferie = (bool) $elem['ferie'];
            $fermeture = (bool) $elem['fermeture'];
            $date = dateFr($elem['jour']);
            $commentaire = $elem['commentaire'];
            $nom = $elem['nom'];
            $days[] = array(
                "holiday" => $ferie,
                "closed"  => $fermeture,
                "date"    => $date,
                "comment" => $commentaire,
                "name"    => $nom,
                "number"  => $i
            );
            $i++;
        }

        $holiday_enable = $this->config('Conges-Enable');

        $this->templateParams(array(
            "CSRFSession"        => $GLOBALS['CSRFSession'],
            "days"               => $days,
            "holiday_enable"     => $holiday_enable,
            "nbDays"             => $nbDays,
            "nbExtra"            => $nbExtra,
            'selectedYear'       => $yearSelected,
            'title'              => 'Public holidays and closing days',
            "years"              => $annees
        ));

        return $this->output("closingdays/index.html.twig");
    }

    #[Route(path: '/closingday', name: 'closingday.save', methods: ['POST'])]
    public function save(Request $request, Session $session): RedirectResponse
    {
        if (!$this->csrf_protection($request)) {
            $session->set('AccessDeniedReason', 'CSRF');
            return $this->redirectToRoute('access-denied');
        }

        $post = $request->request->all();
        $CSRFToken = $request->get('CSRFToken');

        $j = new ClosingDay();
        $j->CSRFToken = $CSRFToken;
        $j->update($post);

        if ($j->error){
            $session->getFlashBag()->add('error',"Une erreur est survenue lors de la modification de la liste des jours fériés.");
        } else {
            $session->getFlashBag()->add('notice',"La liste des jours fériés a été modifiée avec succès.");
        }

        return $this->redirectToRoute('closingday.index');
    }
}
