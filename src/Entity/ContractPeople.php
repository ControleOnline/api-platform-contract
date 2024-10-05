<?php

namespace ControleOnline\Entity;

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

/**
 * @ORM\Table (name="contract_people")
 * @ORM\EntityListeners ({ControleOnline\Listener\LogListener::class})
 * @ORM\Entity (repositoryClass="ControleOnline\Repository\ContractPeopleRepository")
 */

#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Put(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
            validationContext: ['groups' => ['people_write']],
            denormalizationContext: ['groups' => ['people_write']]
        ),
        new Delete(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Post(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
        ),
        new GetCollection(security: 'is_granted(\'ROLE_CLIENT\')'),

    ],
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    security: 'is_granted(\'ROLE_CLIENT\')',
    normalizationContext: ['groups' => ['contract_people_read']],
    denormalizationContext: ['groups' => ['contract_people_write']]
)]
class ContractPeople
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups("contract_read","contract_people_read")
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['id' => 'exact'])]

    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\Contract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id", referencedColumnName="id", nullable=false)
     * })
     * @Groups("contract_people_read")
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['contract.id' => 'exact'])]
    private $contract;
    /**
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\People", inversedBy="contractsPeople")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="people_id", referencedColumnName="id", nullable=false)
     * })
     * @Groups("contract_read","contract_people_read")
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['people.id' => 'exact'])]

    private $people;
    /**
     * @ORM\Column(name="people_type", type="string", columnDefinition="enum('Beneficiary', 'Witness', 'Payer', 'Provider')")
     * @Groups("contract_read","contract_people_read")
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['people_type' => 'exact'])]
    private $people_type;
    /**
     * @ORM\Column(name="contract_percentage", type="float",  nullable=true)
     * @Groups("contract_read","contract_people_read")
     */
    private $contract_percentage;


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
     * Get the value of people_type
     */
    public function getPeopleType()
    {
        return $this->people_type;
    }

    /**
     * Set the value of people_type
     */
    public function setPeopleType($people_type): self
    {
        $this->people_type = $people_type;

        return $this;
    }

    /**
     * Get the value of contract_percentage
     */
    public function getContractPercentage()
    {
        return $this->contract_percentage;
    }

    /**
     * Set the value of contract_percentage
     */
    public function setContractPercentage($contract_percentage): self
    {
        $this->contract_percentage = $contract_percentage;

        return $this;
    }
}
