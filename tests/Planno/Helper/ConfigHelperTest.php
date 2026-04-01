<?php

use App\Repository\ConfigRepository;
use App\Entity\Config;
use App\Planno\Helper\ConfigHelper;
use Tests\PLBWebTestCase;

class ConfigHelperTest extends PLBWebTestCase
{
    public function testPasswordUpdate(): void {

        $entityManager = $this->entityManager;
        $repository    = $entityManager->getRepository(Config::class);
        $helper        = new ConfigHelper();

        $repository->setParam('LDAP-Password', 'current_encrypted_password', 1);

        $params = array('LDAP-Password' => '', 'technical' => 1);
        $error = $helper->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('current_encrypted_password', $repository->getValue('LDAP-Password'), 'Password has not been updated (empty password)');

        $params = array('LDAP-Password' => 'NewPassword', 'technical' => 1);
        $helper->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertNotEquals('current_encrypted_password', $repository->getValue('LDAP-Password'), 'Password is not current_encrypted_password');
        $this->assertNotEquals('NewPassword',                $repository->getValue('LDAP-Password'), 'Password is not NewPassword');
        $this->assertNotEquals('',                           $repository->getValue('LDAP-Password'), 'Password is not empty');

    }

}
