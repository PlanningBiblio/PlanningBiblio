<?php

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../legacy/Class/class.planning.php');

class ClassPlanningEndOfServiceTest extends TestCase
{
    protected int $select_statuts_id;
    protected int $pl_poste_tab_affect_id;
    protected int $pl_poste_horaires_id;
    protected int $pl_poste_id;
    protected Agent $agent;

    public function setUp(): void
    {
        global $entityManager;
        $conn = $entityManager->getConnection();

        $conn->executeStatement(
            'INSERT INTO select_statuts SET valeur = ?, couleur = ?, categorie = ?',
            ['Catégorie A', '', '1']
        );
        $this->select_statuts_id = $conn->lastInsertId();

        $builder = new FixtureBuilder();
        $this->agent = $builder->build(Agent::class, ['statut' => 'Catégorie A']);

        $today = (new \DateTime())->format('Y-m-d');
        $tableau = 99;
        $site = 1;
        $conn->executeStatement('DELETE FROM pl_poste_tab_affect WHERE date = ? AND site = ?', [$today, $site]);
        $conn->executeStatement(
            'INSERT INTO pl_poste_tab_affect SET date = ?, tableau = ?, site = ?',
            [$today, $tableau, $site]
        );
        $this->pl_poste_tab_affect_id = $conn->lastInsertId();

        $conn->executeStatement(
            'INSERT INTO pl_poste_horaires SET debut = ?, fin = ?, tableau = ?, numero = ?',
            ['16:00', '17:00', 0, $tableau]
        );
        $this->pl_poste_horaires_id = $conn->lastInsertId();

        $conn->executeStatement(
            'INSERT INTO pl_poste SET perso_id = ?, date = ?, debut = ?, fin = ?',
            [$this->agent->getId(), $today, '16:00', '17:00']
        );
        $this->pl_poste_id = $conn->lastInsertId();
    }

    public function tearDown(): void
    {
        global $entityManager;
        $conn = $entityManager->getConnection();

        $conn->executeStatement('DELETE FROM pl_poste WHERE id = ?', [$this->pl_poste_id]);
        $conn->executeStatement('DELETE FROM pl_poste_horaires WHERE id = ?', [$this->pl_poste_horaires_id]);
        $conn->executeStatement('DELETE FROM pl_poste_tab_affect WHERE id = ?', [$this->pl_poste_tab_affect_id]);
        $entityManager->remove($this->agent);
        $entityManager->flush();
        $conn->executeStatement('DELETE FROM select_statuts WHERE id = ?', [$this->select_statuts_id]);
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
