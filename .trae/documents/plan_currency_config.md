# Plano: Configuração Simples de Moeda (Sem Conversão API)

## Análise e Opinião

Sua reflexão está 100% correta. Implementar conversão de moeda em tempo real com API (Dólar para Real) no momento do checkout introduz uma camada enorme de complexidade (ex: variação cambial durante a sessão, divergência em reembolsos, cache da cotação, etc). 

Considerando que seu plano futuro é focar em um **SaaS para o Brasil**, não faz sentido poluir o código principal com conversões complexas. A abordagem mais limpa, que permite você usar o **mesmo código fonte** tanto no Brasil quanto nos EUA, é tratar a loja como **monomoeda (single-currency)** configurável.

**Como vai funcionar:**
1. A loja terá uma configuração global: "Moeda da Loja" (ex: BRL ou USD) e "Símbolo" (ex: R$ ou $).
2. Todos os preços no banco continuam sendo valores numéricos simples, mas a exibição no front-end usará essa configuração.
3. **Meios de pagamento ficam atrelados à moeda:** Mercado Pago e PIX só processam BRL. Se amanhã você criar a loja americana (USD), você habilita um meio de pagamento como Stripe (que aceita USD) e desabilita o PIX.
4. Para o SaaS no Brasil, essa configuração simplesmente virá preenchida e travada em BRL/R$.

Dessa forma, você mantém o sistema leve, atende a versão americana e deixa a estrutura perfeita para o SaaS brasileiro.

---

## Passos de Implementação (Spec)

1. **Adicionar Configurações de Moeda no Admin**
   - Na aba "Settings" (`/admin/settings`), adicionar dois novos campos:
     - `store_currency`: Código da moeda (Padrão: `BRL`).
     - `store_currency_symbol`: Símbolo da moeda (Padrão: `R$`).
   - Salvar esses dados na tabela `settings`.

2. **Atualizar a Formatação Global de Valores**
   - Atualizar a função `formatMoney($amount)` em `src/functions.php` para buscar o `store_currency_symbol` e formatar os decimais de acordo (vírgula para centavos no Brasil, ponto para EUA).

3. **Remover Símbolos Hardcoded (`$` e `R$`)**
   - Substituir usos manuais de `$<?php echo number_format(...) ?>` ou `R$ <?php echo ... ?>` por chamadas à função `formatMoney()`.
   - Arquivos afetados:
     - `templates/admin/dashboard.php`
     - `templates/order-success.php`
     - `templates/account.php`
     - `src/generate_pdf.php`
     - `templates/checkout.php` (se houver)

4. **Validar Moeda nos Gateways de Pagamento (Opcional/Segurança)**
   - No `checkout.php` e nos arquivos dos provedores (`mercadopago_gateway.php`, `manual_pix_gateway.php`), garantir que a transação declare a moeda configurada da loja.
   - Ocultar Mercado Pago / Pix no checkout caso a loja esteja configurada com uma moeda diferente de BRL (evitando que o usuário americano tente pagar com Pix).

Este plano resolve a questão de forma robusta e sem integrações desnecessárias com APIs de câmbio. Podemos prosseguir com esta execução?