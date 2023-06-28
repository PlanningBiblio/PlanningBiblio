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
        $result = $queryBuilder->select('COUNT(b.id)')
            ->from(AbsenceBlock::class,'b')
            ->where('b.start > :end')
            ->orWhere('b.end < :start')
            ->setParameters(array('start' => $start, 'end' => $end))
            ->getQuery()
            ->getResult();

        if ($result) {
            if ($result[0] >= 1) {
                return 0;
            } else {
                return 1;
            }
        }
        return -1;
    }
}
