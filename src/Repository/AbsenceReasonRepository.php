<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Model\AbsenceReason;

class AbsenceReasonRepository extends EntityRepository
{
    public function getRemoteWorkingDescriptions()
    {
        $entityManager = $this->getEntityManager();

        $teleworking_reasons = array();

        $absence_reasons = $entityManager->getRepository(AbsenceReason::class)
                                         ->findBy(array('teleworking' => 1));

        foreach ($absence_reasons as $reason) {
            $teleworking_reasons[] = $reason->valeur();
        }

        return $teleworking_reasons;
    }
    
    public function getCumulativeReasons()
    {
        $entityManager = $this->getEntityManager();
        
        $cumulative_reasons = array();
        
        $absence_reasons = $entityManager->getRepository(AbsenceReason::class)
                                         ->findBy(array('absence_cumulee' => 1));
        
        foreach ($absence_reasons as $reason) {
            $cumulative_reasons[] = $reason->valeur();
        }
        
        return $cumulative_reasons;
    }
}
