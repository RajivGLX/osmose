<?php

namespace App\Repository;

use App\Entity\Slots;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Slots>
 *
 * @method Slots|null find($id, $lockMode = null, $lockVersion = null)
 * @method Slots|null findOneBy(array $criteria, array $orderBy = null)
 * @method Slots[]    findAll()
 * @method Slots[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SlotsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Slots::class);
    }

    //    /**
    //     * @return Slots[] Returns an array of Slots objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Slots
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
