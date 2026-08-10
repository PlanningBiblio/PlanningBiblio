<?php

use PHPUnit\Framework\TestCase;
use App\Planno\Menu;
use App\Entity\Site;

class ClassMenuTest extends TestCase
{
    public function testMenuContent(): void
    {
        $menu = new Menu();
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
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable&config=Conges-Recuperations'), false, 'both equal and not equal single conditions');
        $this->assertEquals($menu->checkCondition('config=Conges-Enable&config!=Conges-Recuperations'), true, 'both equal and not equal single conditions');

        $GLOBALS['config']['Conges-Enable'] = 1;
        $GLOBALS['config']['Conges-Recuperations'] = 1;
        $this->assertEquals($menu->checkCondition('config=Conges-Enable;Conges-Recuperations'), true, 'multiple equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable;Conges-Recuperations'), false, 'multiple not equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable&config=Conges-Recuperations'), false, 'both equal and not equal single conditions');
        $this->assertEquals($menu->checkCondition('config=Conges-Enable&config!=Conges-Recuperations'), false, 'both equal and not equal single conditions');

        $GLOBALS['config']['Conges-Enable'] = 0;
        $GLOBALS['config']['Conges-Recuperations'] = 0;
        $this->assertEquals($menu->checkCondition('config=Conges-Enable;Conges-Recuperations'), false, 'multiple equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable;Conges-Recuperations'), true, 'multiple not equal condition');
        $this->assertEquals($menu->checkCondition('config!=Conges-Enable&config=Conges-Recuperations'), false, 'both equal and not equal single conditions');
        $this->assertEquals($menu->checkCondition('config=Conges-Enable&config!=Conges-Recuperations'), false, 'both equal and not equal single conditions');

        // Check Sites display under Planning Menu
        global $entityManager;

        $site2 = new Site();
        $site2->setName('Site 2');
        $site3 = new Site();
        $site3->setName('Site 3');

        $entityManager->persist($site2);
        $entityManager->persist($site3);
        $entityManager->flush();

        $menu = new Menu();
        $result = $menu->get();

        $this->assertEquals(3, count($result['menu_js'][30]['items']), "Planning menu should count 3 entries.");
        $this->assertEquals('Site par défaut', $result['menu_js'][30]['items'][0]['title'], "Planning menu 1 title should be 'Site par défaut'.");
        $this->assertEquals('Site 2', $result['menu_js'][30]['items'][1]['title'], "Planning menu 2 title should be 'Site 2'.");
        $this->assertEquals('Site 3', $result['menu_js'][30]['items'][2]['title'], "Planning menu 3 title should be 'Site 3'.");

        $site2->setDeletedDate(new \DateTime());
        $entityManager->flush();

        $menu = new Menu();
        $result = $menu->get();

        $this->assertEquals(2, count($result['menu_js'][30]['items']), "Planning menu should count 2 entries.");
        $this->assertEquals('Site par défaut', $result['menu_js'][30]['items'][0]['title'], "Planning menu 1 title should be 'Site par défaut'.");
        $this->assertEquals('Site 3', $result['menu_js'][30]['items'][1]['title'], "Planning menu 2 title should be 'Site 3'.");
    }

    public static function tearDownAfterClass(): void
    {
        global $entityManager;

        if ($entityManager) {
            $siteRepository = $entityManager->getRepository(\App\Entity\Site::class);
            $sitesToDelete = $siteRepository->findBy(['name' => ['Site 2', 'Site 3']]);
            foreach ($sitesToDelete as $site) {
                $entityManager->remove($site);
            }
            $entityManager->flush();
        }
    }

}
