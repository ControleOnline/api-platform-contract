<?php

namespace ControleOnline\Repository;

use ControleOnline\Entity\ContractPeople;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ContractPeople|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContractPeople|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContractPeople[]    findAll()
 * @method ContractPeople[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractPeopleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractPeople::class);
    }

 
}
