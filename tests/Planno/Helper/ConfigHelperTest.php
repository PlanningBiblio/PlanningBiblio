<?php

use App\Repository\ConfigRepository;
use App\Entity\Config;
use App\Planno\Helper\ConfigHelper;
use Tests\PLBWebTestCase;

class ConfigHelperTest extends PLBWebTestCase
{
    public function testPasswordUpdate(): void {

        $entityManager = $this->entityManager;
        #TODO: Use $this
        $helper        = new ConfigHelper();

        $this->config->setParam('LDAP-Password', 'current_encrypted_password', 1);

        $params = array('LDAP-Password' => '', 'technical' => 1);
        $error = $helper->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('current_encrypted_password', $this->config->getValue('LDAP-Password'), 'Password has not been updated (empty password)');

        $params = array('LDAP-Password' => 'NewPassword', 'technical' => 1);
        $helper->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertNotEquals('current_encrypted_password', $this->config->getValue('LDAP-Password'), 'Password is not current_encrypted_password');
        $this->assertNotEquals('NewPassword',                $this->config->getValue('LDAP-Password'), 'Password is not NewPassword');
        $this->assertNotEquals('',                           $this->config->getValue('LDAP-Password'), 'Password is not empty');

    }

    public function testDefaultValues(): void
    {
        $this->config->setParam('Mail-IsEnabled', '1');
        $this->config->setParam('Mail-Signature', 'Ce mail a été envoyé par Planno');
        $this->config->setParam('Absences-notifications-A1', json_encode([0,1,2,3]));

        $helper = new ConfigHelper();
        $helper->saveConfig(['technical' => 0]);

        $this->assertEquals('0', $this->config->getValue('Mail-IsEnabled'));
        $this->assertEquals('', $this->config->getValue('Mail-Signature'));
        $this->assertEquals('[]', $this->config->getValue('Absences-notifications-A1'));
    }
}
