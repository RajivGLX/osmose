<?php

namespace App\Repository;

use App\Entity\Administrator;
use App\Entity\Center;
use App\Entity\User;
use App\Services\Identifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Administrator>
 *
 * @method Administrator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Administrator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Administrator[]    findAll()
 * @method Administrator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdministratorRepository extends ServiceEntityRepository
{
    private $identifier;
    public function __construct(ManagerRegistry $registry, Identifier $identifier)
    {
        parent::__construct($registry, Administrator::class);
        $this->identifier = $identifier;
    }

    public function findAdminsBySuperAdmin(UserInterface $superAdmin): array
    {
        $qb = $this->createQueryBuilder('a');

        // Jointure avec la table "User" pour les "rol_admin"
        $qb->innerJoin('a.user', 'u');
        // Jointure avec la table "Administrator" pour le lien entre les utilisateurs et les centres
        $qb->innerJoin('u.administrator', 'admin');
        // Jointure avec la table "Centre" pour les centres liés à l'administrateur
        $qb->innerJoin('admin.centers', 'c');
        // Filtre par les centres de l'utilisateur actuel
        $qb->where('c.id IN (:centreIds)');
        $qb->setParameter('centreIds', $superAdmin->getAdministrator()->getCenters()->map(fn ($centre) => $centre->getId())->toArray());

        return $qb->getQuery()->getResult();


    }

    public function findAdminByOneCenterAndUserId(UserInterface $user, Center $center)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->innerJoin('a.user', 'u');
        $qb->innerJoin('u.administrator', 'admin');
        $qb->innerJoin('admin.centers', 'c');
        $qb->where('u.id = :userId');
        $qb->setParameter('userId', $user->getId());
        $qb->andWhere('c.id = :centerId');
        $qb->setParameter('centerId', $center->getId());

        return $qb->getQuery()->getOneOrNullResult();

    }

    public function getCentersAdmin(User $user, $arrayId = false): array
    {
        $userAdmin = $this->findOneBy(['user' => $user]);
        if ($this->identifier->isAdminDialyzone($user)) {
            $centerOfSuperAdmin = [];
            return $centerOfSuperAdmin;
        }else{
            $centerOfSuperAdmin = $userAdmin->getCenters()->toArray();
            if ($arrayId &&  $centerOfSuperAdmin != null){
                foreach ($centerOfSuperAdmin as $idCenter){
                    $centersIdAdmin[] = $idCenter->getId();
                }
                return $centersIdAdmin;
            }

            return $centerOfSuperAdmin;
        }

    }

//    /**
//     * @return Administrator[] Returns an array of Administrator objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Administrator
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
