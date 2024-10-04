<?php

namespace ControleOnline\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Library\Provider\Signature\SignatureFactory;
use ControleOnline\Entity\People;
use ControleOnline\Entity\Config;
use ControleOnline\Entity\Contract;
use ControleOnline\Entity\File;

class SignatureService
{

  public function __construct(
    private EntityManagerInterface $manager,
    private PeopleRoleService $peopleRoleService,
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
    $this->manager->persist($file);
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

  public function getFactory(?string $factoryName = null): ?SignatureFactory
  {
    $providerName = $factoryName === null ?
      $this->getDefaultProviderFromConfig() : $factoryName;

    if ($providerName === null) {
      return null;
    }

    $provider = sprintf(
      '\\App\\Library\\Provider\\Signature\\%s\\Factory',
      ucfirst(strtolower($providerName))
    );

    if (!class_exists($provider)) {
      throw new \Exception('Signature provider factory not found');
    }

    return new $provider(
      $this->getProviderConfig($providerName)
    );
  }


  private function getProviderConfig(string $providerName): ?array
  {

    $myCompany = $this->peopleRoleService->getMainCompany();
    if ($myCompany instanceof People) {

      return $this->manager->getRepository(Config::class)
        ->getKeyValuesByPeople(
          $myCompany,
          strtolower($providerName)
        );
    }

    throw new \Exception('Company not found');
  }

  private function getDefaultProviderFromConfig(): ?string
  {
    $myCompany = $this->peopleRoleService->getMainCompany();
    if ($myCompany instanceof People) {
      $configs = $this->manager->getRepository(Config::class)
        ->getKeyValuesByPeople(
          $myCompany,
          'provider'
        );

      if ($configs === null) {
        return null;
      }

      return isset($configs['provider-signature']) ?
        $configs['provider-signature'] : null;
    }

    throw new \Exception('Company not found');
  }
}
