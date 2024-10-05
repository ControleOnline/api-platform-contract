<?php

namespace ControleOnline\Service;

use Doctrine\ORM\EntityManagerInterface;

use ControleOnline\Entity\Contract;
use ControleOnline\Entity\File;

class ContractService
{

  public function __construct(
    private EntityManagerInterface $manager,
    private PdfService $pdf,
    private ModelService $modelService
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
    $file->setPeople($data->getBeneficiary());

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


  public function afterPersist(Contract $contract)
  {
    return  $this->genetateFromModel($contract);
  }
}
