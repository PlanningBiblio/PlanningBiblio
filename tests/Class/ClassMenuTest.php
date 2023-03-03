<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../public/include/class.menu.php');

class ClassMenuTest extends TestCase
{
    public function testMenuContent()
    {
        $menu = new menu();
        $this->assertEquals($menu->checkCondition('random string'), false, 'random string is false');
        $this->assertEquals($menu->checkCondition(null), true, 'null condition is true');
        $this->assertEquals($menu->checkCondition(''), true, 'empty condition is true');

        $GLOBALS['config']['Conges-Enable'] = 0;
        $this->assertEquals($menu->checkCondition('config=Conges-Enable'), false, 'single equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable'), true, 'single not equal condition');

        $GLOBALS['config']['Conges-Enable'] = 1;
        $this->assertEquals($menu->checkCondition('config=Conges-Enable'), true, 'single equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable'), false, 'single not equal condition');

        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Recuperations'] = 0;
        $this->assertEquals($menu->checkCondition('config=Conges-Enable;Conges-Recuperations'), false, 'multiple equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable;Conges-Recuperations'), false, 'multiple not equal condition');

        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Recuperations'] = 1;
        $this->assertEquals($menu->checkCondition('config=Conges-Enable;Conges-Recuperations'), true, 'multiple equal condition');

        $GLOBALS['config']['Conges-Enable'] = 0;
        $GLOBALS['config']['Conges-Recuperations'] = 0;
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable;Conges-Recuperations'), true, 'multiple not equal condition');
    }
}
