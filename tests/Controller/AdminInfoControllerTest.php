<?php

use App\Model\Agent;
use App\Model\AdminInfo;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;



class AdminInfoControllerTest extends PLBWebTestCase
{
    public function testAdd()
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AdminInfo::class);
        

        $this->logInAgent($agent, array(23));

        $client = static::createClient();
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('csrf');

        $client->request('POST', '/admin/info', array('start' => '05/10/2012', 'end' => '10/10/2012', 'text' => 'salut', '_token' => $token));
        

        $info = $entityManager->getRepository(AdminInfo::class)->findOneBy(array('debut' => '20121005', 'fin' => '20121010'));


        $this->assertEquals('salut', $info->texte(), 'info texte is salut');
        
        $this->assertEquals('20121005', $info->debut(), 'debut is 20121005');
        $this->assertEquals('20121010', $info->fin(), 'fin is 20121010');
    

    }
}