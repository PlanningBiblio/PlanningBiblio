<?php

use App\Entity\Absence;
use App\Entity\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AbsenceControllerDeleteTest extends PLBWebTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '192.168.1.2';

        $this->builder->delete(Absence::class);
        $this->builder->delete(Agent::class);

        $agents = [];
        $agents[0] = $this->builder->build(Agent::class, array('login' => 'jdevoe'));
        $agents[1] = $this->builder->build(Agent::class, array('login' => 'abreton'));
        $agents[2] = $this->builder->build(Agent::class, array('login' => 'kboivin'));

        $this->agents = $agents;
    }

    public function test1(): void
    {
        /**
         * Absence validation disable
         * Agent can edit/delete its own absences (right 6)
         * The absence can be deleted
         */

        $this->setParam('Absences-validation', 0);
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Multisites-nombre', 1);

        $acl = [6];
        $nbAgent = 1;
        $validations = null;
        $result = 'empty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test2(): void
    {
        /**
         * Agent can not edit/delete its own absences (no right 6)
         * The absence can not be deleted
         */

        $acl = [];
        $nbAgent = 1;
        $validations = null;
        $result = 'notEmpty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test3(): void
    {
        /**
         * Agent can edit/delete its own absences (right 6)
         * Absence for several agents
         * The absence can be deleted
         */

        $acl = [6];
        $nbAgent = 3;
        $validations = null;
        $result = 'empty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test4(): void
    {
        /**
         * Agent can not edit/delete its own absences (no right 6)
         * Absence for several agents
         * The absence can not be deleted
         */

        $acl = [];
        $nbAgent = 3;
        $validations = null;
        $result = 'notEmpty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test5(): void
    {
        /**
         * Absence validation enable
         * Agent can edit/delete its own absences (right 6)
         * Absence is not validated
         * The absence can be deleted
         */

        $this->setParam('Absences-validation', 1);

        $acl = [6];
        $nbAgent = 1;
        $validations = null;
        $result = 'empty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test6(): void
    {
        /**
         * Agent can edit/delete its own absences (right 6)
         * Absence is validated level 1
         * The absence can not be deleted
         */

        $acl = [6];
        $nbAgent = 1;
        $result = 'notEmpty';

        $validations = [3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test7(): void
    {
        /**
         * Agent can edit/delete its own absences (right 6)
         * Absence is validated level 2
         * The absence can not be deleted
         */

        $acl = [6];
        $nbAgent = 1;
        $result = 'notEmpty';

        $validations = [3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test8(): void
    {
        /**
         * Agent can edit/delete its own absences (right 6)
         * Absence for several agents
         * Absence is not validated
         * The absence can be deleted
         */

        $acl = [6];
        $nbAgent = 3;
        $validations = [0,0];
        $result = 'empty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test9(): void
    {
        /**
         * Agent can edit/delete its own absences (right 6)
         * Absence for several agents
         * Absence is validated level 1
         * The absence can not be deleted
         */

        $acl = [6];
        $nbAgent = 3;
        $result = 'notEmpty';

        $validations = [3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test10(): void
    {
        /**
         * Agent can edit/delete its own absences (right 6)
         * Absence for several agents
         * Absence is validated level 2
         * The absence can not be deleted
         */

        $acl = [6];
        $nbAgent = 3;
        $result = 'notEmpty';

        $validations = [3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test11(): void
    {
        /**
         * Absence validation enable
         * Agent is admin level 1 (right 201)
         * Absence is not validated
         * The absence can be deleted
         */

        $this->setParam('Absences-validation', 1);

        $acl = [201];
        $nbAgent = 1;
        $validations = [0,0];
        $result = 'empty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test12(): void
    {
        /**
         * Agent is admin level 1 (right 201)
         * Absence is validated level 1
         * The absence can be deleted
         */

        $acl = [201];
        $nbAgent = 1;
        $result = 'empty';

        $validations = [3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test13(): void
    {
        /**
         * Agent is admin level 1 (right 201)
         * Absence is validated level 2
         * The absence can not be deleted
         */

        $acl = [6,201];
        $nbAgent = 1;
        $result = 'notEmpty';

        $validations = [3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test14(): void
    {
        /**
         * Agent is admin level 2 (right 501)
         * Absence is not validated
         * The absence can be deleted
         */

        $acl = [501];
        $nbAgent = 3;
        $validations = [0,0];
        $result = 'empty';

        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test15(): void
    {
        /**
         * Agent is admin level 2 (right 501)
         * Absence is validated level 1
         * The absence can not be deleted
         */

        $acl = [501];
        $nbAgent = 3;
        $result = 'empty';

        $validations = [3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,0];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test16(): void
    {
        /**
         * Agent is admin level 2 (right 501)
         * Absence for several agents
         * Absence is validated level 2
         * The absence can not be deleted
         */

        $acl = [501];
        $nbAgent = 3;
        $result = 'empty';

        $validations = [3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [-3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [3,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);

        $validations = [0,-3];
        $this->createAndTest($acl, $nbAgent, $validations, $result);
    }

    public function test17(): void
    {
        /**
         * Absences-validation disabled
         * Agent is admin level 1 (right 201)
         * Absence is not his own
         * The absence can be deleted
         */

        $this->setParam('Absences-validation', 0);

        $acl = [201];
        $nbAgent = 1;
        $result = 'empty';

        $validations = [0,1];
        $this->createAndTest($acl, $nbAgent, $validations, $result, 1);
    }

    public function test18(): void
    {
        /**
         * Absences-validation disabled
         * Agent is admin level 2 (right 501)
         * Absence is not his own
         * The absence can be deleted
         */

        $acl = [501];
        $nbAgent = 1;
        $result = 'empty';

        $validations = [0,1];
        $this->createAndTest($acl, $nbAgent, $validations, $result, 1);
    }



    private function createAndTest($acl, $nbAgents = 1, $validations = [1,1], $result = 'empty', $loggedInAgentId = 0)
    {
        $acl = array_merge($acl, [99,100]);
        $agents = $this->agents;

        if ($nbAgents > 3) {
            $nbAgents = 3;
        }

        $start = new DateTime();
        $start->modify('next monday 10:00');
        $end = new DateTime();
        $end->modify('next monday 11:00');
        $group = $nbAgents > 1 ? time() . '-' . rand(111,999) : '';

        $validation_n1 = !empty($validations['level1']) ? new DateTime() : null;
        $validation = !empty($validations['level2']) ? new DateTime() : null;

        $valide_n1 = $validations[0] ?? 0;
        $valide = $validations[1] ?? 0;

        for ($i = 0; $i < $nbAgents; $i++) {
            $absence = $this->builder->build(
                Absence::class,
                array(
                    'debut' => $start,
                    'motif' => 'Réunion',
                    'fin' => $end,
                    'perso_id' => $agents[$i]->getId(),
                    'validation_n1' => $validation_n1,
                    'valide_n1' => $valide_n1,
                    'validation' => $validation,
                    'valide' => $valide,
                    'supprime' => 0,
                    'groupe' => $group,
                )
            );
        }

        $this->logInAgent($agents[$loggedInAgentId], $acl);

        $this->client->request('DELETE', '/absence', ['CSRFToken' => $this->CSRFToken, 'id' => $absence->getId()]);

        $test = $this->entityManager->getRepository(Absence::class)->findBy(['id' => $absence->getId()]);

        if ($result == 'empty') {
            $this->assertEmpty($test, 'Absence not deleted');
        } else {
            $this->assertNotEmpty($test, 'Absence deleted');
        }
    }
}
