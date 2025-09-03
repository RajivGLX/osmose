<?php

namespace App\Repository;

use App\Entity\Availability;
use App\Entity\Center;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Availability>
 *
 * @method Availability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Availability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Availability[]    findAll()
 * @method Availability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Availability::class);
    }

    public function existsAvailability(\DateTime $date, string $slotName, int $centerId): ?object
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a')
            ->join('a.slot', 's')
            ->where("DATE_FORMAT(a.date, '%Y-%m-%d') = :date")
            ->setParameter('date', $date->format('Y-m-d'))
            ->andWhere('s.name = :slotName')
            ->setParameter('slotName', $slotName)
            ->andWhere('a.center = :center')
            ->setParameter('center', $centerId);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    public function findAllByCenterAndDay(\DateTime $date, int $centerId): ?array
    {
        $qb = $this->createQueryBuilder('a');

        $qb->select('a')
            ->where("DATE_FORMAT(a.date, '%Y-%m-%d') = :date")
            ->setParameter('date', $date->format('Y-m-d'))
            ->andWhere('a.center = :centerId')
            ->setParameter('centerId', $centerId);

        $results = $qb->getQuery()->getResult();

        return $results ?: null;
    }

    public function findAvailabilityByCenterAndMonth(Center $center, int $month): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.center = :center')
            ->setParameter('center', $center)
            ->andWhere('MONTH(a.date) = :month')
            ->setParameter('month', $month);

        return $qb->getQuery()->getResult();
    }

    public function findAvailabilityOnOneYearByCenter(int $idCenter): array
    {
        $today = new \DateTime();
        $sixMonthsLater = (clone $today)->modify('+12 months');

        $qb = $this->createQueryBuilder('a')
            ->select('a.date, a.id, a.available_place, a.reserved_place, s.id as slot_id, s.name as slot_name, COUNT(b.id) as booking_count')
            ->join('a.slot', 's')
            ->leftJoin('a.bookings', 'b')
            ->where('a.center = :centerId')
            ->setParameter('centerId', $idCenter)
            ->andWhere('a.date BETWEEN :start AND :end')
            ->setParameter('start', $today->format('Y-m-d'))
            ->setParameter('end', $sixMonthsLater->format('Y-m-d'))
            ->groupBy('a.date, a.id, s.id');

        $results = $qb->getQuery()->getResult();

        // Group results by month, day, and slot
        $groupedResults = [];
        foreach ($results as $result) {
            $date = $result['date']->format('Y-m-d');
            $month = $result['date']->format('Y/m');
            $day = $result['date']->format('Y/m/d');
            $slotName = $result['slot_name'];

            if (!isset($groupedResults[$month])) {
                $groupedResults[$month] = [];
            }
            if (!isset($groupedResults[$month][$day])) {
                $groupedResults[$month][$day] = [];
            }
            if (!isset($groupedResults[$month][$day][$slotName])) {
                $groupedResults[$month][$day][$slotName] = [
                    'qty' => $result['available_place'],
                    'check' => $result['available_place'] > 0 ? true : false,
                    'booking' => (int) $result['booking_count'],
                ];
            }
        }

        return $groupedResults;
    }

}
