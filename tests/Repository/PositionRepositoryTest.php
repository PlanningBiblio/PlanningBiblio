<?php

use PHPUnit\Framework\TestCase;
use App\Model\Skill;
use App\Model\Position;

use Doctrine\ORM\EntityRepository;

use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../public/include/db.php');

class PositionRepositoryTest extends TestCase
{

    public function testGetAllSkills()
    {
        global $entityManager;
        $builder = new FixtureBuilder();

        $builder->delete(Skill::class);
        $skill1 = $builder->build(Skill::class, array('nom' => 'basket'));
        $skill2 = $builder->build(Skill::class, array('nom' => 'planche'));
        $skill3 = $builder->build(Skill::class, array('nom' => 'natation'));
        $skill4 = $builder->build(Skill::class, array('nom' => 'lire'));
        $id1 = $skill1->id();
        $id2 = $skill2->id();
        $id3 = $skill3->id();
        $id4 = $skill4->id();

        $builder->delete(Position::class);
        $post1 = $builder->build(Position::class, array(
            'nom' => 'post1',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'activites' => array('0' => $id1, '1' => $id4)
        ));
        $post2 = $builder->build(Position::class, array(
            'nom' => 'post2',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'activites' => array('0' => $id2, '1' => $id3)
        ));
        $post3 = $builder->build(Position::class, array(
            'nom' => 'post3',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'activites' => array('0' => $id1, '1' => $id3)
        ));

        $skills = $entityManager->getRepository(Position::class)->getAllSkills();

        $this->assertTrue(in_array($skill1->id(), $skills));
        $this->assertTrue(in_array($skill2->id(),$skills));
        $this->assertTrue(in_array($skill3->id(),$skills));
        $this->assertTrue(in_array($skill4->id(),$skills));
    }

    public function testPurgeAll()
    {
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

        $builder->delete(Position::class);
        $post1 = $builder->build(Position::class, array(
            'nom' => 'post1',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'supprime' => $date1
        ));
        $post2 = $builder->build(Position::class, array(
            'nom' => 'post2',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'supprime' => $date1
        ));
        $post3 = $builder->build(Position::class, array(
            'nom' => 'post3',
            'statistiques' => 0,
            'teleworking' => 0,
            'bloquant' => 1,
            'supprime' => $date2
        ));

        $deleted_posts = $entityManager->getRepository(Position::class)->purgeAll($limitDate);
        $this->assertEquals(1,$deleted_posts);
    }


    public function testAll()
    {
        global $entityManager;
        $builder = new FixtureBuilder();

        $builder->delete(Position::class);
        $post1 = new Position();
        $post1->nom('post1');
        $post1->supprime(null);
        $post1->teleworking(0);
        $post1->groupe('3');
        $post1->groupe_id('3');
        $post1->obligatoire('0');
        $post1->etage('0');
        $post1->activites('0');

        $entityManager->persist($post1);
        $entityManager->flush();

        $p = $entityManager->getRepository(Position::class);
        $p->deleted = null;
        $postes = $p->all();

        $this->assertTrue(in_array($post1->nom(), $postes[$post1->id()]));
    }
}