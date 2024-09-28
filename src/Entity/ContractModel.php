<?php

namespace ControleOnline\Entity;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Delete;
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
        new Get(
            security: 'is_granted(\'ROLE_CLIENT\')',
            normalizationContext: ['groups' => ['contract_model_detail_read']]
        ),
        new Put(
            security: 'is_granted(\'ROLE_CLIENT\')'
        ),
        new Post(
            security: 'is_granted(\'ROLE_CLIENT\')'
        ),
        new Delete(
            security: 'is_granted(\'ROLE_CLIENT\')'
        ),
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
     * @Groups({ "contract_read","contract_model_read","contract_model_detail_read"})
     */
    private $id;

    /**
     * @var \ControleOnline\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     * @Groups({"contract_read","contract_model_read","contract_model_write","contract_model_detail_read"})
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
     * @Groups({"contract_read","contract_model_read","contract_model_write","contract_model_detail_read"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['people' => 'exact'])]

    private $people;


    /**
     * @var \ControleOnline\Entity\People
     *
     * @ORM\ManyToOne(targetEntity="ControleOnline\Entity\People")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="signer_id", referencedColumnName="id")
     * })
     * @Groups({"contract_read","contract_model_read","contract_model_write","contract_model_detail_read"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['signer' => 'exact'])]

    private $signer;

    /**
     * @ORM\Column(name="content", type="string")
     * @Groups({"contract_model_detail_read","contract_model_write","contract_model_detail_read"})
     */
    private $content;

    /**
     * @ORM\Column(name="context", type="string")
     * @Groups({"contract_read","contract_model_read","contract_model_detail_read","contract_model_write","contract_model_detail_read"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['context' => 'exact'])]
    private $context;

    /**
     * @ORM\Column(name="model", type="string")
     * @Groups({"contract_read","contract_model_read","contract_model_detail_read","contract_model_write","contract_model_detail_read"})
     */
    #[ApiFilter(filterClass: SearchFilter::class, properties: ['model' => 'partial'])]
    private $model;


    public function getId(): int
    {
        return $this->id;
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

    /**
     * Get the value of content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the value of model
     */
    public function setModel($model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the value of context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the value of context
     */
    public function setContext($context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the value of signer
     */
    public function getSigner()
    {
        return $this->signer;
    }

    /**
     * Set the value of signer
     */
    public function setSigner($signer): self
    {
        $this->signer = $signer;

        return $this;
    }
}
