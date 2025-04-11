<?php

namespace ControleOnline\Entity; 
use ControleOnline\Listener\LogListener;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Put(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
            validationContext: ['groups' => ['contract_people:write']],
            denormalizationContext: ['groups' => ['contract_people:write']]
        ),
        new Delete(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Post(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
        ),
        new GetCollection(security: 'is_granted(\'ROLE_CLIENT\')'),

    ],
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    security: 'is_granted(\'ROLE_CLIENT\')',
    normalizationContext: ['groups' => ['contract_people:read']],
    denormalizationContext: ['groups' => ['contract_people:write']]
)]
#[ORM\Table(name: 'contract_people')]
#[ORM\EntityListeners([LogListener::class])]
#[ORM\Entity(repositoryClass: \ControleOnline\Repository\ContractPeopleRepository::class)]
class ContractPeople
{
    /**
     * @Groups({"contract_people:read", "contract:read"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact'])]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @Groups({"contract_people:read", "contract:read", "contract_people:write"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['people.id' => 'exact'])]
    #[ORM\JoinColumn(name: 'people_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \ControleOnline\Entity\People::class)]
    private $people;

    /**
     * @Groups({"contract_people:read", "contract:read", "contract_people:write"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['peopleType' => 'exact'])]
    #[ORM\Column(name: 'people_type', type: 'string')]
    private $peopleType;


    /**
     * @var \ControleOnline\Entity\Contract
     *
     * @Groups({"contract_people:read", "contract_people:write"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['contract' => 'exact'])]
    #[ORM\JoinColumn(name: 'contract_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \ControleOnline\Entity\Contract::class, inversedBy: 'peoples')]
    private $contract;

    /**
     * @Groups({"contract_people:read", "contract:read", "contract_people:write"})
     */
    #[ORM\Column(name: 'contract_percentage', type: 'float', nullable: true)]
    private $contractPercentage;


    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set the value of contract
     */
    public function setContract($contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get the value of people
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * Set the value of people
     */
    public function setPeople($people): self
    {
        $this->people = $people;

        return $this;
    }

    /**
     * Get the value of peopleType
     */
    public function getPeopleType()
    {
        return $this->peopleType;
    }

    /**
     * Set the value of peopleType
     */
    public function setPeopleType($peopleType): self
    {
        $this->peopleType = $peopleType;

        return $this;
    }

    /**
     * Get the value of contractPercentage
     */
    public function getContractPercentage()
    {
        return $this->contractPercentage;
    }

    /**
     * Set the value of contractPercentage
     */
    public function setContractPercentage($contractPercentage): self
    {
        $this->contractPercentage = $contractPercentage;

        return $this;
    }
}
