<?php

namespace ControleOnline\Repository;

use ControleOnline\Entity\ContractModel;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ContractModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContractModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContractModel[]    findAll()
 * @method ContractModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContractModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractModel::class);
    }

 
}
