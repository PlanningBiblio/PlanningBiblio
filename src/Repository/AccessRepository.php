<?php

namespace App\Repository;

use App\Entity\Access;
use Doctrine\ORM\EntityRepository;

class AccessRepository extends EntityRepository
{
    /**
     * Find access filtered by group id.
     *
     * This method returns access whose "groupe_id" value donesn't equal 99 or 100
     * grouped by its label(column "groupe").
     * 
     * and dispatch groups by sites
     * 
     * @return array List of matching access
     */
    public function getAccessGroups($numberOfSites = 1): array
    {
        $result =  $this->createQueryBuilder('a')
            ->select('a.groupe_id, a.groupe, a.categorie, a.ordre')
            ->where('a.groupe_id NOT IN (99, 100)')
            ->groupBy('a.groupe')
            ->getQuery()
            ->getArrayResult();

        foreach ($result as $i => $elem) { 
            if (empty($elem['categorie'])) {
                $result[$i]['categorie'] = 'Divers';
                $result[$i]['ordre'] = 200;
            }
        }

        $groups = [];
        foreach ($result as $elem) {
            $groups[$elem['groupe_id']] = $elem;
        }

        uasort($groups, ['self', 'cmp']);

        // Si multisites, les droits de gestion des absences,
        // congés et modification planning dépendent des sites :
        // on les places dans un autre tableau pour simplifier l'affichage
        $groupsBySite = [];

        if ($numberOfSites > 1) {
            for ($i = 2; $i <= 10; $i++) {

                // Exception, groupe 701 = pas de gestion multisites (pour le moment)
                if ($i == 7) {
                    continue;
                }

                $group = ($i * 100) + 1 ;
                if (array_key_exists($group, $groups)) {
                    $groupsBySite[] = $groups[$group];
                    unset($groups[$group]);
                }
            }
        }

        uasort($groupsBySite, ['self', 'cmp']);

        return [
            'accessGroups' => $groups,
            'accessgroupsBySite' => $groupsBySite
        ];
    }

    private static function cmp($a, $b): int
    {
        return ($a['ordre'] > $b['ordre']) ? 1 : -1;
    }
}
