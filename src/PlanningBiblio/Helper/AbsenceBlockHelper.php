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
            ->where('b.start <= :start AND b.end >= :end') // OUTSIDE
            ->orWhere('b.start <= :start AND b.end <= :end') // "ON THE LEFT"
            ->orWhere('b.start >= :start AND b.end <= :end') // INSIDE
            ->orWhere('b.start >= :start AND b.end >= :end') // "ON THE RIGHT"
            ->setParameters(array('start' => $start, 'end' => $end))
            ->getQuery();
        $result = $query->getResult();
        foreach ($result as $elem) {
            return $elem['blockscount'];
        }
        return -1;
    }
}
