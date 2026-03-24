<?php

namespace ControleOnline\Service;

use Doctrine\ORM\EntityManagerInterface;

use ControleOnline\Entity\Contract;
use ControleOnline\Entity\File;
use ControleOnline\Entity\PeopleLink;
use ControleOnline\Entity\Status;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface as Security;

class ContractService
{

  public function __construct(
    private EntityManagerInterface $manager,
    private PdfService $pdf,
    private ModelService $modelService,
    private StatusService $statusService,
    private PeopleService $peopleService,
    private Security $security,
  ) {}

  public function genetateFromModel(Contract $data)
  {
    $file = $data->getContractFile();
    if (!$file)
      $file = new File();

    $file->setFileType('text');
    $file->setExtension('html');
    $file->setContent($this->modelService->genetateFromModel($data));
    $file->setFileName($data->getContractModel()->getModel());
    $file->setPeople($data->getProvider());
    $file->setContext($data->getContractModel()->getContext());

    $this->manager->persist($file);

    $data->setContractFile($file);

    $this->manager->persist($data);
    $this->manager->flush();

    return $data;
  }

  public function getContractPDFContent(Contract $contract): string
  {
    $content = $contract->getContractFile()->getContent();

    if ($contract->getContractFile()->getExtension() == 'pdf')
      return $content;

    if (empty($content)) {
      throw new \Exception(
        sprintf('Houve um erro ao gerar o PDF')
      );
    }

    return $this->pdf->convertHtmlToPdf($content);
  }


  public function prePersist(Contract $contract)
  {

    $openStatus =
      $this->statusService->discoveryStatus(
        'open',
        'open',
        'contract'
      );

    if (!$contract->getStatus())
      $contract->setStatus(
        $openStatus
      );

    if ($contract->getStatus()->getRealStatus() != 'open')
      throw new \Exception(
        sprintf('Not modify contracts if is not open')
      );

    return $contract;
  }

  public function postPersist(Contract $contract)
  {
    if ($contract->getStatus()->getRealStatus() == 'open')
      return  $this->genetateFromModel($contract);
  }

  public function securityFilter(QueryBuilder $queryBuilder, $resourceClass = null, $applyTo = null, $rootAlias = null): void
  {
    $currentUser = $this->security->getToken()->getUser()->getPeople();
    $companies   = $this->peopleService->getMyCompanies();

    $queryBuilder->andWhere(sprintf('%s.provider IN(:companies)', $rootAlias));
    $queryBuilder->setParameter('companies', $companies);


    // JOIN client da empresa
    $queryBuilder->innerJoin(
      PeopleLink::class,
      'pl_client',
      'WITH',
      sprintf('pl_client.people = %s.client 
                 AND pl_client.linkType = :clientLinkType', $rootAlias)
    );

    // Clientes do vendedor
    $queryBuilder->leftJoin(
      PeopleLink::class,
      'pl_seller',
      'WITH',
      sprintf('pl_seller.people = %s.client 
                 AND pl_seller.linkType = :sellerLinkType
                 AND pl_seller.company = :currentUser', $rootAlias)
    );

    // JOIN owner (eu sou dono da empresa)
    $queryBuilder->leftJoin(
      PeopleLink::class,
      'pl_owner',
      'WITH',
      sprintf('pl_owner.company = %s.provider 
         AND pl_owner.people = :currentUser
         AND pl_owner.linkType = :ownerLinkType', $rootAlias)
    );

    $queryBuilder->andWhere(
      'pl_seller.id IS NOT NULL OR pl_owner.id IS NOT NULL'
    );

    $queryBuilder->setParameter('sellerLinkType', 'sellers-client');
    $queryBuilder->setParameter('clientLinkType', 'client');
    $queryBuilder->setParameter('ownerLinkType', 'owner');
    $queryBuilder->setParameter('currentUser', $currentUser);
  }
}
