<?php

namespace App\Tests\Service;

use App\Entity\Agent;
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

            $agentObject[$agent->getId()] = $agent;

            $agentTable[$agent->getId()] = [
                'id' => $agent->getId(),
                'nom' => $agent->getLastname(),
                'prenom' => $agent->getFirstname(),
            ];
        }

        foreach ($agents as $a) {
            $agent = $a['agent'];
            $name = $a['name'];

            $output = nom($agent->getId());
            $this->assertEquals($name[2], $output);

            $output = nom($agent->getId(), 'nom p');
            $this->assertEquals($name[2], $output);

            $output = nom($agent->getId(), 'prenom nom');
            $this->assertEquals($name[3], $output);

            $output = nom($agent->getId(), 'nom prenom');
            $this->assertEquals($name[4], $output);

            $output = nom($agent->getId(), $agentObject);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->getId(), 'nom p', $agentObject);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->getId(), 'prenom nom', $agentObject);
            $this->assertEquals($name[3], $output);

            $output = nom($agent->getId(), 'nom prenom', $agentObject);
            $this->assertEquals($name[4], $output);

            $output = nom($agent->getId(), $agentTable);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->getId(), 'nom p', $agentTable);
            $this->assertEquals($name[2], $output);

            $output = nom($agent->getId(), 'prenom nom', $agentTable);
            $this->assertEquals($name[3], $output);

            $output = nom($agent->getId(), 'nom prenom', $agentTable);
            $this->assertEquals($name[4], $output);
        }
    }
}
