<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Agent;
use App\Entity\HolidayInfo;
use App\Planno\Helper\HolidayHelper;
use App\Planno\Helper\HourHelper;
use DateTime;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

include_once(__DIR__ . '/../../legacy/Class/class.conges.php');

class CompTimeController extends BaseController
{
    #[Route(path: '/comptime/add', name: 'comptime.add', methods: ['GET'])]
    public function add(Request $request, Session $session)
    {
        if ($this->config('Conges-Recuperations') == 0  || $this->config('Conges-Enable') == 0 ) {
            return $this->redirectToRoute('access-denied');
        }

        $perso_id = $request->query->getInt('perso_id');

        if (!$perso_id) {
            $perso_id = $session->get('loginId');
        }

        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->forAgent($perso_id)
            ->getValidationLevelFor($session->get('loginId'));

        if (!$admin and !$adminN2) {
            $perso_id = $session->get('loginId');
        }

        $c = new \conges();
        $balance = $c->calculCreditRecup($perso_id);
        $recuperation = number_format((float) $balance[1], 2, '.', ' ');

        $balance_before_days = null;
        $balance2_before_days = null;

        $holiday_helper = new HolidayHelper();
        $hoursPerDay = 0;
        $hoursPerDayInHoursMinutes = null;
        if ($holiday_helper->showHoursToDays()) {
            $hoursPerDay = $holiday_helper->hoursPerDay($perso_id);
            $hoursPerDayInHoursMinutes = HourHelper::decimalToHoursMinutes($hoursPerDay)['as_string'];
            $balance_before_days = $holiday_helper->hoursToDays($balance[1], $perso_id, null, true);
            $balance2_before_days = $holiday_helper->hoursToDays($balance[4], $perso_id, null, true);
        }

        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($session->get('loginId'));

        $holiday_info = $this->entityManager->getRepository(HolidayInfo::class)
            ->findByDateRange(new DateTime, (new DateTime)->modify('+1 year'));

        $this->templateParams(array(
            'is_holiday'            => false,
            'request_type'          => 'recover',
            'id'                    => null,
            'allday'                => false,
            'halfday'               => false,
            'hre_debut'             => null,
            'hre_fin'               => null,
            'debut'                 => null,
            'fin'                   => null,
            'debit'                 => null,
            'valide'                => true,
            'delete_button'         => null,
            'commentaires'          => '',
            'anticipation'          => '',
            'balance_before'        => heure4($balance[1], true),
            'balance_before_days'   => $balance_before_days,
            'balance_date'          => dateFr($balance[0]),
            'balance2_before'       => heure4($balance[4], true),
            'balance2_before_days'  => $balance2_before_days,
            'credit'                => '',
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'hours_per_day'         => $hoursPerDay,
            'hours_per_day_in_hhmm' => $hoursPerDayInHoursMinutes,
            'holiday_info'          => $holiday_info,
            'agent_name'            => $_SESSION['login_nom'] . ' ' . $_SESSION['login_prenom'],
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'managed'               => $managed,
            'perso_id'              => $perso_id,
            'recuperation'          => $recuperation,
            'recuperation_prev'     => $balance[4],
            'reliquat'              => '',
            'show_allday'           => true,
            'title'                 => 'Requesting compensation for overtime',
            'selected_agent_id'     => $perso_id,
            'save_button'           => true,
        ));

        return $this->output('holiday/edit.html.twig');
    }

}
