<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExchangeRate;
use App\ValueObject\BankEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry, ExchangeRate::class);
    }

    public function getRatesForCurrentDate(BankEnum $src): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from(ExchangeRate::class, 'e');

        $qb->andWhere(
            $qb->expr()->eq('e.src', $qb->expr()->literal($src->getValue()))
        );

        $qb->andWhere(
            $qb->expr()->between(
                'e.datetime',
                $qb->expr()->literal(date('Y-m-d 00:00:00')),
                $qb->expr()->literal(date('Y-m-d 23:59:59')))
        );

        return $qb->getQuery()->execute();
    }
}
