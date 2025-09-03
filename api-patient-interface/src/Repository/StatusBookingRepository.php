<?php

namespace App\Repository;

use App\Entity\StatusBooking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatusBooking>
 *
 * @method StatusBooking|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatusBooking|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatusBooking[]    findAll()
 * @method StatusBooking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusBookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusBooking::class);
    }

    //    /**
    //     * @return StatusBooking[] Returns an array of StatusBooking objects
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

    //    public function findOneBySomeField($value): ?StatusBooking
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
