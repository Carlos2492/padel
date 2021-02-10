<?php

namespace App\Repository;

use App\Entity\Horas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Horas|null find($id, $lockMode = null, $lockVersion = null)
 * @method Horas|null findOneBy(array $criteria, array $orderBy = null)
 * @method Horas[]    findAll()
 * @method Horas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HorasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Horas::class);
    }

    // /**
    //  * @return Horas[] Returns an array of Horas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Horas
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
