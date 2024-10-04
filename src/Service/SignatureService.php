<?php

namespace Controleonline\Service;

use ControleOnline\Entity\People;
use ControleOnline\Entity\Contract as ContractEntity;
use App\Library\Provider\Signature\Document;
use App\Library\Provider\Signature\Signer;
use Doctrine\ORM\EntityManagerInterface;
use App\Library\Provider\Signature\Exception\InvalidParameterException;
use App\Library\Provider\Signature\Exception\ProviderRequestException;
use App\Library\Exception\MissingDataException;
use App\Library\Provider\Signature\SignatureFactory;
use ControleOnline\Entity\Config;
use ControleOnline\Service\PeopleRoleService;

class SignatureService
{
    protected $request;
    protected $signatureProvider;
    public function __construct(
        private EntityManagerInterface $manager,
        private PeopleRoleService $peopleRoleService,

    ) {}


    public function sign(ContractEntity $data)
    {

        $this->signatureProvider = $this->getFactory();

        if ($this->signatureProvider === null) {
            $data->setContractStatus('Waiting approval');
        } else {

            try {

                $document = ($this->signatureProvider->createDocument())
                    ->setFileName(
                        sprintf('Contrato-%s', $this->getContractContractorSignerName($data))
                    )
                    ->setContent($data->getContractFile()->getContent())
                    ->setDeadlineAt(
                        (new \DateTime('now'))
                            ->add(new \DateInterval('P7D'))
                            ->format('c')
                    );

                // config signers

                $this->addDocumentSignersFromContract($document, $data);

                // create document in cloud service

                $this->signatureProvider->saveDocument($document);

                // update contract status

                $data->setContractStatus('Waiting signatures');
                $data->setDocKey($document->getKey());

                $this->manager->persist($data);
                $this->manager->flush($data);
            } catch (InvalidParameterException $e) {
                throw new \Exception($e->getMessage());
            } catch (ProviderRequestException $e) {
                throw new \Exception($e->getMessage());
            } catch (MissingDataException $e) {
                throw new \Exception($e->getMessage());
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
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
    
    private function getFactory(?string $factoryName = null): ?SignatureFactory
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

    protected function addDocumentSignersFromContract(Document $document, ContractEntity $contract)
    {
        if ($contract->getContractPeople()->isEmpty()) {
            throw new MissingDataException('Este contrato não tem assinantes');
        }

        // add providers


        $contractProviders = $contract->getContractPeople()
            ->filter(function ($contractPeople) {
                return $contractPeople->getPeopleType() == 'Provider';
            });
        if ($contractProviders->isEmpty()) {
            throw new MissingDataException('O prestador de serviços não foi definido');
        }

        foreach ($contractProviders as $provider) {
            $document->addSigner(
                $this->getSignerFromPeople($provider->getPeople(), 'prestador de serviços')
            );
        }

        // add the rest

        $contractParticipants = $contract->getContractPeople()
            ->filter(function ($contractPeople) {
                return $contractPeople->getPeopleType() != 'Provider';
            });
        if ($contractParticipants->isEmpty()) {
            throw new MissingDataException(
                'Devem existir pelo menos 1 assinante no contrato'
            );
        }

        foreach ($contractParticipants as $participant) {
            $document->addSigner(
                $this->getSignerFromPeople($participant->getPeople(), 'assinante')
            );
        }
    }

    protected function getContractContractorSignerName(ContractEntity $contract): string
    {
        $contractPayers = $contract->getContractPeople()
            ->filter(function ($contractPeople) {
                return $contractPeople->getPeopleType() == 'Payer';
            });
        if ($contractPayers->isEmpty()) {
            throw new MissingDataException(
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
            throw new MissingDataException(
                sprintf('O %s "%s" não possui um email', $role, $people->getFullName())
            );
        }

        $signer->setEmail($email->getEmail());

        if ($people->isPeople()) {
            $signer->setHasCPF(true);

            if (($document = $people->getOneDocument()) === null) {
                throw new MissingDataException(
                    sprintf('O %s "%s" não possui um CPF/CNPJ', $role, $people->getFullName())
                );
            }

            $signer->setCPF($document->getDocument());
            if (($birthday = $people->getBirthdayAsString()) === null) {
                throw new MissingDataException(
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
