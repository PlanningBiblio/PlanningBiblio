<?php

namespace App\Service;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;

class MenuService
{
    public $elements = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function checkCondition($allConditions): bool
    {
        if ($allConditions != null && $allConditions != '') {

            $conditionsArray = explode('&', $allConditions);

            foreach ($conditionsArray as $condition) {
                if (substr($condition, 0, 7) == 'config=') {
                    $tmp = substr($condition, 7);
                    $values = explode(';', $tmp);
                    foreach ($values as $value) {
                        if (empty($GLOBALS['config'][$value])) {
                            return false;
                        }
                    }
                } elseif (substr($condition, 0, 8) == 'config!=') {
                    $tmp = substr($condition, 8);
                    $values = explode(';', $tmp);
                    foreach ($values as $value) {
                        if (!empty($GLOBALS['config'][$value])) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    private function fetch(): void
    {
        $menu = [];

        $repo = $this->entityManager->getRepository(Menu::class)->findBy([], ['level1' => 'ASC', 'level2' => 'ASC']);

        foreach ($repo as $elem) {
            if ($this->checkCondition($elem->getRequirement())) {
                $menu[$elem->getLevel1()][$elem->getLevel2()]['title'] = $elem->getTitle();
                $menu[$elem->getLevel1()][$elem->getLevel2()]['url'] = $elem->getUrl();
            }
        }

        if ($GLOBALS['config']['Multisites-nombre'] > 1) {
            for ($i = 0; $i < $GLOBALS['config']['Multisites-nombre']; $i++) {
                $j = $i + 1;
                $menu[30][$j]['title'] = $GLOBALS['config']["Multisites-site".$j];
                $menu[30][$j]['url'] = $j;
            }
        }

        $this->elements = $menu;
    }

    public function get(): array
    {
        $this->fetch();
        $elements = $this->elements;

        $mainMenu = [];
        $secondaryMenu = [];

        $keys = array_keys($elements);
        sort($keys);

        foreach ($keys as $key) {
            $mainMenu[] = [
                'key' => $key,
                'url' => $elements[$key][0]['url'],
                'title' => $elements[$key][0]['title'],
            ];

            $secondaryMenu[$key] = [
                'key' => $key,
                'items' => array()
            ];

            $keys2 = array_keys($elements[$key]);
            sort($keys2);
            unset($keys2[0]);

            $i = 0;
            foreach ($keys2 as $key2) {
                // Avoid displaying empty items, such as a deleted site under the planning menu.
                if (empty($elements[$key][$key2]['title'])) {
                    continue;
                }

                $secondaryMenu[$key]['items'][$i] = [
                    'key' => $key,
                    'url' => $elements[$key][$key2]['url'],
                    'title' => $elements[$key][$key2]['title'],
                ];
                $i++;
            }
        }

        return [
            'mainMenu' => $mainMenu,
            'secondaryMenu' => $secondaryMenu,
        ];
    }
}
