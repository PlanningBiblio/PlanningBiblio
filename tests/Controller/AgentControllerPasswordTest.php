<?php
use App\Model\Agent;
use App\Model\Holiday;
use App\Model\Absence;
use App\Model\Position;
use App\Model\PlanningPosition;

use App\PlanningBiblio\WorkingHours;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;


class AgentControllerPasswordTest extends PLBWebTestCase
{

    protected $builder;
    protected $entityManager;
    protected $CSRFToken;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;

        $this->builder = new FixtureBuilder();
        $this->builder->delete(Agent::class);

        $this->entityManager = $entityManager;
    }

    protected function setParam($name, $value)
    {
        $GLOBALS['config'][$name] = $value;
        $param = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $name]);

        $param->valeur($value);
        $this->entityManager->persist($param);
        $this->entityManager->flush();
    }

    public function testPasswordChangeWithCSRFOk()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);


        $clear = "Password_1";
        $crypted = password_hash($clear, PASSWORD_BCRYPT);

        $crypted2 = password_hash('Password_changed2', PASSWORD_BCRYPT);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'agent_password', 'supprime' => 0,'password' => $crypted,
        ));

        $this->logInAgent($agent, array(100,99,401,601));

        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('');

        $crawler = $this->client->request('POST', '/ajax/change-own-password', array(
            'current_password' => 'Password_1',
            'password' => 'Password_changed2',
            '_token' => $token,
        ));

        $agent = $entityManager->find(Agent::class, $agent->id());
        $this->assertTrue(password_verify('Password_changed2', $agent->password()));

        $result = $crawler->filterXPath('//p');
        $this->assertEquals($result->text('Node does not exist', false), 'Password successfully changed');

    }


    public function testPasswordChangeWithFakeCSRF()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);


        $clear = "Password_1";
        $crypted = password_hash($clear, PASSWORD_BCRYPT);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'agent_password', 'supprime' => 0,'password' => $crypted,
        ));

        $this->logInAgent($agent, array(100,99,401,601));

        $crawler = $this->client->request('POST', '/ajax/change-own-password', array(
            'current_password' => 'Password_1',
            'password' => 'Password_changed2',
            '_token' => 'fake_token',
        ));

        $agent = $entityManager->find(Agent::class, $agent->id());
        $this->assertFalse(password_verify('Password_changed2', $agent->password()));
        $this->assertTrue(password_verify('Password_1', $agent->password()));

    }
}
