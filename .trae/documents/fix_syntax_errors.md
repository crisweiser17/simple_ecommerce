# Plano para Corrigir Erros 500 (Erros de Sintaxe)

## Resumo
O usuário relatou um erro 500 ao tentar adicionar um produto no painel admin (`/admin/product-form`) e pediu para verificar as outras rotas. Após análise, foi detectado que o erro 500 é causado por um "Parse Error" (Erro de Sintaxe do PHP) no arquivo `templates/admin/product-form.php`. A mesma verificação global identificou que o mesmo erro de aninhamento incorreto da tag `<?php echo __('...'); ?>` ocorreu em outras rotas/templates.

## Análise do Estado Atual
Ao rodar um linter do PHP em todos os templates (`find templates src -name "*.php" -exec php -l {} \;`), foram encontrados erros nos seguintes arquivos devido ao aninhamento incorreto de tags PHP (`<?php echo __('<?php echo ...'); ?>`):
1. `templates/admin/product-form.php` (Linha 294) - Causa o erro na rota `/admin/product-form`.
2. `templates/cart.php` (Linha 107) - Causa erro na rota `/cart`.
3. `templates/order-success.php` (Linhas 159 e 167) - Causa erro na rota `/order-success`.
4. `templates/quote-success.php` (Linha 12) - Causa erro na rota de sucesso de orçamento.

Esses erros de sintaxe "quebram" a execução do script e retornam o código HTTP 500.

## Alterações Propostas

1. **`templates/admin/product-form.php`**
   - *Onde:* Linha 294
   - *O que:* Corrigir `placeholder="<?php echo __('<?php echo __('Leave blank to not expire'); ?>'); ?>"`
   - *Para:* `placeholder="<?php echo __('Leave blank to not expire'); ?>"`

2. **`templates/cart.php`**
   - *Onde:* Linha 107
   - *O que:* Corrigir `<?php echo $storeMode === 'catalog' ? __('<?php echo __('Request Quote'); ?>') : __('Order Summary'); ?>`
   - *Para:* `<?php echo $storeMode === 'catalog' ? __('Request Quote') : __('Order Summary'); ?>`

3. **`templates/order-success.php`**
   - *Onde:* Linha 159
   - *O que:* Corrigir `<?php echo __('<?php echo __('Copy PIX code'); ?>'); ?>`
   - *Para:* `<?php echo __('Copy PIX code'); ?>`
   - *Onde:* Linha 167
   - *O que:* Corrigir `<?php echo __('<?php echo __('Payment Instructions'); ?>'); ?>`
   - *Para:* `<?php echo __('Payment Instructions'); ?>`

4. **`templates/quote-success.php`**
   - *Onde:* Linha 12
   - *O que:* Corrigir `<?php echo __('<?php echo __('Quote Summary'); ?>'); ?>`
   - *Para:* `<?php echo __('Quote Summary'); ?>`

## Suposições e Decisões
- O erro reportado de fato decorre desse problema de sintaxe.
- Não existem outras rotas quebradas pelo mesmo motivo (confirmado pelo linter no passo de exploração).

## Passos de Verificação
- Rodar o comando do linter `php -l` nos arquivos alterados após a edição para garantir a remoção dos erros sintáticos.
- Verificar manualmente as rotas `/admin/product-form` (adicionar novo) e `/cart` para garantir que elas não retornam mais o Erro 500 e renderizam corretamente.