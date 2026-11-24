<?php

namespace ControleOnline\Contract\Tests\Service;

use ControleOnline\Service\ContractDocumentContextBuilder;
use PHPUnit\Framework\TestCase;

class ContractDocumentContextBuilderTest extends TestCase
{
    public function testWithoutOrderReturnsEmptyProductsAndZeroTotal(): void
    {
        $context = (new ContractDocumentContextBuilder())->build(null);

        self::assertNull($context['order']);
        self::assertSame([], $context['products']);
        self::assertSame(0.0, $context['total']);
        self::assertSame('R$ 0,00', $context['total_formatted']);
    }

    public function testListsTopLevelProductsWithFormattedValues(): void
    {
        $order = $this->order([
            $this->orderProduct('Codex', 2, 130.0, 260.0, 'Assinatura mensal', 'CDX'),
            $this->orderProduct('Cadastro de produtos para PDV', 1, 1000.0, 1000.0),
        ]);

        $context = (new ContractDocumentContextBuilder())->build($order);

        self::assertSame($order, $context['order']);
        self::assertCount(2, $context['products']);
        self::assertSame([
            'name' => 'Codex',
            'description' => 'Assinatura mensal',
            'sku' => 'CDX',
            'quantity' => '2',
            'unit_price' => 130.0,
            'unit_price_formatted' => 'R$ 130,00',
            'total' => 260.0,
            'total_formatted' => 'R$ 260,00',
        ], $context['products'][0]);
        self::assertSame(1260.0, $context['total']);
        self::assertSame('R$ 1.260,00', $context['total_formatted']);
    }

    public function testSkipsChildRowsAndComputesMissingLineTotal(): void
    {
        $parent = $this->orderProduct('Combo', 1, 50.0, null);
        $child = $this->orderProduct('Componente', 1, 10.0, 10.0, '', '', $parent);

        $context = (new ContractDocumentContextBuilder())->build($this->order([$parent, $child]));

        self::assertCount(1, $context['products']);
        self::assertSame('Combo', $context['products'][0]['name']);
        self::assertSame(50.0, $context['products'][0]['total']);
        self::assertSame(50.0, $context['total']);
    }

    public function testFractionalQuantityKeepsDecimals(): void
    {
        $context = (new ContractDocumentContextBuilder())->build(
            $this->order([$this->orderProduct('Horas', 1.5, 100.0, 150.0)])
        );

        self::assertSame('1,500', $context['products'][0]['quantity']);
    }

    public function testDocumentedPlaceholdersRenderInTwig(): void
    {
        $twig = new \Twig\Environment(new \Twig\Loader\ArrayLoader());
        $template = $twig->createTemplate(
            '{% for item in products %}{{ item.quantity }}x {{ item.name }} {{ item.unit_price_formatted }} = {{ item.total_formatted }};{% endfor %} Total: {{ total_formatted }}'
        );

        $html = $template->render((new ContractDocumentContextBuilder())->build(
            $this->order([$this->orderProduct('Codex', 2, 130.0, 260.0)])
        ));

        self::assertSame('2x Codex R$ 130,00 = R$ 260,00; Total: R$ 260,00', $html);
    }

    private function order(array $orderProducts): object
    {
        return new class ($orderProducts) {
            public function __construct(private array $orderProducts) {}

            public function getOrderProducts(): array
            {
                return $this->orderProducts;
            }
        };
    }

    private function orderProduct(
        string $name,
        float $quantity,
        float $price,
        ?float $total,
        string $description = '',
        string $sku = '',
        ?object $parent = null
    ): object {
        $product = new class ($name, $description, $sku) {
            public function __construct(private string $name, private string $description, private string $sku) {}

            public function getProduct(): string
            {
                return $this->name;
            }

            public function getDescription(): string
            {
                return $this->description;
            }

            public function getSku(): ?string
            {
                return $this->sku;
            }
        };

        return new class ($product, $quantity, $price, $total, $parent) {
            public function __construct(
                private object $product,
                private float $quantity,
                private float $price,
                private ?float $total,
                private ?object $parent
            ) {}

            public function getProduct(): object
            {
                return $this->product;
            }

            public function getQuantity(): float
            {
                return $this->quantity;
            }

            public function getPrice(): float
            {
                return $this->price;
            }

            public function getTotal(): ?float
            {
                return $this->total;
            }

            public function getOrderProduct(): ?object
            {
                return $this->parent;
            }
        };
    }
}
