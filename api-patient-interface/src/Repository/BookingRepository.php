<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Center;
use App\Entity\Patient;
use App\Entity\Status;
use App\Entity\StatusBooking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 *
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findBookingsByCenterAndMonth(Center $center, int $month): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.center = :center')
            ->setParameter('center', $center)
            ->andWhere('MONTH(b.dateReserve) = :month')
            ->setParameter('month', $month);

        return $qb->getQuery()->getResult();
    }

    public function findBookingsByCenterIdAndMonth(int $idCenter, \DateTime $date): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.center = :centerId')
            ->setParameter('centerId', $idCenter)
            ->andWhere('b.dateReserve BETWEEN :start AND :end')
            ->setParameter('start', $date->format('Y-m-01'))
            ->setParameter('end', $date->format('Y-m-t'));

        return $qb->getQuery()->getResult();
    }

    public function getAllBookingsByCenterId(int $idCenter): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.center = :centerId')
            ->setParameter('centerId', $idCenter);

        return $qb->getQuery()->getResult();
    }

    public function findBookingsByPatientAndMonth(Patient $patient, int $month): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.patient = :patient')
            ->setParameter('patient', $patient)
            ->andWhere('MONTH(b.dateReserve) = :month')
            ->setParameter('month', $month);

        return $qb->getQuery()->getResult();
    }

    public function findFutureBookingsByPatient(Patient $patient): array
    {
        $dateNow = new \DateTime();
        $qb = $this->createQueryBuilder('b')
            ->where('b.patient = :patient')
            ->setParameter('patient', $patient)
            ->andWhere('b.dateReserve > :dateNow')
            ->setParameter('dateNow', $dateNow);

        return $qb->getQuery()->getResult();
    }

    public function findFuturBookingsByCenter($center): array
    {
        $dateNow = new \DateTime();
        $qb = $this->createQueryBuilder('b')
            ->where('b.center = :center')
            ->setParameter('center', $center)
            ->andWhere('b.dateReserve > :dateNow')
            ->setParameter('dateNow', $dateNow);

        return $qb->getQuery()->getResult();
    }

    public function findOldBookingsByPatient(Patient $patient): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.patient = :patient')
            ->setParameter('patient', $patient)
            ->andWhere('b.dateReserve < :dateNow')
            ->setParameter('dateNow', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    public function findPastBookingsByCenter($center): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.center = :center')
            ->setParameter('center', $center)
            ->andWhere('b.dateReserve < :dateNow')
            ->setParameter('dateNow', new \DateTime());

        return $qb->getQuery()->getResult();
    }

    public function findAllBookingsByPatientAndCenter(Patient $patient, Center $center): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.patient = :patient')
            ->setParameter('patient', $patient)
            ->andWhere('b.center = :center')
            ->setParameter('center', $center);

        return $qb->getQuery()->getResult();
    }

    public function findAllBookingsByDateReserveBeforeNow(): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.dateReserve < :now')
            ->setParameter('now', new \DateTime(), \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE);

        return $qb->getQuery()->getResult();
    }

    public function countBookingsByStatusAndPatient(Status $status, Patient $patient)
    {
        $dateNow = new \DateTime();
        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->innerJoin('b.status', 's')
            ->where('s.id = :id')
            ->setParameter('id', $status->getId())
            ->andWhere('b.patient = :patient')
            ->setParameter('patient', $patient)
            ->andWhere('b.dateReserve > :dateNow')
            ->setParameter('dateNow', $dateNow);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getBookingCountsByStatus(array $allStatuses, Patient $patient): array
    {
        foreach ($allStatuses as $status) {
            $bookingsByStatusAndPatient[$status->getSlug()]['count'] = 0;
            $bookingsByStatusAndPatient[$status->getSlug()]['name'] = $status->getName();
        }

        $qb = $this->createQueryBuilder('b')
            ->select('s.slug, COUNT(b.id)')
            ->innerJoin('b.statuses', 's')
            ->where('b.patient = :patient')
            ->andWhere('b.dateReserve > :today')
            ->setParameter('patient', $patient)
            ->setParameter('today', new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
            ->groupBy('s.slug');

        $query = $qb->getQuery();
        $results = $query->getResult();
        foreach ($results as $result) {
            $bookingsByStatusAndPatient[$result['slug']]['count'] = $result[1]; // Update count if found
        }

        return $bookingsByStatusAndPatient;
    }

    public function findBookingByCenter($center)
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.center = :center')
            ->setParameter('center', $center);

        return $qb->getQuery()->getResult();
    }

    public function findBookingByArrayIdBooking(array $idBookings): array
    {
        $qb = $this->createQueryBuilder('b')
            ->where('b.id IN (:idBookings)')
            ->setParameter('idBookings', $idBookings);

        return $qb->getQuery()->getResult();
    }
}
