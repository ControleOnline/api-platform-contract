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
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\EntityListeners ({ControleOnline\Listener\LogListener::class})
 * @ORM\Entity (repositoryClass="ControleOnline\Repository\ContractModelRepository")
 */
#[ApiResource(
    operations: [
        new Get(security: 'is_granted(\'ROLE_CLIENT\')'),
        new Put(),
        new Post(),
        new GetCollection(security: 'is_granted(\'ROLE_CLIENT\')')
    ],
    formats: ['jsonld', 'json', 'html', 'jsonhal', 'csv' => ['text/csv']],
    normalizationContext: ['groups' => ['contract_model_read']],
    denormalizationContext: ['groups' => ['contract_model_write']]
)]
class ContractModel
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({ "contract_read"})
     */
    private $id;

    /**
     * @var \ControleOnline\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     * @Groups({"contract_read"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['category' => 'exact'])]

    private $category;
    /**
     * @var \ControleOnline\Entity\People
     *
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\People")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="people_id", referencedColumnName="id")
     * })
     * @Groups({"contract_read"})
     */
    private $people;
    /**
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\File")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     * })
     * @Groups({"contract_read"})
     */
    private $file;

    public function __construct() {}
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get })
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set })
     */
    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get the value of people
     */
    public function getPeople(): \ControleOnline\Entity\People
    {
        return $this->people;
    }

    /**
     * Set the value of people
     */
    public function setPeople(\ControleOnline\Entity\People $people): self
    {
        $this->people = $people;

        return $this;
    }

    /**
     * Get the value of category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the value of category
     */
    public function setCategory($category): self
    {
        $this->category = $category;

        return $this;
    }
}
