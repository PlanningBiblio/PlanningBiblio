<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use App\Model\Absence;
use App\Model\AbsenceDocument;

class AbsenceRepository extends EntityRepository
{
    public function purge($id)
    {
        $entityManager = $this->getEntityManager();
        $this->deleteAllDocuments($id);
        $absence = $entityManager->getRepository(Absence::class)->find($id);
        $entityManager->remove($absence);
        $entityManager->flush();
    }

    public function purgeAll($limit_date) {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('a')
                ->from(Absence::class, 'a')
                ->andWhere('a.fin < :limit_date')
                ->setParameter('limit_date', $limit_date);
        $results = $builder->getQuery()->getResult();

        $deleted_absences = 0;
        foreach ($results as $result) {
            $this->purge($result->id());
            $deleted_absences++;
        }
        return $deleted_absences;
    }

    public function deleteAllDocuments($id) {
        $entityManager = $this->getEntityManager();
        $absdocs = $entityManager->getRepository(AbsenceDocument::class)->findBy(['absence_id' => $id]);
        foreach ($absdocs as $absdoc) {
            $absdoc->deleteFile();
            $entityManager->remove($absdoc);
        }
        $entityManager->flush();

        $absenceDocument = new AbsenceDocument();
        if (is_dir($absenceDocument->upload_dir() . $id)) {
            rmdir($absenceDocument->upload_dir() . $id);
        }
    }
}
