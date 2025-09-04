<?php 
use App\Entity\Agent;
use App\Entity\Model;
use App\Entity\Manager;

use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

use PHPUnit\Framework\TestCase;

#class EntityValidationStatusesTest extends TestCase
class EntityValidationStatusesTest extends PLBWebTestCase
{
    use \App\Traits\EntityValidationStatuses;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }


    public function testGetStatusesParams()
    {
        $this->setUpPantherClient();
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Absences-validation', 1);
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        # descriptions
        $desc_state_0 = "Demandée";
        $meeting_reason = "Réunion";
        $meeting_reason = "Formation";

        # Logged-in user
        # Scenarios: logged in as: myself, no-rights, level1, level2, admin
        # TODO: Foreach on different agents

        # Agents
        $agent1 = $builder->build(Agent::class);
        $manager_level1_for_agent1 = $builder->build(Agent::class);
        $manager_level2_for_agent1 = $builder->build(Agent::class);

        $manager1 = new Manager();
        $manager1->setUser($agent1);
        $manager1->setLevel1(1);
        $manager1->setLevel2(0);
        $manager_level1_for_agent1->addManaged($manager1);

        $manager2 = new Manager();
        $manager2->setUser($agent1);
        $manager2->setLevel1(0);
        $manager2->setLevel2(1);
        $manager_level2_for_agent1->addManaged($manager2);



        echo "manager " .  $manager_level1_for_agent1->getId() . " agent " . $agent1->getId(); 
        $this->assertTrue($manager_level1_for_agent1->isManagerOf(array($agent1->getId())));

        #$this->logInAgent($agent1, $agent1->getACL());
        $this->login($agent1);
        $_SESSION['login_id'] = $agent1->getId();


        #$agent3 = $builder->build(Agent::class, array('login' => 'mmyers', 'droits' => array(99,100)));
        #$agent3 = $builder->build(Agent::class, array('login' => 'mmyers', 'droits' => array()));

        $agents_ids = array($agent1->getId());
        $module = 'absence';

        # MYSELF
        # CREATION
        # Logged-in as myself
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertFalse($params['show_select'], 'showing select');
        $this->assertFalse($params['show_n1'], 'Show n1');
        $this->assertFalse($params['show_n2'], 'Show n2');

        # EDITION
        # Logged-in as myself, editing an absence for myself
        $absence_id = $this->createAbsenceFor($agent1); 
#        $this->debug_absence($absence_id);
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertFalse($params['show_select'], 'showing select');
        $this->assertFalse($params['show_n1'], 'Show n1');
        $this->assertFalse($params['show_n2'], 'Show n2');


        # MANAGER LEVEL1
        $this->login($manager_level1_for_agent1);
        $_SESSION['login_id'] = $manager_level1_for_agent1->getId();
        # CREATION
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertFalse($params['show_n2'], 'Show n2');

        # EDITION
        # Logged-in as manager level1, editing an absence for agent1
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertFalse($params['show_n2'], 'Show n2');

        # MT49914: Simplified absence validation schema for meetings
        $absence_id = $this->createAbsenceFor($agent1, 0, $meeting_reason); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertFalse($params['show_n1'], 'Show n1 when editing a meeting');
        $this->assertTrue($params['show_n2'], 'Show n2');

        # MANAGER LEVEL2
        $this->login($manager_level2_for_agent1);
        $_SESSION['login_id'] = $manager_level2_for_agent1->getId();
        # CREATION
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        # EDITION
        # Logged-in as manager level2, editing an absence for agent1
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');



        # Let's change logged-in user
        /*
        $this->login($agent3);
        $this->logInAgent($agent, $agent->getACL());
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        var_dump($params);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for last');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        # TODO: This should be false
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');
        */


    }

    private function debug_absence($id) {
        $absence = new \absences();
        $absence->fetchById($id);
        print_r($absence->elements);
    }

    private function createAbsenceFor($agent, $status = 0, $motif = 'default')
    {
        $date = new DateTime('now');

        $absence = new \absences();
        $absence->debut = $date->format('Y-m-d');
        $absence->fin = $date->format('Y-m-d');
        $absence->hre_debut = '00:00:00';
        $absence->hre_fin = '23:59:59';
        $absence->perso_ids = array($agent->getId());
        $absence->commentaires = '';
        $absence->motif = $motif;
        $absence->valide = $status;
        $absence->valide_n1 = $status;
        $absence->valide_n2 = $status;
        $absence->CSRFToken = $this->CSRFToken;
        $absence->pj1 = '';
        $absence->pj2 = '';
        $absence->so = '';

        $absence->add();

        return $absence->id;
    }

}

