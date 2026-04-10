# Plano de Implementação: Variações de Produto e Correções de Internacionalização (i18n)

## Resumo
O objetivo deste plano é adicionar um sistema completo de **Variações de Produtos** (ex: Tamanho, Cor) ao e-commerce, permitindo variação de preços, além de corrigir as inconsistências de idioma (inglês/português) no painel administrativo e nas rotas.

## Análise do Estado Atual
- **Banco de Dados**: Atualmente não há tabelas para variações. Os produtos são simples e não possuem estrutura para atributos configuráveis.
- **Carrinho**: Gerenciado via Alpine.js no front-end (`localStorage`), os itens são agrupados apenas pelo `id` do produto.
- **i18n (Idiomas)**: Foram identificados diversos textos hardcoded (fixos) em `dashboard.php`, `product-form.php` e `index.php`, ignorando a função de tradução `__('key')`.

## Mudanças Propostas

### 1. Estrutura do Banco de Dados (`setup_db.php`)
- Criar a tabela `global_variations` (`id`, `name`, `options_json`) para armazenar as variações reutilizáveis criadas pelo usuário.
- Alterar a tabela `products` adicionando a coluna `variations_json` (TEXT). Isso armazenará a configuração específica de variações daquele produto, incluindo os modificadores de preço para cada opção (ex: `+ R$ 10,00` para tamanho Grande).

### 2. Gerenciamento de Variações Globais (Admin)
- **`index.php`**: Adicionar rotas `/admin/save-global-variation` e `/admin/delete-global-variation`.
- **`templates/admin/dashboard.php`**: 
  - Adicionar o menu "Variações" abaixo de "Produtos" na barra lateral.
  - Criar a aba de conteúdo para listar, adicionar, editar e excluir as Variações Globais (semelhante à aba Categorias).

### 3. Formulário de Produto (`templates/admin/product-form.php` e `index.php`)
- Injetar as variações globais no Alpine.js do formulário.
- Criar uma nova seção **"Variações"** onde o lojista poderá:
  - Selecionar uma variação global existente (que preencherá automaticamente as opções).
  - Criar uma variação customizada exclusiva para aquele produto.
  - Definir o **modificador de preço** para cada opção (ex: Base = R$ 50. Opção Média = + R$ 0, Grande = + R$ 20).
  - Marcar um checkbox "Salvar variação para uso futuro", que fará o backend (`index.php`) salvar essa variação na tabela `global_variations` ao salvar o produto.

### 4. Loja (Página do Produto e Carrinho)
- **`templates/product.php` e `templates/product_single.php`**: Renderizar `<select>` ou botões de rádio para cada variação caso o produto possua `variations_json`. Atualizar o preço final exibido dinamicamente via Alpine.js somando os modificadores.
- **`templates/layout.php` (Carrinho)**: 
  - Atualizar a lógica do carrinho para agrupar itens com base na combinação de ID do produto + Variações selecionadas (criando um `cartItemId` único).
  - Garantir que o objeto adicionado ao `localStorage` contenha as variações escolhidas.

### 5. Exibição de Pedidos
- **`templates/cart.php`, `templates/checkout.php`, `templates/admin/order_detail.php`**: Atualizar a interface para exibir as variações selecionadas logo abaixo do nome do produto (ex: *Produto X - Tamanho: Grande*).

### 6. Correções de Internacionalização (i18n)
- **`index.php`**: Substituir mensagens de erro e respostas estáticas (ex: "Product not found", "Access Denied") por chamadas `__('chave')`.
- **`templates/admin/dashboard.php` e `product-form.php`**: Remover todos os textos hardcoded (como "Produto Digital", "CSV Template", labels do FilePond) e substituí-los por `__('chave')`.
- **`lang/pt.php` e `lang/en.php`**: Adicionar todas as chaves mapeadas nos arquivos acima para garantir traduções 100% consistentes na troca de idioma.

## Premissas e Decisões
- **Sem Controle de Estoque por Variação**: Conforme confirmado, o sistema não precisará de SKUs ou controle de quantidade individuais para as opções.
- **Estrutura JSON**: O uso de `variations_json` na tabela de produtos simplifica as consultas e o checkout, evitando junções complexas de banco de dados, sendo ideal para variações que apenas alteram preço ou características visuais.

## Verificação
Após a implementação:
1. Criar uma variação global "Tamanho" com opções P, M, G.
2. Adicionar a um produto, definir que o G custa + R$ 10.
3. Testar a página do produto para verificar se o preço atualiza.
4. Adicionar ao carrinho e finalizar um pedido (verificar se as variações aparecem no Admin > Orders).
5. Alternar o idioma da loja e confirmar que todo o painel admin (especialmente os textos corrigidos) acompanha a linguagem corretamente.