<?php 
use App\Entity\AbsenceReason;
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
        $desc_state_1 = "Acceptée";
        $desc_state_2 = "Acceptée (En attente de validation hiérarchique)";
        $workflow_b_reason = 'Reason with workflow B';

        # Absence reason with workflow B
        $ar = new AbsenceReason();
        $ar->setValue($workflow_b_reason);
        $ar->setRank(1);
        $ar->setType(1);
        $ar->setTeleworking(0);
        $ar->setNotificationWorkflow('B');
        $this->entityManager->persist($ar);

        # Logged-in user
        # Scenarios: logged in as: myself, no-rights, level1, level2, admin
        # TODO: Foreach on different agents

        # Agents
        $agent1 = $builder->build(Agent::class);
        $manager_level1_for_agent1 = $builder->build(Agent::class);
        $manager_level2_for_agent1 = $builder->build(Agent::class);
        $manager_level1_level2_for_agent1 = $builder->build(Agent::class);

        $managed1 = new Manager();
        $managed1->setUser($agent1);
        $managed1->setLevel1(1);
        $managed1->setLevel2(0);
        $manager_level1_for_agent1->addManaged($managed1);

        $managed2 = new Manager();
        $managed2->setUser($agent1);
        $managed2->setLevel1(0);
        $managed2->setLevel2(1);
        $manager_level2_for_agent1->addManaged($managed2);

        $managed3 = new Manager();
        $managed3->setUser($agent1);
        $managed3->setLevel1(1);
        $managed3->setLevel2(1);
        $manager_level1_level2_for_agent1->addManaged($managed3);


        $this->assertTrue( $manager_level1_for_agent1->isManagerOf(array($agent1->getId()), 'level1'));
        $this->assertFalse($manager_level1_for_agent1->isManagerOf(array($agent1->getId()), 'level2'));

        $this->assertFalse($manager_level2_for_agent1->isManagerOf(array($agent1->getId()), 'level1'));
        $this->assertTrue( $manager_level2_for_agent1->isManagerOf(array($agent1->getId()), 'level2'));

        $this->assertTrue($manager_level1_level2_for_agent1->isManagerOf(array($agent1->getId()), 'level1'));
        $this->assertTrue($manager_level1_level2_for_agent1->isManagerOf(array($agent1->getId()), 'level2'));




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

        # Logged-in as manager level1, editing an absence for agent1
        $absence_id = $this->createAbsenceFor($agent1, 2); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_2, $params['entity_state_desc'], 'entity state description is Accepted (waiting for hierarchy approval)');
        $this->assertEquals(2, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertFalse($params['show_n2'], 'Show n2');

        # MT49914: Simplified absence validation schema with workflow B
        $absence_id = $this->createAbsenceFor($agent1, 1, $workflow_b_reason); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id, 'B');
        $this->assertEquals($desc_state_1, $params['entity_state_desc'], 'entity state description is Accepted');
        $this->assertEquals(1, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertFalse($params['show_n1'], 'Don\'t show n1 when editing a workflow B absence');
        $this->assertTrue($params['show_n2'], 'Show n2');

        # MANAGER LEVEL2
        $this->login($manager_level2_for_agent1);
        $_SESSION['login_id'] = $manager_level2_for_agent1->getId();

        # CREATION
        $this->setParam('Absences-Validation-N2', 1);
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertFalse($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        $this->setParam('Absences-Validation-N2', 0);
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        # EDITION
        # Logged-in as manager level2, editing an absence for agent1
        $this->setParam('Absences-Validation-N2', 1);
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertFalse($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        $this->setParam('Absences-Validation-N2', 0);
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        # MANAGER LEVEL1 & LEVEL2
        $this->login($manager_level1_level2_for_agent1);
        $_SESSION['login_id'] = $manager_level1_level2_for_agent1->getId();

        # CREATION
        $this->setParam('Absences-Validation-N2', 1);
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        $this->setParam('Absences-Validation-N2', 0);
        $params = $this->getStatusesParams($agents_ids, $module);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        # EDITION
        # Logged-in as manager level 1 & 2, editing an absence for agent1
        $this->setParam('Absences-Validation-N2', 1);
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');

        $this->setParam('Absences-Validation-N2', 0);
        $absence_id = $this->createAbsenceFor($agent1); 
        $params = $this->getStatusesParams($agents_ids, $module, $absence_id);
        $this->assertEquals($desc_state_0, $params['entity_state_desc'], 'entity state description is Asked for');
        $this->assertEquals(0, $params['entity_state'], 'entity state');
        $this->assertTrue($params['show_select'], 'showing select');
        $this->assertTrue($params['show_n1'], 'Show n1');
        $this->assertTrue($params['show_n2'], 'Show n2');




        # Once valid n1:
        # Logged-in as manager level2, editing a valid n1 absence for agent1 without Workflow B

        # Logged-in as manager level2, editing a valid n1 absence for agent1 with Workflow B


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

