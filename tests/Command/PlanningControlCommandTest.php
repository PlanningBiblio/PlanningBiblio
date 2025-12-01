<?php

namespace App\Tests\Command;

use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionHours;
use App\Entity\PlanningPositionLines;
use App\Entity\PlanningPositionLock;
use App\Entity\PlanningPositionTabAffectation;
use App\Entity\PlanningPositionTab;
use App\Entity\Position;
use Tests\PLBWebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
class PlanningControlCommandTest extends PLBWebTestCase
{

    public function testSomething(): void
    {
        $this->setParam('Rappels-Actifs', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('Multisites-site1', 1);
        $this->setParam('Multisites-site2', 0);
        $this->setParam('Multisites-site3', 0);
        $this->setParam('Multisites-site4', 0);
        $this->setParam('Rappels-Jours', 1);
        $this->setParam('Dimanche', 0);
        $this->setParam('Rappels-Renfort', 0);
        $this->setParam('Conges-Enable', 0);
        $this->setParam('Mail-Planning', 'xxx.ss@biblibre.com');

        $dt1 = new \DateTime('today 09:00:00');
        $hms1 = $dt1->format('H:i:s');
        $dt2 = new \DateTime('today 10:00:00');
        $hms2 = $dt2->format('H:i:s');

        $today = new \DateTime('');
        $ppta = $this->builder->build(PlanningPositionTabAffectation::class, [
            'date' => $today, 'tableau' => 1, 'site' => 1
        ]);
        $ppl = $this->builder->build(PlanningPositionLock::class, [
            'date' => $today, 'verrou' => '1', 'perso' => '0',
            'verrou2' => 1, 'validation2' => new \DateTime('2025-11-17 10:19:36'), 'perso2' => '1','vivier'=>'0','site'=>'1'
        ]);
        $this->builder->build(PlanningPositionTab::class, [
            'id' => 1, 'tableau'=>1,
            'nom' => 'Scolaire : Mercredi - Samedi',
            'site'=> 1
        ]);
        $this->builder->build(PlanningPosition::class, [
            'id'=> 1,
            'perso_id' => 19, 'date'=>$today,
            'site'=>1, 'debut' => $dt1, 'fin' => $dt2
        ]);
        $this->builder->build(PlanningPositionHours::class, [
            'numero'  => 1,
            'tableau' => 1,
            'debut' => $dt1, 'fin' => $dt2
        ]);

        $pos = new Position();
        $pos->setName('toto');
        $pos->setGroup('');
        $pos->setGroupId(0);
        $pos->setMandatory('Obligatoire');
        $pos->setFloor(2);
        $pos->setActivities([5, 9]);
        $pos->setStatistics(1);
        $pos->setTeleworking(0);
        $pos->setBlocking(1);
        $pos->setLunch(0);
        $pos->setDelete(null);
        $this->entityManager->persist($pos);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->builder->build(PlanningPositionLines::class, [
            'numero'  => 1,
            'tableau' => 1,
            'ligne'   => 1,
            'type'    => 'poste',
            'poste'   => $pos->getId()
        ]);

        $repo = $this->entityManager->getRepository(PlanningPositionTabAffectation::class);
        $this->assertNotNull(
            $repo->findOneBy(['date' => $today, 'tableau' => 1, 'site' => 1]),
            'PlanningPositionTabAffectation should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionLock::class);
        $this->assertNotNull(
            $repo->findOneBy(['date' => $today, 'site' => 1]),
            'PlanningPositionLock should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionTab::class);
        $this->assertNotNull(
            $repo->find(1),
            'PlanningPositionTab should be saved'
        );

        $repo = $this->entityManager->getRepository(Position::class);
        $this->assertNotNull(
            $repo->find(id: $pos->getId()),
            'Position should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPosition::class);
        $this->assertNotNull(
            $repo->find(1),
            'PlanningPosition should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionHours::class);
        $this->assertNotNull(
            $repo->findOneBy(['numero' => 1, 'tableau' => 1]),
            'PlanningPositionHours should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionLines::class);
        $this->assertNotNull(
            $repo->findOneBy(['numero' => 1, 'tableau' => 1, 'ligne' => 1]),
            'PlanningPositionLines should be saved'
        );

        $this->execute();
        
        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:planning:control');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--not-really' => true
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString("To: xxx.ss@biblibre.com", $output);
        $this->assertStringContainsString("Subject: Plannings", $output);
        $this->assertStringContainsString("Message:", $output);

    }
    
}
