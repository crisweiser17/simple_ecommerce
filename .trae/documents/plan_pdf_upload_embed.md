# Plano de Implementação: Upload e Exibição de PDF de Produtos

Este plano detalha os passos para permitir o upload de arquivos PDF no painel de administração e exibir o PDF embutido (embed) na página dedicada do produto.

## 1. Atualizar o Formulário de Produto no Admin
**Arquivo:** `templates/admin/product-form.php`
- Modificar a seção referente ao PDF para incluir um campo de upload de arquivo via FilePond, mantendo o campo de URL como alternativa/fallback.
- O campo de upload será nomeado `pdf_file` e configurado para aceitar apenas arquivos `application/pdf`.
- Adicionar o script de inicialização do FilePond para o novo campo (`data-filepond="pdf-single"`), definindo o limite de tamanho (ex: 10MB) e os textos em português.

## 2. Processar o Upload do PDF no Backend
**Arquivo:** `index.php` (na rota `/admin/save-product`)
- Antes de chamar `createProduct($data)` ou `updateProduct($productId, $data)`, verificar se existe um arquivo em `$_FILES['pdf_file']`.
- Utilizar a função auxiliar existente `uploadSingleImageFile` (que permite definir extensões e MIME types permitidos) para processar o upload do PDF.
- Configurar os parâmetros para aceitar apenas a extensão `pdf` e o MIME type `application/pdf`, salvando o arquivo em `/public/uploads/pdfs/`.
- Se o upload for bem-sucedido, substituir o valor de `$data['pdf_url']` (e `$_POST['pdf_url']`) pelo caminho do arquivo salvo (ex: `/uploads/pdfs/pdf_...pdf`).

## 3. Melhorar a Exibição do PDF na Página do Produto
**Arquivo:** `templates/product.php`
- Na aba "Laudos" (`x-show="activeTab === 'laudos'"`), substituir o link simples de download por um layout mais rico.
- Adicionar um cabeçalho com fundo cinza (`bg-gray-50`) contendo o título do laudo (usando `pdf_label`) e um botão de download em destaque.
- Inserir um visualizador embutido usando as tags `<object>` e `<iframe>` com altura fixa (ex: `600px`), permitindo que o usuário leia o PDF diretamente na página sem precisar sair dela.
- Manter o fallback visual caso o produto não possua nenhum PDF cadastrado.

## 4. Testes e Validação
- Acessar o painel admin, criar/editar um produto e fazer o upload de um PDF válido.
- Verificar se o arquivo foi salvo na pasta `/public/uploads/pdfs/` e se a URL foi gravada corretamente no banco de dados.
- Acessar a página do produto no front-end, abrir a aba de Laudos e confirmar se o layout exibe o PDF embutido e se o botão de download funciona corretamente.
- Tentar enviar um arquivo inválido (ex: uma imagem renomeada para .pdf) para garantir que a validação de MIME type bloqueie o upload.