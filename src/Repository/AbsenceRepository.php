<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use App\Entity\Absence;
use App\Entity\AbsenceDocument;

class AbsenceRepository extends EntityRepository
{
    private function purge($id)
    {
        $entityManager = $this->getEntityManager();
        $this->deleteAllDocuments($id, false);
        $absence = $entityManager->getRepository(Absence::class)->find($id);
        $entityManager->remove($absence);
        /* If this function was to be made public, we probably would want to add a
           parameter to flush here (like for deleteAllDocuments)
           ie: called from purgeAll: don't flush (flush is done in purgeAll)
               called from the outside: flush
        */
    }

    public function deleteAllDocuments($id, bool $flush = true) {
        $entityManager = $this->getEntityManager();
        $absdocs = $entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);
        foreach ($absdocs as $absdoc) {
            $absdoc->deleteFile();
            $entityManager->remove($absdoc);
        }
        if ($flush == true) {
            $entityManager->flush();
        }

        $absenceDocument = new AbsenceDocument();
        if (is_dir($absenceDocument->upload_dir() . $id)) {
            rmdir($absenceDocument->upload_dir() . $id);
        }
    }

    public function findIcalKeysAfterEnd(string $end, string $calName): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.ical_key')
            ->where('a.cal_name = :cal_name')
            ->andWhere('a.fin > :end')
            ->setParameter('cal_name', $calName)
            ->setParameter('end', $end);

        return $qb->getQuery()->getScalarResult();
    }

    public function getByUserIds(array $userIds, string $calName): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.perso_id IN (:userIds)')
            ->andWhere('a.cal_name = :cal_name')
            ->setParameter('cal_name', $calName)
            ->setParameter('userIds', $userIds);

        return $qb->getQuery()->getResult();
    }

    public function purgeAll($limit_date) 
    {
        $entityManager = $this->getEntityManager();
        $builder = $entityManager->createQueryBuilder();
        $builder->select('a')
                ->from(Absence::class, 'a')
                ->andWhere('a.fin < :limit_date')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();

        $deleted_absences = 0;
        foreach ($results as $result) {
            $this->purge($result->getId());
            $deleted_absences++;
        }
        $entityManager->flush();
        return $deleted_absences;
    }
}
