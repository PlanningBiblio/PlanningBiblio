<?php
namespace App\Tests\Service;

use App\Service\ConfigManager;
use App\Repository\ConfigRepository;
use Tests\PLBWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#class ConfigManagerTest extends PLBWebTestCase
class ConfigManagerTest extends KernelTestCase
{
    public function testPasswordUpdate(): void {

        $kernel = self::bootKernel();
        $container = static::getContainer();
        $configManager = $container->get(ConfigManager::class);        
        $repository = $container->get(ConfigRepository::class);
        #$configManager = self::getContainer()->get(ConfigManager::class, true);

        $repository->setParam('LDAP-Password',  'current_encrypted_password');

        $params = array('LDAP-Password' => '', 'technical' => 1);

        $error = $configManager->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('current_encrypted_password', $repository->getParam('LDAP-Password'), 'Password has not been updated');

        $params = array('LDAP-Password' => 'NewPassword', 'technical' => 1);
        $configManager->saveConfig($params);

        $this->assertEquals('', $error, 'No error has been returned');
        $this->assertEquals('NewPassword', $repository->getParam('LDAP-Password'), 'Password has been updated');

    }

}
