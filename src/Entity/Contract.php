<?php

namespace ControleOnline\Entity;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiFilter;
use ControleOnline\Controller\GenerateContractController;
use ControleOnline\Controller\SignContractController;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\EntityListeners ({ControleOnline\Listener\LogListener::class})
 * @ORM\Entity (repositoryClass="ControleOnline\Repository\ContractRepository")
 */
#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Put(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Post(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
        ),
        new GetCollection(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Post(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
            uriTemplate: '/contracts/{id}/generate',
            controller: GenerateContractController::class,
        ),
        new Post(
            security: 'is_granted(\'ROLE_ADMIN\') or (is_granted(\'ROLE_CLIENT\'))',
            uriTemplate: '/contracts/{id}/sign',
            controller: SignContractController::class,
        ),
    ],
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    normalizationContext: ['groups' => ['contract:read']],
    denormalizationContext: ['groups' => ['contract:write']]
)]
class Contract
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({ "contract:read"})
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * })
     * @Groups({"contract:read","contract:write"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['contractModel' => 'exact'])]

    private $contractModel;


    /**
     * @var \ControleOnline\Entity\Status
     *
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\Status")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     * @Groups({"contract:read","contract:write"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['status' => 'exact'])]

    private $status;
    /**
     * @ORM\Column(name="doc_key", type="string")
     * @Groups({"contract:read","contract:write"})
     */
    private $docKey;
    /**
     * @ORM\Column(name="start_date", type="datetime",  nullable=false)
     * @Groups({"contract:read","contract:write"})
     */
    private $startDate;
    /**
     * @ORM\Column(name="end_date", type="datetime",  nullable=true)
     * @Groups({"contract:read","contract:write"})
     */
    private $endDate;
    /**
     * @ORM\Column(name="creation_date", type="datetime",  nullable=false)
     * @Groups({"contract:read","contract:write"})
     */
    private $creationDate;
    /**
     * @ORM\Column(name="alter_date", type="datetime",  nullable=false)
     * @Groups({"contract:read","contract:write"})
     */
    private $alterDate;

    /**
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\File")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", nullable=true)
     * })
     * @Groups({"contract:read","contract:write"})
     */
    private $contractFile;

    /**
     * @var \ControleOnline\Entity\People
     *
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\People")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="beneficiary_id", referencedColumnName="id")
     * })
     * @Groups({"contract:read","contract:write"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['beneficiary' => 'exact'])]

    private $beneficiary;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="ControleOnline\Entity\ContractPeople", mappedBy="contract")
     * @Groups({"contract:read","contract:write"})
     */

    #[ApiFilter(filterClass: SearchFilter::class, properties: ['peoples.people.name' => 'partial'])]
    private $peoples;


    public function __construct()
    {
        $this->startDate = new DateTime('now');
        $this->endDate = new DateTime('now');
        $this->creationDate = new DateTime('now');
        $this->alterDate = new DateTime('now');
        $this->peoples = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param \ControleOnline\Entity\Status $status
     * @return Status
     */
    public function setStatus(\ControleOnline\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \ControleOnline\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }
    public function setStartDate(DateTime $start_date): Contract
    {
        $this->startDate = $start_date;
        return $this;
    }
    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }
    public function setEndDate(?DateTime $end_date): Contract
    {
        $this->endDate = $end_date;
        return $this;
    }
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }
    public function setCreationDate(DateTime $creation_date): Contract
    {
        $this->creationDate = $creation_date;
        return $this;
    }
    public function getAlterDate(): DateTime
    {
        return $this->alterDate;
    }
    public function setAlterDate(DateTime $alter_date): Contract
    {
        $this->alterDate = $alter_date;
        return $this;
    }
    /**
     * @return mixed
     */

    /**
     * Get })
     */
    public function getContractFile()
    {
        return $this->contractFile;
    }

    /**
     * Set })
     */
    public function setContractFile($contractFile): self
    {
        $this->contractFile = $contractFile;

        return $this;
    }

    /**
     * Get the value of beneficiary
     */
    public function getBeneficiary()
    {
        return $this->beneficiary;
    }

    /**
     * Set the value of beneficiary
     */
    public function setBeneficiary($beneficiary): self
    {
        $this->beneficiary = $beneficiary;

        return $this;
    }

    /**
     * Get })
     */
    public function getContractModel()
    {
        return $this->contractModel;
    }

    /**
     * Set })
     */
    public function setContractModel($contractModel): self
    {
        $this->contractModel = $contractModel;

        return $this;
    }


    /**
     * Add peoples.
     *
     * @return Peoples
     */
    public function addPeoples(ContractPeople $peoples)
    {
        $this->peoples[] = $peoples;
        return $this;
    }
    /**
     * Remove peoples.
     */
    public function removePeoples(ContractPeople $peoples)
    {
        $this->peoples->removeElement($peoples);
    }
    /**
     * Get peoples.
     *
     * @return Collection
     */
    public function getPeoples()
    {
        return $this->peoples;
    }

    /**
     * Get the value of docKey
     */
    public function getDocKey()
    {
        return $this->docKey;
    }

    /**
     * Set the value of docKey
     */
    public function setDocKey($docKey): self
    {
        $this->docKey = $docKey;

        return $this;
    }
}
