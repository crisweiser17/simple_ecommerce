# Plano de Otimização Mobile-First (Public e Admin)

Este plano detalha as alterações necessárias em todas as páginas do sistema para garantir que a experiência seja totalmente responsiva e otimizada para dispositivos móveis, utilizando as classes utilitárias do Tailwind CSS e a reatividade do Alpine.js.

## 1. Layout Público e Navegação (`templates/layout.php`)
- **Header/Menu:** Implementar um "Hamburger Menu" (menu sanduíche) para telas pequenas. Esconder os links de navegação principais no mobile e exibi-los apenas quando o menu for ativado (via Alpine.js `x-data="{ mobileMenuOpen: false }"`).
- **Ajustes de Espaçamento:** Reduzir paddings horizontais no mobile (`px-4` no mobile, `md:px-8` em desktop).
- **Rodapé (Footer):** Garantir que as colunas do rodapé empilhem corretamente (`grid-cols-1 sm:grid-cols-2 md:grid-cols-4`).

## 2. Páginas Públicas (Loja e Checkout)
- **Home e Arquivo (`templates/archive.php`, `index.php`):**
  - Garantir que a grade de produtos seja de 1 coluna no mobile, 2 em tablets e 3 ou 4 em desktops (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`).
- **Página do Produto (`templates/product.php` e `templates/public/product_single.php`):**
  - O layout dividido (Imagem na esquerda, Info na direita) deve empilhar verticalmente no mobile (`flex-col md:flex-row`).
  - Ajustar o tamanho das imagens para não transbordarem a tela.
- **Carrinho e Checkout (`templates/cart.php`, `templates/public/checkout_express.php`):**
  - Garantir que o resumo do pedido (Order Summary) caia para baixo dos itens do carrinho em telas pequenas.
  - Ajustar o grid de endereços (CEP, Rua, Número, etc.) para ocupar 1 coluna no mobile (`grid-cols-1 md:grid-cols-2`).
  - Inputs e botões devem ter tamanho adequado para toque (mínimo `h-10` ou `py-2`/`py-3`).
- **Minha Conta (`templates/account.php`):**
  - O menu lateral de abas (Profile / Orders) deve se transformar em um menu superior ou dropdown no mobile.
  - A tabela de pedidos precisa rolar horizontalmente (`overflow-x-auto` no container da tabela e `whitespace-nowrap` nas células).

## 3. Painel Administrativo - Estrutura Global
- **Sidebar (Menu Lateral Admin):**
  - Atualmente a sidebar é fixa (`w-64`). No mobile, isso esmaga o conteúdo.
  - **Ação:** Transformar a sidebar do admin em um "Off-canvas / Drawer" no mobile. Adicionar um botão de menu (Hamburger) no topo da tela do admin visível apenas no mobile para abrir/fechar a sidebar (usando Alpine.js).
  - Aplicar isso nos arquivos: `templates/admin/dashboard.php`, `templates/admin/order_detail.php`, `templates/admin/customer_detail.php`, `templates/admin/product-form.php`.

## 4. Painel Administrativo - Páginas de Dados
- **Dashboard (`templates/admin/dashboard.php`):**
  - Gráficos e Cards de Resumo: Empilhar em telas pequenas (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`).
  - Tabelas (Últimos Pedidos, Clientes, Produtos): Envolver todas as `<table>` em uma div com `class="overflow-x-auto w-full"` para não quebrar o layout.
- **Detalhes do Pedido (`templates/admin/order_detail.php`):**
  - Ajustar o grid de status e formulário do cliente para `grid-cols-1` no mobile e `md:grid-cols-2`.
  - Tabela de itens do pedido com rolagem horizontal.
- **Formulário de Produto (`templates/admin/product-form.php`):**
  - Layout do formulário empilhado. O preview da imagem e o upload devem se ajustar 100% à largura da tela.
- **Detalhes do Cliente (`templates/admin/customer_detail.php`):**
  - Empilhar cards de informações do cliente. Rolagem horizontal no histórico de pedidos.

## 5. Revisão e Testes
- Adicionar tags `<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">` onde faltar (se aplicável, para evitar zoom indesejado em inputs no iOS).
- Revisar todos os formulários para garantir que fontes não sejam menores que `16px` em campos de texto (para evitar zoom no iOS).
- Testar a responsividade usando a pré-visualização do navegador.