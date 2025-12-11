<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Entity\AbsenceReason;

class AbsenceReasonRepository extends EntityRepository
{
    public function getRemoteWorkingDescriptions()
    {
        $entityManager = $this->getEntityManager();

        $teleworking_reasons = array();

        $absence_reasons = $entityManager->getRepository(AbsenceReason::class)
                                         ->findBy(array('teleworking' => 1));

        foreach ($absence_reasons as $reason) {
            $teleworking_reasons[] = $reason->getValue();
        }

        return $teleworking_reasons;
    }
}
