<?php

namespace ControleOnline\Entity;

use Symfony\Component\Serializer\Attribute\Groups;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use ControleOnline\Entity\People;
use ControleOnline\Entity\Contract;
use ControleOnline\Repository\ContractPeopleRepository;
use ControleOnline\Listener\LogListener;

#[ORM\Table(name: 'contract_people')]
#[ORM\EntityListeners([LogListener::class])]
#[ORM\Entity(repositoryClass: ContractPeopleRepository::class)]
#[ApiResource(
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    normalizationContext: ['groups' => ['contract_people:read']],
    denormalizationContext: ['groups' => ['contract_people:write']],
    security: "is_granted('ROLE_CLIENT')",
    operations: [
        new GetCollection(security: "is_granted('ROLE_CLIENT')"),
        new Get(security: "is_granted('ROLE_CLIENT')"),
        new Post(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_CLIENT')"
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_CLIENT')",
            validationContext: ['groups' => ['contract_people:write']],
            denormalizationContext: ['groups' => ['contract_people:write']]
        ),
        new Delete(security: "is_granted('ROLE_CLIENT')")
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'people.id' => 'exact',
    'peopleType' => 'exact',
    'contract' => 'exact'
])]
class ContractPeople
{
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['contract_people:read', 'contract:read'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'people_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['contract_people:read', 'contract:read', 'contract_people:write'])]
    private $people;

    #[ORM\Column(name: 'people_type', type: 'string')]
    #[Groups(['contract_people:read', 'contract:read', 'contract_people:write'])]
    private $peopleType;

    #[ORM\ManyToOne(targetEntity: Contract::class, inversedBy: 'peoples')]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id')]
    #[Groups(['contract_people:read', 'contract_people:write'])]
    private $contract;

    #[ORM\Column(name: 'contract_percentage', type: 'float', nullable: true)]
    #[Groups(['contract_people:read', 'contract:read', 'contract_people:write'])]
    private $contractPercentage;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getContract()
    {
        return $this->contract;
    }

    public function setContract($contract): self
    {
        $this->contract = $contract;
        return $this;
    }

    public function getPeople()
    {
        return $this->people;
    }

    public function setPeople($people): self
    {
        $this->people = $people;
        return $this;
    }

    public function getPeopleType()
    {
        return $this->peopleType;
    }

    public function setPeopleType($peopleType): self
    {
        $this->peopleType = $peopleType;
        return $this;
    }

    public function getContractPercentage()
    {
        return $this->contractPercentage;
    }

    public function setContractPercentage($contractPercentage): self
    {
        $this->contractPercentage = $contractPercentage;
        return $this;
    }
}
