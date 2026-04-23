<?php

use App\Planno\Helper\ConfigHelper;
use Tests\PLBWebTestCase;

class ConfigHelperTest extends PLBWebTestCase
{
    public function testPasswordUpdate(): void
    {

        $configHelper = new ConfigHelper();

        $configHelper->setParam('LDAP-Password', 'current_encrypted_password', 1);

        $params = array('LDAP-Password' => '', 'technical' => 1);
        $error = $configHelper->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('current_encrypted_password', $configHelper->findOneByName('LDAP-Password')->getValue(), 'Password has not been updated (empty password)');

        $params = array('LDAP-Password' => 'NewPassword', 'technical' => 1);
        $configHelper->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertNotEquals('current_encrypted_password', $configHelper->findOneByName('LDAP-Password')->getValue(), 'Password is not current_encrypted_password');
        $this->assertNotEquals('NewPassword', $configHelper->findOneByName('LDAP-Password')->getValue(), 'Password is not NewPassword');
        $this->assertNotEquals('', $configHelper->findOneByName('LDAP-Password')->getValue(), 'Password is not empty');

    }

}
