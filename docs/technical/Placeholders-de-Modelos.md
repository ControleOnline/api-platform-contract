# Placeholders dos modelos de proposta e contrato

Os modelos (`Model.file`) são templates **Twig**. Eles são editados no app **MANAGER**. O CRM só gera o documento a partir do modelo da empresa.

O documento (`Contract.contractFile`, HTML) é gerado por `ContractService::renderContractDocument`:

- ao criar o contrato ou a proposta;
- ao adicionar um signatário;
- em `POST /contracts/{id}/generate` (a tela chama depois de alterar produtos);
- antes de enviar para assinatura (`SignatureService::sign`).

## Variáveis disponíveis

| Variável | Conteúdo |
| --- | --- |
| `contract` | Entidade `Contract` (ex.: `contract.startDate\|date('d/m/Y')`, `contract.id`) |
| `client` | Cliente (`client.name`, `client.alias`) |
| `provider` | Empresa (`provider.name`) |
| `order` | Pedido vinculado ao contrato (o mais recente), ou `null` |
| `products` | Linhas principais do pedido (componentes filhos não entram) |
| `total` / `total_formatted` | Soma das linhas (`1260.0` / `R$ 1.260,00`) |

Cada item de `products` tem: `name`, `description`, `sku`, `quantity`, `unit_price`, `unit_price_formatted`, `total` e `total_formatted`.

## Exemplo

```twig
<p>Contratante: {{ client.name }}. Início: {{ contract.startDate|date('d/m/Y') }}.</p>
<table>
  <tr><th>Produto</th><th>Qtd</th><th>Unitário</th><th>Total</th></tr>
  {% for item in products %}
  <tr>
    <td>{{ item.name }}</td>
    <td>{{ item.quantity }}</td>
    <td>{{ item.unit_price_formatted }}</td>
    <td>{{ item.total_formatted }}</td>
  </tr>
  {% else %}
  <tr><td colspan="4">Nenhum produto.</td></tr>
  {% endfor %}
</table>
<p><strong>Valor total: {{ total_formatted }}</strong></p>
```

Os produtos só entram no pedido se pertencerem à categoria do modelo (`product_categories`, verificado por `ProposalProductCategoryGuard` no módulo orders).
