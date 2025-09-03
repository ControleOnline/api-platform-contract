<?php

namespace ControleOnline\Service;

use Doctrine\ORM\EntityManagerInterface;

use ControleOnline\Entity\Contract;
use ControleOnline\Entity\ContractPeople;
use ControleOnline\Entity\File;

class ContractPeopleService
{

  public function __construct(
    private EntityManagerInterface $manager,
    private ContractService $contractService
  ) {}

  public function postPersist(ContractPeople $contractPeople)
  {
    //$contract = $contractPeople->getContract();
    //$this->contractService->genetateFromModel($contract);
    //return  $contractPeople;
  }
}
