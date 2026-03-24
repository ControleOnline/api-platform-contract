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
    $queryBuilder->andWhere(sprintf('%s.provider IN(:companies)', $rootAlias, $rootAlias));
    $queryBuilder->setParameter('companies', $companies);


    $companies   = $this->peopleService->getMyCompanies();

    $queryBuilder->leftJoin(
      PeopleLink::class,
      'PeopleLink',
      'WITH',
      sprintf('(PeopleLink.people = %s.client)', $rootAlias, $rootAlias)
    );

    $queryBuilder->andWhere('PeopleLink.linkType IN(:linkType)');
    $queryBuilder->andWhere('PeopleLink.company = :currentUser');
    $queryBuilder->setParameter('linkType', 'sellers-client');
    $queryBuilder->setParameter('currentUser', $currentUser);
  }
}
