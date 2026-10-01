<?php

namespace App\Controller;

use App\Entity\PublicHoliday;
use App\Service\PublicHolidayService;
use DateTime;
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

        $years = $this->entityManager->getRepository(PublicHoliday::class)->findYears();

        if (!in_array($yearNext, $years)) {
            $years[] = $yearNext;
        }
        if (!in_array($yearCurrent, $years)) {
            $years[] = $yearCurrent;
        }

        sort($years);

        // Recherche des jours fériés enregistrés dans la base de données
        $days = $this->entityManager->getRepository(PublicHoliday::class)->findBy(['annee' => $yearSelected], ['jour' => 'ASC']);

        // Recherche des jours fériés avec la fonction "jour_ferie"
        if (empty($days)) {
            $year = substr($yearSelected, 0, 4);

            $start = new DateTime($year . '-09-01');
            $end = (clone $start)->modify('+1 year');

            $days = PublicHolidayService::getFrenchHolidaysByDateRange($start, $end);
        }

        $this->templateParams([
            'days'           => $days,
            'nbDays'         => count($days),
            'nbExtra'        => count($days) + 15,
            'selectedYear'   => $yearSelected,
            'title'          => 'Public holidays and closing days',
            'years'          => $years
        ]);

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
        $year = $request->request->get('annee');

        // Delete all entries for the corresponding year
        $holidays = $this->entityManager->getRepository(PublicHoliday::class)->findByAnnee($year);
        foreach($holidays as $holiday) {
            $this->entityManager->remove($holiday);
        }

        // Inserts the elements received from the form
        $keys = array_keys($post['jour']);

        foreach ($keys as $elem) {
            if (empty($post['jour'][$elem]) or $post['jour'][$elem] == '0000-00-00') {
                continue;
            }

            $holiday = new PublicHoliday();

            $holiday->setClosed(isset($post['fermeture'][$elem]))
                ->setComment($post['commentaire'][$elem])
                ->setDay(DateTime::createFromFormat('d/m/Y', $post['jour'][$elem]))
                ->setName($post['nom'][$elem])
                ->setPublicHoliday(isset($post['ferie'][$elem]))
                ->setYear($post['annee']);

            $this->entityManager->persist($holiday);
        }

        $this->entityManager->flush();

        $this->addFlash('notice', 'The list of public holidays has been successfully modified');

        return $this->redirectToRoute('closingday.index');
    }
}
