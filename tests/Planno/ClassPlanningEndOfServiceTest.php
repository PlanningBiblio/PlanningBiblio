<?php

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionHours;
use App\Entity\PlanningPositionTabAffectation;
use App\Entity\SelectStatus;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../legacy/Class/class.planning.php');

class ClassPlanningEndOfServiceTest extends TestCase
{
    protected SelectStatus $selectStatus;
    protected PlanningPositionTabAffectation $planningPositionTabAffectation;
    protected PlanningPositionHours $planningPositionHours;
    protected PlanningPosition $planningPosition;
    protected Agent $agent;

    public function setUp(): void
    {
        global $entityManager;

        $selectStatus = new SelectStatus;
        $selectStatus->setValeur('Catégorie A');
        $selectStatus->setCouleur('');
        $selectStatus->setCategorie(1);
        $selectStatus->setRang(0);
        $entityManager->persist($selectStatus);
        $entityManager->flush();
        $this->selectStatus = $selectStatus;

        $builder = new FixtureBuilder();
        $this->agent = $builder->build(Agent::class, ['statut' => 'Catégorie A']);

        $today = new DateTime;
        $today_ymd = $today->format('Y-m-d');
        $tableau = 99;
        $site = 1;

        $affectations = $entityManager->getRepository(PlanningPositionTabAffectation::class)->findBy(['date' => $today, 'site' => $site]);
        foreach ($affectations as $affectation) {
            $entityManager->remove($affectation);
        }
        $entityManager->flush();

        $affectation = new PlanningPositionTabAffectation;
        $affectation->setDate($today);
        $affectation->setTable($tableau);
        $affectation->setSite($site);

        $entityManager->persist($affectation);
        $entityManager->flush();
        $this->planningPositionTabAffectation = $affectation;

        $planningPositionHours = new PlanningPositionHours;
        $planningPositionHours->setStart(DateTime::createFromFormat('H:i', '16:00'));
        $planningPositionHours->setEnd(DateTime::createFromFormat('H:i', '17:00'));
        $planningPositionHours->setTable(0);
        $planningPositionHours->setNumber($tableau);

        $entityManager->persist($planningPositionHours);
        $entityManager->flush();
        $this->planningPositionHours = $planningPositionHours;

        $planningPosition = new PlanningPosition;
        $planningPosition->setUser($this->agent->getId());
        $planningPosition->setDate($today);
        $planningPosition->setStart(DateTime::createFromFormat('H:i', '16:00'));
        $planningPosition->setEnd(DateTime::createFromFormat('H:i', '17:00'));
        $planningPosition->setPosition(0);
        $planningPosition->setSite(1);

        $entityManager->persist($planningPosition);
        $entityManager->flush();
        $this->planningPosition = $planningPosition;
    }

    public function tearDown(): void
    {
        global $entityManager;

        $entityManager->remove($this->planningPosition);
        $entityManager->remove($this->planningPositionHours);
        $entityManager->remove($this->planningPositionTabAffectation);
        $entityManager->remove($this->agent);
        $entityManager->remove($this->selectStatus);
        $entityManager->flush();
    }

    public function testFinDeServiceWithAbsence(): void
    {
        global $entityManager;

        $today = (new \DateTime())->format('Y-m-d');

        $p = new \planning();
        $p->date = $today;
        $p->site = '1';
        $p->finDeService();

        $this->assertTrue($p->categorieA);

        $builder = new FixtureBuilder();
        $absence = $builder->build(
            Absence::class,
            [
                'perso_id' => $this->agent->getId(),
                'debut' => (new \DateTime())->sub(new \DateInterval('P2D')),
                'fin' => (new \DateTime())->add(new \DateInterval('P2D')),
                'valide' => 1,
                'groupe' => '',
            ]
        );

        $p = new \planning();
        $p->date = $today;
        $p->site = '1';
        $p->finDeService();

        $this->assertFalse($p->categorieA, 'Check should be negative because the agent is absent');

        $entityManager->remove($absence);
        $entityManager->flush();
    }

    public function testFinDeServiceWithHoliday(): void
    {
        global $entityManager;

        $today = (new \DateTime())->format('Y-m-d');

        $p = new \planning();
        $p->date = $today;
        $p->site = '1';
        $p->finDeService();

        $this->assertTrue($p->categorieA);

        $builder = new FixtureBuilder();
        $holiday = $builder->build(
            Holiday::class,
            [
                'perso_id' => $this->agent->getId(),
                'debut' => (new \DateTime())->sub(new \DateInterval('P2D')),
                'fin' => (new \DateTime())->add(new \DateInterval('P2D')),
                'valide' => 1,
                'groupe' => '',
            ]
        );

        $p = new \planning();
        $p->date = $today;
        $p->site = '1';
        $p->finDeService();

        $this->assertFalse($p->categorieA, 'Check should be negative because the agent is in holiday');

        $entityManager->remove($holiday);
        $entityManager->flush();
    }
}
