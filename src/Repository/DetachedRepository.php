<?php

namespace App\Repository;

use App\Entity\Detached;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;

class DetachedRepository extends EntityRepository
{
    public function findUserIds(string|DateTimeInterface $dateInput): array
    {
        if (is_string($dateInput)) {
            $date = new DateTimeImmutable($dateInput);
        } else {
            $date = DateTimeImmutable::createFromInterface($dateInput);
        }

        $targetDate = $date->modify('monday this week')->setTime(0, 0, 0);

        return $this->createQueryBuilder('d')
            ->select('d.userId')
            ->where('d.date = :date')
            ->setParameter('date', $targetDate)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
