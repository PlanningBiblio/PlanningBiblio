<?php

use App\Model\Agent;
use App\Model\Model;

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testIsWeek() {
        $builder = new FixtureBuilder();
        $builder->delete(Model::class);
        $model1 = $builder->build(Model::class, array('nom' => 'bu info', 'jour' => 7));
        $model2 = $builder->build(Model::class, array('nom' => 'bu droit', 'jour' => 9));

        $this->assertTrue($model1->isWeek());
        $this->assertFalse($model2->isWeek());
    }
}