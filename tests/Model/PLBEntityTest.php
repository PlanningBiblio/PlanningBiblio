<?php

use App\Model\Agent;
use App\Model\Absence;
use App\Model\PLBEntity;

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;

class PLBEntityTest extends TestCase
{

    public function test__call(){
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $name = 'add';
        $this->expectExceptionMessage("Unknown method $name");
        $agent->add();
    }

    public function testDisable() {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $date = \DateTime::createFromFormat("d/m/Y", '29/12/2022');
        $builder->delete(Absence::class);
        $absence = $builder->build(Absence::class, array('debut' => $date, 'fin' => $date, 'perso_id' => $agent->id(), 'valide_n1' => 1, 'valide' =>1, 'groupe' => 1));

        $this->expectExceptionMessage("This entity cannot be disabled");
        $absence->disable();
    }

    public function testEnable() {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $date = \DateTime::createFromFormat("d/m/Y", '29/12/2022');
        $builder->delete(Absence::class);
        $absence = $builder->build(Absence::class, array('debut' => $date, 'fin' => $date, 'perso_id' => $agent->id(), 'valide_n1' => 1, 'valide' =>1, 'groupe' => 1));

        $this->expectExceptionMessage("This entity cannot be enabled");
        $absence->enable();
    }
}