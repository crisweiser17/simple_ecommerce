# Plano de implementação: feedback visual ao adicionar produto no carrinho

## Objetivo
Quando o usuário adicionar um produto ao carrinho, exibir um modal centralizado informando que o produto **$nome_produto** foi adicionado, com dois botões:
- **Continuar comprando**
- **Finalizar compra**

## Escopo técnico
- Implementar no layout público para funcionar tanto na listagem quanto na página de produto.
- Aproveitar a store Alpine já existente do carrinho.
- Garantir UX consistente em desktop e mobile.

## Etapas de implementação

1. **Adicionar estado global de modal na store Alpine**
   - Arquivo: `templates/layout.php`
   - Criar estado global (ex.: `cartFeedbackModal`) dentro de `alpine:init` com:
     - `open` (boolean)
     - `productName` (string)
     - métodos: `show(name)`, `close()`
   - Motivo: permitir disparo centralizado a partir de qualquer tela que use o layout.

2. **Disparar modal no fluxo de adicionar ao carrinho**
   - Arquivo: `templates/layout.php`
   - No método `add(product)` da store `cart`, após adicionar/somar item e persistir no localStorage:
     - chamar método de abertura do modal global;
     - preencher nome do produto com fallback seguro (`product.name` ou texto padrão).
   - Resultado esperado: qualquer clique em “Add to Cart” abre o feedback.

3. **Renderizar modal global no layout**
   - Arquivo: `templates/layout.php`
   - Inserir bloco de modal próximo ao `<main>` para sobrepor página inteira:
     - backdrop escuro;
     - card central;
     - mensagem dinâmica com nome do produto;
     - botão **Continuar comprando** (fecha modal);
     - botão **Finalizar compra** (navega para `/cart`).
   - Usar `x-show`, `x-transition`, `x-cloak`, e bindings Alpine para evitar flicker.

4. **Adicionar comportamentos de usabilidade**
   - Arquivo: `templates/layout.php`
   - Fechar modal com:
     - clique no backdrop;
     - tecla `Esc`;
     - botão de continuar.
   - Garantir que o botão de finalizar funcione como navegação direta para checkout (`/cart`).

5. **Ajustar detalhe de quantidade da página de produto (consistência opcional recomendada)**
   - Arquivo: `templates/layout.php`
   - No método `add(product)`, respeitar `product.quantity` inicial quando informado (na página de produto já existe `quantity: qty`).
   - Benefício: feedback e carrinho refletirem a intenção real do usuário.

6. **Validação funcional**
   - Cenários de teste:
     - Adicionar pela listagem (`archive.php`) abre modal com nome correto;
     - Adicionar pela página de produto (`product.php`) abre modal com nome correto;
     - “Continuar comprando” fecha modal e mantém usuário na página;
     - “Finalizar compra” redireciona para `/cart`;
     - Modal não quebra navegação, nem estado do carrinho no localStorage.
   - Executar checagem sintática PHP após alterações.

## Critérios de aceite
- Modal aparece no centro da tela em todas as páginas públicas que usam o botão de adicionar.
- Mensagem contém o nome do produto recém-adicionado.
- Dois botões funcionam exatamente como solicitado.
- Sem regressão no contador, armazenamento e renderização do carrinho.
