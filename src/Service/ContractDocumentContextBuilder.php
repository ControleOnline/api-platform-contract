<?php

namespace ControleOnline\Service;

/**
 * Builds the Twig variables that contract/proposal models can use to print the
 * products of the order linked to the contract.
 *
 * Available in the model template:
 *   {{ order }}                         linked order entity (or null)
 *   {% for item in products %}          top-level order products
 *     {{ item.name }} {{ item.description }} {{ item.sku }}
 *     {{ item.quantity }} {{ item.unit_price_formatted }} {{ item.total_formatted }}
 *   {% endfor %}
 *   {{ total_formatted }} / {{ total }} sum of the listed products
 *
 * Kept free of Doctrine so it can be unit tested with plain objects.
 */
final class ContractDocumentContextBuilder
{
    public function build(?object $order): array
    {
        $products = [];
        $total = 0.0;

        foreach ($this->iterableOf($this->call($order, 'getOrderProducts')) as $orderProduct) {
            // Child rows (components/additions) are already priced in their parent line.
            if ($this->call($orderProduct, 'getOrderProduct') !== null) {
                continue;
            }

            $product = $this->call($orderProduct, 'getProduct');
            $quantity = (float) ($this->call($orderProduct, 'getQuantity') ?? 0);
            $unitPrice = (float) ($this->call($orderProduct, 'getPrice') ?? 0);
            $lineTotal = $this->call($orderProduct, 'getTotal');
            $lineTotal = $lineTotal === null ? $quantity * $unitPrice : (float) $lineTotal;

            $products[] = [
                'name' => (string) ($this->call($product, 'getProduct') ?? ''),
                'description' => (string) ($this->call($product, 'getDescription') ?? ''),
                'sku' => (string) ($this->call($product, 'getSku') ?? ''),
                'quantity' => $this->formatQuantity($quantity),
                'unit_price' => $unitPrice,
                'unit_price_formatted' => $this->formatMoney($unitPrice),
                'total' => $lineTotal,
                'total_formatted' => $this->formatMoney($lineTotal),
            ];
            $total += $lineTotal;
        }

        return [
            'order' => $order,
            'products' => $products,
            'total' => $total,
            'total_formatted' => $this->formatMoney($total),
        ];
    }

    public function formatMoney(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function formatQuantity(float $quantity): string
    {
        return floor($quantity) === $quantity
            ? (string) (int) $quantity
            : number_format($quantity, 3, ',', '.');
    }

    private function call(mixed $value, string $method): mixed
    {
        if (!is_object($value) || !method_exists($value, $method)) {
            return null;
        }

        return $value->{$method}();
    }

    private function iterableOf(mixed $value): iterable
    {
        return is_iterable($value) ? $value : [];
    }
}
