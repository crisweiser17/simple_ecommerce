# Plano: galeria de imagens por produto (com imagem principal)

## Objetivo
Permitir upload de uma ou mais imagens no formulário de produto do admin, marcar uma como principal e usar essa imagem principal no card do catálogo e como primeira imagem na página de produto; as demais devem aparecer como thumbnails abaixo da principal apenas no detalhe.

## Escopo funcional
- Upload múltiplo de imagens no `/admin/product-form`.
- Marcação de imagem principal no admin (uma única por produto).
- Persistência de galeria por produto sem quebrar compatibilidade com `products.image_url`.
- Exibição no catálogo (`archive`) usando imagem principal.
- Exibição na página de produto com imagem principal + thumbnails secundárias.

## Passos de implementação

1. **Modelagem e migração de banco**
- Criar tabela `product_images` com colunas: `id`, `product_id`, `image_url`, `is_primary`, `sort_order`, `created_at`.
- Criar índice por `product_id` e garantia lógica para no máximo uma principal por produto (controlada por regra de aplicação no save).
- Incluir criação automática da tabela em `ensureProductsSchema()` para ambiente já em produção.
- Opcional de manutenção: atualizar `setup_db.php` e `update_db.php` para também criar `product_images` em instalações novas/legadas.

2. **Camada de dados em `src/products.php`**
- Adicionar funções para galeria:
  - buscar imagens por produto ordenadas (principal primeiro, depois `sort_order`);
  - salvar lote de imagens de um produto;
  - definir principal de forma atômica (zera anteriores e marca uma).
- Ajustar `getAllProducts()` para retornar `primary_image_url` via subconsulta/join da galeria com fallback para `products.image_url`.
- Ajustar `getProduct()` para trazer `category_slug` e anexar coleção `images` (já ordenada) + `primary_image_url`.
- Manter compatibilidade do carrinho/API preservando `image_url` como fallback quando não houver galeria.

3. **Upload e persistência no fluxo `/admin/save-product`**
- Alterar o form para `multipart/form-data`.
- No backend, validar arquivos de imagem (`jpg/jpeg/png/webp/gif`) e tamanho máximo seguro.
- Criar diretório `public/uploads/products/` quando necessário.
- Salvar arquivos com nome único e montar URL pública (`/uploads/products/...`).
- Ao criar/editar produto:
  - combinar imagens já existentes (quando edição) + novas imagens;
  - persistir lista final em `product_images`;
  - aplicar a imagem principal selecionada.
- Sincronizar `products.image_url` com a principal para manter retrocompatibilidade em telas antigas/importação CSV.

4. **UI do admin em `templates/admin/product-form.php`**
- Adicionar campo de upload múltiplo (`multiple`) para imagens.
- Em edição, listar imagens já cadastradas com preview.
- Permitir seleção de “imagem principal” via radio.
- Permitir remoção de imagens existentes no envio (checkbox por imagem removida).
- Manter o campo `image_url` atual como fallback/manual (sem remover neste primeiro ciclo).

5. **Renderização no catálogo (`templates/archive.php`)**
- Trocar `product['image_url']` pela imagem principal resolvida (`primary_image_url` com fallback).
- Garantir placeholder caso nenhuma imagem exista.

6. **Renderização no detalhe (`templates/product.php`)**
- Mostrar imagem principal em destaque.
- Renderizar thumbnails das imagens secundárias logo abaixo da principal.
- Adicionar interação simples (JS leve) para trocar imagem principal ao clicar na thumbnail.
- Manter fallback para cenário sem galeria.

7. **Ajustes complementares**
- Ajustar dashboard/admin listagem de produtos para mostrar imagem principal resolvida.
- Garantir escaping/sanitização em URLs renderizadas.
- Garantir que remoção de produto também limpe linhas em `product_images` (via `ON DELETE CASCADE` ou limpeza na aplicação).

8. **Validação obrigatória**
- Rodar verificação de sintaxe PHP nos arquivos alterados.
- Rodar testes de fluxo manual:
  - criar produto com múltiplas imagens e definir principal;
  - editar produto, trocar principal, remover imagem;
  - validar catálogo mostrando principal;
  - validar página de produto com thumbnails e troca de destaque.
- Subir preview no navegador e validar visual/funcional nas duas páginas (admin + storefront).

## Critérios de aceite
- Admin aceita upload de múltiplas imagens por produto.
- Sempre existe no máximo uma imagem principal por produto.
- Catálogo usa a principal.
- Página de produto mostra principal e thumbnails secundárias.
- Fluxos antigos continuam funcionando com fallback `image_url`.
- Sintaxe/lint e validação visual aprovados.

## Riscos e mitigação
- **Risco**: regressão em telas que ainda leem apenas `image_url`.  
  **Mitigação**: sincronizar `image_url` com principal e manter fallback.
- **Risco**: upload de arquivos inválidos.  
  **Mitigação**: whitelist de extensão/MIME e limite de tamanho.
- **Risco**: inconsistência de principal após edição.  
  **Mitigação**: transação para atualizar flags de principal de forma atômica.
