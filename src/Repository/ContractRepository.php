<?php

namespace ControleOnline\Repository;

use ControleOnline\Entity\MyContract;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method MyContract|null find($id, $lockMode = null, $lockVersion = null)
 * @method MyContract|null findOneBy(array $criteria, array $orderBy = null)
 * @method MyContract[]    findAll()
 * @method MyContract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MyContract::class);
    }

 
}
