<?php

namespace ControleOnline\Service;

use ControleOnline\Entity\People;
use ControleOnline\Entity\Contract;
use App\Library\Provider\Signature\Document;
use App\Library\Provider\Signature\Signer;
use Doctrine\ORM\EntityManagerInterface;
use App\Library\Provider\Signature\SignatureFactory;
use ControleOnline\Entity\Config;
use ControleOnline\Entity\Status;
use ControleOnline\Service\ContractService;
use ControleOnline\Service\PeopleRoleService;
use Exception;

class SignatureService
{
    protected $request;
    protected $signatureProvider;
    public function __construct(
        private EntityManagerInterface $manager,
        private PeopleRoleService $peopleRoleService,
        private ContractService $contractService,
        private StatusService $statusService
    ) {}


    public function sign(Contract $data)
    {

        $data->setStatus(
            $this->statusService->discoveryStatus(
                'pending',
                'Waiting Signature',
                'contract'
            )
        );

        $this->signatureProvider = $this->getFactory($data);

        if ($this->signatureProvider !== null) {


            $document = ($this->signatureProvider->createDocument())
                ->setFileName(
                    sprintf('Contrato-%s', $this->getContractContractorSignerName($data))
                )
                ->setContent(
                    $this->contractService->getContractPDFContent($data)
                )
                ->setDeadlineAt(
                    (new \DateTime('now'))
                        ->add(new \DateInterval('P7D'))
                        ->format('c')
                );

            $data->setDocKey($document->getKey());
            $this->addDocumentSignersFromContract($document, $data);
            $this->signatureProvider->saveDocument($document);
        }


        $this->manager->persist($data);
        $this->manager->flush();
        return $data;
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

    private function getDefaultProviderFromConfig(Contract $data): ?string
    {
        $myCompany = $data->getBeneficiary();


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

    private function getFactory(Contract $data): ?SignatureFactory
    {
        $providerName =
            $this->getDefaultProviderFromConfig($data);

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

    protected function addDocumentSignersFromContract(Document $document, Contract $contract)
    {
        if ($contract->getPeoples()->isEmpty()) {
            throw new Exception('Este contrato não tem assinantes');
        }

        $document->addSigner(
            $this->getSignerFromPeople($contract->getContractModel()->getSigner(), 'assinante')
        );

        $contractParticipants = $contract->getPeoples();
        foreach ($contractParticipants as $participant) {
            $document->addSigner(
                $this->getSignerFromPeople($participant->getPeople(), 'assinante')
            );
        }
    }

    protected function getContractContractorSignerName(Contract $contract): string
    {
        $contractPayers = $contract->getPeoples()
            ->filter(function ($contractPeople) {
                return $contractPeople->getPeopleType() == 'Contractor';
            });
        if ($contractPayers->isEmpty()) {
            throw new Exception(
                'Devem existir pelo menos 1 assinante como contratante'
            );
        }

        return $contractPayers->first()->getPeople()->getFullName();
    }


    protected function getSignerFromPeople(People $people, string $role): Signer
    {
        $signer = $this->signatureProvider->createSigner();

        $signer->setKey($people->getId());
        $signer->setName($people->getFullName());

        if (($email = $people->getOneEmail()) === null) {
            throw new Exception(
                sprintf('O %s "%s" não possui um email', $role, $people->getFullName())
            );
        }

        $signer->setEmail($email->getEmail());

        if ($people->getPeopleType() == 'F') {
            $signer->setHasCPF(true);

            if (($document = $people->getOneDocument()) === null) {
                throw new Exception(
                    sprintf('O %s "%s" não possui um CPF/CNPJ', $role, $people->getFullName())
                );
            }

            $signer->setCPF($document->getDocument());
            if (($birthday = $people->getBirthdayAsString()) === null) {
                throw new Exception(
                    sprintf(
                        'O %s "%s" não tem data de nascimento definida',
                        $role,
                        $people->getFullName()
                    )
                );
            }

            $signer->setBirthday($birthday);
        }

        return $signer;
    }
}
