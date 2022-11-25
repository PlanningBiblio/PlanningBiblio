<?php

use PHPUnit\Framework\TestCase;
use App\Model\Skill;
use App\Model\Position;
use App\Model\Agent;

use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../public/include/db.php');

class SkillRepositoryTest extends TestCase
{

    public function testPurge(){
        global $entityManager;
        $builder = new FixtureBuilder();

        $builder->delete(Skill::class);
        $skill1 = $builder->build(Skill::class, array('nom' => 'basket'));
        $skill2 = $builder->build(Skill::class, array('nom' => 'planche'));
        $skill3 = $builder->build(Skill::class, array('nom' => 'natation'));
        $skill4 = $builder->build(Skill::class, array('nom' => 'lire'));

        $purge = $entityManager->getRepository(Skill::class)->purge($skill1->id());
        $this->assertEquals(1,$purge);

        $builder->delete(Position::class);
        $post1 = $builder->build(Position::class, array(
            'nom' => 'post1',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'activites' => array('0' => $skill1->id(), '1' => $skill4->id())
        ));

        $purge = $entityManager->getRepository(Skill::class)->purge($skill1->id());
        $this->assertEquals(0,$purge);
        $purge = $entityManager->getRepository(Skill::class)->purge($skill4->id());
        $this->assertEquals(0,$purge);


        $purge = $entityManager->getRepository(Skill::class)->purge($skill2->id());
        $this->assertEquals(1,$purge);

        $post2 = $builder->build(Position::class, array(
            'nom' => 'post1',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'activites' => array('0' => $skill2->id())
        ));

        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe', 'postes' => json_encode([$skill2->id()])));

        $purge = $entityManager->getRepository(Skill::class)->purge($skill2->id());
        $this->assertEquals(0,$purge);

    }

    public function testPurgeAll(){
        global $entityManager;
        $builder = new FixtureBuilder();

        $d = date("d");
        $m = date("m");
        $Y = date("Y")+5;

        $date1 = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $Y = date("Y")-5;

        $date2 = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $Y = date("Y");

        $limitDate = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $builder->delete(Skill::class);
        $skill1 = $builder->build(Skill::class, array('nom' => 'basket', 'supprime' => $date1));
        $skill2 = $builder->build(Skill::class, array('nom' => 'planche', 'supprime' => $date1));
        $skill3 = $builder->build(Skill::class, array('nom' => 'natation', 'supprime' => $date2));
        $skill4 = $builder->build(Skill::class, array('nom' => 'lire', 'supprime' => $date2));

        $deleted_skills = $entityManager->getRepository(Skill::class)->purgeAll($limitDate);
        $this->assertEquals(2,$deleted_skills);
    }

    public function testDelete() {
        date_default_timezone_set('Europe/Luxembourg');
        global $entityManager;

        $_SESSION['oups']['CSRFToken'] = '00000';

        $builder = new FixtureBuilder();

        $d = date("d");
        $m = date("m");
        $Y = date("Y")-5;

        $date = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $builder->delete(Skill::class);
        $skill = $builder->build(Skill::class, array('nom' => 'basket', 'supprime' => $date));

        $s = $entityManager->getRepository(skill::class);
        $s->CSRFToken = '00000';
        $s->id = $skill->id();
        $s->delete();

        $db = new \db;
        $db->select2("activites", "*", array("id"=>$skill->id()));
        $skill = $db->result;

        $this->assertStringContainsString(date("Y-m-d"), $skill[0]['supprime']);
    }

    public function testAll() {
        global $entityManager;
        $builder = new FixtureBuilder();

        $d = date("d");
        $m = date("m");
        $Y = date("Y")-1;

        $date = \DateTime::createFromFormat("d/m/Y", "$d/$m/$Y");

        $builder->delete(Skill::class);
        $skill1 = new Skill();
        $skill1->nom('basket');
        $skill1->supprime(null);

        $entityManager->persist($skill1);
        $entityManager->flush();

        $skill2 = new Skill();
        $skill2->nom('basket');
        $skill2->supprime(null);

        $entityManager->persist($skill2);
        $entityManager->flush();

        $skill3 = new Skill();
        $skill3->nom('basket');
        $skill3->supprime($date);

        $entityManager->persist($skill3);
        $entityManager->flush();

        $s = $entityManager->getRepository(Skill::class);
        $s->deleted = null;
        $s->all();
        $skills = $s->elements;

        $this->assertTrue(in_array($skill1->nom(), $skills[$skill1->id()]));
        $this->assertTrue(in_array($skill2->nom(), $skills[$skill2->id()]));
        $this->assertFalse(in_array($skill3->id(), $skills));
    }
}