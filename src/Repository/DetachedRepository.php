<?php

namespace App\Repository;

use App\Entity\Detached;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;

class DetachedRepository extends EntityRepository
{
    public function findUserIds(string|DateTimeInterface $dateInput): array
    {
        if (is_string($dateInput)) {
            $date = new DateTime($dateInput);
        } else {
            $date = clone $dateInput;
        }

        $date->modify('monday this week')->format('Y-m-d');

        return $this->createQueryBuilder('d')
            ->select('d.perso_id')
            ->where('d.date = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
