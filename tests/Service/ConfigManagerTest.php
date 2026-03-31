<?php
namespace App\Tests\Service;

use App\Service\ConfigManager;
use App\Repository\ConfigRepository;
use Tests\PLBWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigManagerTest extends KernelTestCase
{
    public function testPasswordUpdate(): void {

        $kernel = self::bootKernel();
        $container = static::getContainer();
        $configManager = $container->get(ConfigManager::class);
        $repository = $container->get(ConfigRepository::class);

        $repository->setParam('LDAP-Password', 'current_encrypted_password', 1);

        $params = array('LDAP-Password' => '', 'technical' => 1);
        $error = $configManager->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('current_encrypted_password', $repository->getParamValue('LDAP-Password'), 'Password has not been updated (empty password)');

        $params = array('LDAP-Password' => 'NewPassword', 'technical' => 1);
        $configManager->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertNotEquals('current_encrypted_password', $repository->getParamValue('LDAP-Password'), 'Password is not current_encrypted_password');
        $this->assertNotEquals('NewPassword',                $repository->getParamValue('LDAP-Password'), 'Password is not NewPassword');
        $this->assertNotEquals('',                           $repository->getParamValue('LDAP-Password'), 'Password is not empty');

    }

}
