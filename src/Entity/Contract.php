<?php

namespace ControleOnline\Entity;

use Symfony\Component\Serializer\Attribute\Groups;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ControleOnline\Entity\Model;
use ControleOnline\Entity\Status;
use ControleOnline\Entity\File;
use ControleOnline\Entity\People;
use ControleOnline\Entity\ContractPeople;
use ControleOnline\Repository\ContractRepository;

use ControleOnline\Controller\GenerateContractController;
use ControleOnline\Controller\SignContractController;
use DateTime;


#[ORM\Entity(repositoryClass: ContractRepository::class)]
#[ApiResource(
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    normalizationContext: ['groups' => ['contract:read']],
    denormalizationContext: ['groups' => ['contract:write']],
    operations: [
        new GetCollection(security: "is_granted('ROLE_CLIENT')"),
        new Get(security: "is_granted('ROLE_CLIENT')"),
        new Post(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_CLIENT')"
        ),
        new Put(security: "is_granted('ROLE_CLIENT')"),
        new Post(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_CLIENT')",
            uriTemplate: '/contracts/{id}/generate',
            controller: GenerateContractController::class
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_CLIENT')",
            uriTemplate: '/contracts/{id}/sign',
            controller: SignContractController::class
        )
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'contractModel' => 'exact',
    'status' => 'exact',
    'beneficiary' => 'exact',
    'peoples.people.name' => 'partial'
])]
class Contract
{
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[Groups(['contract:read'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Model::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    #[Groups(['contract:read', 'contract:write'])]
    private $contractModel;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(name: 'status_id', referencedColumnName: 'id')]
    #[Groups(['contract:read', 'contract:write'])]
    private $status;

    #[ORM\Column(name: 'doc_key', type: 'string')]
    #[Groups(['contract:read', 'contract:write'])]
    private $docKey;

    #[ORM\Column(name: 'start_date', type: 'datetime', nullable: false)]
    #[Groups(['contract:read', 'contract:write'])]
    private $startDate;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private $endDate;

    #[ORM\Column(name: 'creation_date', type: 'datetime', nullable: false)]
    #[Groups(['contract:read', 'contract:write'])]
    private $creationDate;

    #[ORM\Column(name: 'alter_date', type: 'datetime', nullable: false)]
    #[Groups(['contract:read', 'contract:write'])]
    private $alterDate;

    #[ORM\ManyToOne(targetEntity: File::class)]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private $contractFile;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(name: 'beneficiary_id', referencedColumnName: 'id')]
    #[Groups(['contract:read', 'contract:write'])]
    private $beneficiary;

    #[ORM\OneToMany(targetEntity: ContractPeople::class, mappedBy: 'contract')]
    #[Groups(['contract:read', 'contract:write'])]
    private $peoples;

    public function __construct()
    {
        $this->startDate = new DateTime('now');
        $this->endDate = new DateTime('now');
        $this->creationDate = new DateTime('now');
        $this->alterDate = new DateTime('now');
        $this->peoples = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStatus(Status $status = null)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $start_date): self
    {
        $this->startDate = $start_date;
        return $this;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $end_date): self
    {
        $this->endDate = $end_date;
        return $this;
    }

    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(DateTime $creation_date): self
    {
        $this->creationDate = $creation_date;
        return $this;
    }

    public function getAlterDate(): DateTime
    {
        return $this->alterDate;
    }

    public function setAlterDate(DateTime $alter_date): self
    {
        $this->alterDate = $alter_date;
        return $this;
    }

    public function getContractFile()
    {
        return $this->contractFile;
    }

    public function setContractFile($contractFile): self
    {
        $this->contractFile = $contractFile;
        return $this;
    }

    public function getBeneficiary()
    {
        return $this->beneficiary;
    }

    public function setBeneficiary($beneficiary): self
    {
        $this->beneficiary = $beneficiary;
        return $this;
    }

    public function getContractModel()
    {
        return $this->contractModel;
    }

    public function setContractModel($contractModel): self
    {
        $this->contractModel = $contractModel;
        return $this;
    }

    public function addPeoples(ContractPeople $peoples)
    {
        $this->peoples[] = $peoples;
        return $this;
    }

    public function removePeoples(ContractPeople $peoples)
    {
        $this->peoples->removeElement($peoples);
    }

    public function getPeoples()
    {
        return $this->peoples;
    }

    public function getDocKey()
    {
        return $this->docKey;
    }

    public function setDocKey($docKey): self
    {
        $this->docKey = $docKey;
        return $this;
    }
}
