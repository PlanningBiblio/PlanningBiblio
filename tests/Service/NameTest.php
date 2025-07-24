<?php

namespace App\Tests\Service;

use App\Model\Agent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\FixtureBuilder;

class NameTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $builder = new FixtureBuilder();

        $names = [
            ['Dupont', 'Jean', 'Dupont J', 'Jean Dupont', 'Dupont Jean'],
            ['Leroyer', 'Adam', 'Leroyer A', 'Adam Leroyer', 'Leroyer Adam'],
            ['De Latour', 'Michelle', 'De Latour M', 'Michelle De Latour', 'De Latour Michelle'],
            ['Boivin', 'Karelle', 'Boivin K', 'Karelle Boivin', 'Boivin Karelle'],
        ];

        $agents = [];
        $agentObject = [];
        $agentTable = [];

        foreach ($names as $name) {
            $agent = $builder->build(Agent::class, [
                'nom' => $name[0], 'prenom' => $name[1],
            ]);

            $agents[] = [
                'agent' => $agent,
                'name' => $name,
            ];

            $agentObject[$agent->id()] = $agent;

            $agentTable[$agent->id()] = [
                'id' => $agent->id(),
                'nom' => $agent->nom(),
                'prenom' => $agent->prenom(),
            ];
        }

        foreach ($agents as $a) {
            $agent = $a['agent'];
            $name = $a['name'];

            $output = nom($agent->id());
            $this->assertEquals($name[2], $output);

            $output = nom($agent->id(), 'nom p');
            $this->assertEquals($name[2], $output);

            $output = nom($agent->id(), 'prenom nom');
            $this->assertEquals($name[3], $output);

            $output = nom($agent->id(), 'nom prenom');
            $this->assertEquals($name[4], $output);

            $output = nom($agent->id(), $agentObject);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->id(), 'nom p', $agentObject);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->id(), 'prenom nom', $agentObject);
            $this->assertEquals($name[3], $output);

            $output = nom($agent->id(), 'nom prenom', $agentObject);
            $this->assertEquals($name[4], $output);

            $output = nom($agent->id(), $agentTable);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->id(), 'nom p', $agentTable);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->id(), 'prenom nom', $agentTable);
            $this->assertEquals($name[3], $output);

            $output = nom($agent->id(), 'nom prenom', $agentTable);
            $this->assertEquals($name[4], $output);
        }
    }
}
