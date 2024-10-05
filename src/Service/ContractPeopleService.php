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




  public function afterPersist(ContractPeople $contractPeople)
  {
    $contract = $contractPeople->getContract();

    if ($contract->getStatus()->getRealStatus() == 'open')
      $this->contractService->genetateFromModel($contract);

    return  $contractPeople;
  }
}
