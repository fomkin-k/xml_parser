<?php

namespace App\Repository;

use App\Entity\XMLElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<XMLElement>
 *
 * @method XMLElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method XMLElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method XMLElement[]    findAll()
 * @method XMLElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class XMLElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, XMLElement::class);
    }

   public function findElementsById($elem_id): array
   {
       return $this->createQueryBuilder('x')
           ->andWhere('x.element_id = :val')
           ->setParameter('val', $elem_id)
           ->orderBy('x.element_id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

   public function findElementsByParentId($parent_id): array
   {
       return $this->createQueryBuilder('x')
           ->andWhere('x.parent_id = :val')
           ->setParameter('val', $parent_id)
           ->orderBy('x.parent_id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

//    /**
//     * @return XMLElement[] Returns an array of XMLElement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('x')
//            ->andWhere('x.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('x.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?XMLElement
//    {
//        return $this->createQueryBuilder('x')
//            ->andWhere('x.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

/* сначала идут элементы с нулевым element_id потом элементы, у которых в element_id
входит id предыдущего */
