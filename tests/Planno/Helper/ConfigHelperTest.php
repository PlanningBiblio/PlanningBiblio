<?php

use App\Planno\Helper\ConfigHelper;
use Tests\PLBWebTestCase;

class ConfigHelperTest extends PLBWebTestCase
{
    public function testPasswordUpdate(): void {
        $this->config->setParam('LDAP-Password', 'current_encrypted_password');

        $params = array('LDAP-Password' => '', 'technical' => 1);
        $error = $this->config->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('current_encrypted_password', $this->config->findOneByName('LDAP-Password')->getValue(), 'Password has not been updated (empty password)');

        $params = array('LDAP-Password' => 'NewPassword', 'technical' => 1);
        $this->config->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertNotEquals('current_encrypted_password', $this->config->findOneByName('LDAP-Password')->getValue(), 'Password is not current_encrypted_password');
        $this->assertNotEquals('NewPassword',                $this->config->findOneByName('LDAP-Password')->getValue(), 'Password is not NewPassword');
        $this->assertNotEquals('',                           $this->config->findOneByName('LDAP-Password')->getValue(), 'Password is not empty');

    }

}
