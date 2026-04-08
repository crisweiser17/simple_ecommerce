# Plano de Implementação

## 1. Listagem de Produtos no Admin (`dashboard.php`)
- **Ação:** Adicionar um link externo na tabela de produtos para visualizar a página do produto no front-end.
- **Detalhes:** 
  - Incluir o ícone `<i class="fa-solid fa-up-right-from-square"></i>`.
  - Garantir que o FontAwesome esteja carregado no `<head>` do `dashboard.php`.
  - O link abrirá em uma nova aba (`target="_blank"`) apontando para `/product/{slug}` ou similar (atualmente já existe um link "Single", vamos adicionar o link padrão para a loja e melhorar a interface visual com o ícone).

## 2. Aspect Ratio das Imagens na Homepage (`dashboard.php` e `archive.php`)
- **Ação:** Permitir a configuração das dimensões da miniatura do produto (Aspect Ratio) no painel Admin e refletir isso na Homepage.
- **Detalhes:**
  - **Admin Settings:** Adicionar dois novos campos no `dashboard.php` (aba Settings > Store Theme): "Proporção da Imagem - Largura" e "Proporção da Imagem - Altura".
  - **Backend (`index.php`):** Salvar esses novos campos (`product_card_aspect_width` e `product_card_aspect_height`) na rota `/admin/save-settings`.
  - **Frontend (`archive.php`):** Substituir a classe fixa `h-48` no contêiner da imagem do produto por um estilo inline `style="aspect-ratio: {width} / {height};"`, permitindo que o contêiner respeite perfeitamente proporções como `363/493`.

## 3. Gerenciamento de Imagens no Formulário de Produto (`product-form.php`)
- **Ação:** Refatorar a interface de gerenciamento de imagens para usar Drag & Drop, botão de remover (X) e remover a geração automática indesejada.
- **Detalhes:**
  - **Unificação:** Ocultar/remover o campo de texto manual "Image URL" para evitar confusão, e consolidar todas as imagens (a principal e as da galeria) em um único grid visual de miniaturas.
  - **Remoção de Imagens (O "X"):** Substituir os checkboxes de "Remover imagem" por um botão "X" no canto superior direito de cada miniatura. Ao clicar no "X", o elemento da imagem será removido do DOM (e, consequentemente, não será enviado no POST `existing_images[]`, o que faz o backend deletá-la naturalmente). Isso permite que o usuário apague facilmente imagens `placehold.co` antigas.
  - **Drag & Drop (Ordenação):** Integrar a biblioteca `SortableJS` via CDN para permitir que o usuário arraste e solte as miniaturas para reordená-las.
  - **Badge "Primary":** Remover os *radio buttons* de seleção de imagem principal. Adicionar uma regra visual (CSS `group-first:block`) que exibe um selo "Primary" automaticamente na primeira imagem do grid (a da esquerda). O backend já está programado para definir a primeira imagem enviada no array como principal.
  - **Imagens Padrão (Placeholder):** Como não haverá mais inserção via Seed para novos produtos, se um produto não tiver imagens, o frontend e o admin já possuem o fallback seguro e limpo para `https://placehold.co/...text=No+Image` de forma dinâmica (sem gravar no banco de dados com "iniciais").

## 4. Testes e Verificação
- Verificar se a listagem do admin carrega o ícone e abre a página correta.
- Salvar uma proporção de 363/493 no painel e verificar se a homepage (`archive.php`) adapta o formato dos cards.
- Editar o produto ID 2, remover a imagem gerada (placeholder com as iniciais), reordenar as novas imagens com Drag & Drop e salvar.
- Validar se a imagem primária é definida corretamente como a primeira da lista.