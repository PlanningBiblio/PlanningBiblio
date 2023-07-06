<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

use App\Model\AbsenceBlock;

class AbsenceBlockHelper extends BaseHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function hasBlock($start, $end)
    {
        if (!$start || !$end) {
            return -1;
        }

        $queryBuilder = $this->entityManager->createQueryBuilder();

        $query = $queryBuilder->select('COUNT(b.id) AS blockscount')
            ->from(AbsenceBlock::class,'b')
            ->where('b.start <= :end AND b.end >= :start')
            ->setParameters(array('start' => $start, 'end' => $end))
            ->getQuery();

        $result = $query->getResult();

        if (!empty($result)) {
            return $result[0]['blockscount'];
        }

        return -1;
    }
}
