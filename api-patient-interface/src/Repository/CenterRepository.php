<?php

namespace App\Repository;

use App\Entity\Center;
use App\Model\SearchData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Center>
 *
 * @method Center|null find($id, $lockMode = null, $lockVersion = null)
 * @method Center|null findOneBy(array $criteria, array $orderBy = null)
 * @method Center[]    findAll()
 * @method Center[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Center::class);
    }

    public function findSearch(SearchData $searchData = null)
    {
        $query =  $this
            ->createQueryBuilder('c')
            ->select('c','r')
            ->join('c.region', 'r');

        if (!empty($searchData->query)){
            $query = $query
                ->andWhere('c.name LIKE :q')
                ->setParameter('q', "%{$searchData->query}%");
        }

        if (!empty($searchData->region)){
            $query = $query
                ->andWhere('r.id IN (:region)')
                ->setParameter('region', $searchData->region);
        }

            return $query->getQuery()->getResult();
    }

    public function findCenterForAdmin($idAdmin):array
    {
        $query = $this
            ->createQueryBuilder('c')
            ->select('c')
            ->innerJoin('c.administrator', 'a')
            ->where('a.id = :idAdmin')
            ->setParameter('idAdmin', $idAdmin);

        return $query->getQuery()->getResult();
    }

}
