# Plano de Implementação: Modos de Loja (Desligar E-commerce e Modo Catálogo)

## Análise do Estado Atual
O sistema atualmente opera como um E-commerce tradicional, exibindo preços, botões "Add to cart", e possuindo um fluxo de checkout com pagamentos (Mercado Pago, Pix Manual). As configurações são armazenadas no banco SQLite (`settings`).

A necessidade do usuário envolve criar três estados de operação para a loja:
1. **E-commerce Completo (Padrão):** Fluxo de vendas normal.
2. **Modo Catálogo (Orçamento/Lista):** Preços ocultos, botões mudam para "Adicionar à minha lista", o carrinho vira uma lista de orçamentos, pede-se Nome/Email/WhatsApp e o pedido é salvo gerando um PDF que é enviado ao admin e baixado pelo usuário.
3. **Modo Informativo (Desligado):** Preços e botões de adicionar ao carrinho ficam ocultos. O site serve apenas para exibir produtos informativamente. O acesso ao `/cart` é bloqueado.

## Mudanças Propostas

### 1. Configurações no Painel Admin (`templates/admin/dashboard.php`)
- **O que fazer:** Adicionar uma nova seção "Modo de Operação da Loja" na aba *Settings*.
- **Como:** Criar um campo `<select name="store_mode">` com as opções `ecommerce`, `catalog` e `informational`. Adicionar um texto explicativo para cada opção.

### 2. Lógica de Exibição no Layout e Componentes (`templates/layout.php`)
- **O que fazer:** Ocultar o ícone do carrinho no Modo Informativo e adaptar textos no Modo Catálogo.
- **Como:** 
  - Se `store_mode == 'informational'`, não renderizar o bloco do carrinho no topo (`<a href="/cart"...`).
  - Se `store_mode == 'catalog'`, mudar o texto do carrinho para "Minha Lista" e o feedback do Alpine (`$store.cartFeedbackModal`) para "Produto adicionado à sua lista".

### 3. Adaptação das Páginas de Produto (`templates/archive.php` e `templates/product.php`)
- **O que fazer:** Controlar a exibição de preços e botões de compra.
- **Como:**
  - Ocultar o bloco de preço (`formatMoney($product['price'])`) caso o `store_mode != 'ecommerce'`.
  - Ocultar o seletor de quantidade e o botão de ação principal caso `store_mode == 'informational'`.
  - Trocar o texto do botão principal para "Adicionar à minha lista" caso `store_mode == 'catalog'`.

### 4. Modificação do Carrinho (`templates/cart.php`)
- **O que fazer:** Transformar o carrinho em uma "Lista de Desejos/Orçamento" no Modo Catálogo.
- **Como:**
  - Se `store_mode == 'catalog'`, ocultar preços dos itens e subtotal.
  - Alterar o título principal para "Minha Lista" e mensagem vazia para "Sua lista está vazia.".
  - Ocultar os campos de Endereço de Entrega (CEP, Rua, etc) do formulário de checkout.
  - Alterar o botão final para "Salvar PDF e Enviar Orçamento".
  - Mudar o `action` do formulário para `/checkout/quote`.

### 5. Criação da Rota de Orçamento e Sucesso (`index.php` e `templates/quote-success.php`)
- **O que fazer:** Processar a solicitação de orçamento e gerar a tela de sucesso com o PDF.
- **Como:**
  - Em `index.php`, redirecionar `/cart` para `/` se `store_mode == 'informational'`.
  - Adicionar a rota POST `/checkout/quote` que recebe Nome, Email, WhatsApp e os itens da lista. Cria um registro em `orders` com status `quote` e total `0`. Redireciona para `/quote-success?id=XYZ`.
  - Criar o arquivo `templates/quote-success.php` com uma mensagem de sucesso, um script para limpar o carrinho no Alpine.js e um botão apontando para `/download-pdf?id=XYZ` para baixar o arquivo.

### 6. Adaptação da Geração de PDF (`src/generate_pdf.php`)
- **O que fazer:** Ajustar o PDF gerado caso o pedido seja um orçamento.
- **Como:**
  - Se o `$order['status'] === 'quote'`, alterar o título do PDF para "Orçamento / Lista de Desejos" e não renderizar as colunas de preços e totais na tabela de itens. Exibir apenas a quantidade e o nome dos produtos.

## Suposições e Decisões
- Os preços não serão exibidos nem no Modo Catálogo nem no Modo Informativo para não caracterizar venda, como validado com o usuário.
- A função "Pedir dados e enviar ao admin" será implementada salvando um "Pedido" com status "Orçamento" (`quote`), aproveitando toda a estrutura já existente de e-mails, dashboard de pedidos e geração de PDF. O Admin receberá o e-mail de "Novo Pedido" que poderá ser adaptado visualmente, e poderá ver o orçamento na aba *Orders*.

## Verificação
1. Alterar o modo para "Informativo" no painel e garantir que botões e preços sumam e `/cart` redirecione para a home.
2. Alterar o modo para "Catálogo", adicionar um item à lista, garantir que preços não apareçam, preencher Nome/Email/WhatsApp e submeter.
3. Verificar se a página de sucesso é exibida e o PDF do orçamento é gerado corretamente sem preços.
4. Alterar o modo de volta para "E-commerce" e garantir que o fluxo tradicional de vendas permaneça intacto.