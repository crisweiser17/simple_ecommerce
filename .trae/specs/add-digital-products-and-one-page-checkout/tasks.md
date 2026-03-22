# Tasks

- [ ] Task 1: Atualização do Banco de Dados
  - [ ] SubTask 1.1: Adicionar colunas na tabela `products` (`type`, `digital_delivery`, `download_limit`, `download_expiry_days`, `file_url`).
  - [ ] SubTask 1.2: Criar tabela `order_digital_deliveries` (`id`, `order_id`, `product_id`, `token`, `download_count`, `max_downloads`, `expires_at`, `delivered_at`, `downloaded_at`).
  - [ ] SubTask 1.3: Criar tabela `embed_sessions` para rastreamento do widget.

- [ ] Task 2: Modificações no Admin - Gestão de Produtos Digitais
  - [ ] SubTask 2.1: Atualizar o formulário de produto no Admin (Toggle "Produto Digital", configs de download).
  - [ ] SubTask 2.2: Implementar `FileUploader.php` para gerenciar upload de arquivos (local seguro `/storage/digital/` ou URL remota, max 25MB, PDF/ZIP/MP4/DOCX).

- [ ] Task 3: Lógica de Entrega Digital (Pós-Pagamento)
  - [ ] SubTask 3.1: Criar `DeliveryManager.php` para gerar tokens e registrar em `order_digital_deliveries` após a confirmação do pagamento.
  - [ ] SubTask 3.2: Criar `EmailDigital.php` para enviar o e-mail de entrega (via Resend SMTP) contendo os links seguros.
  - [ ] SubTask 3.3: Integrar `DeliveryManager` e `EmailDigital` no fluxo de confirmação de pagamento (webhook/polling).

- [ ] Task 4: Sistema de Download Seguro
  - [ ] SubTask 4.1: Criar rota e `DownloadHandler.php` (`/download/{token}`) para validar o token, contar downloads, verificar expiração e servir o arquivo ou redirecionar.

- [ ] Task 5: Área do Cliente - Meus Pedidos
  - [ ] SubTask 5.1: Atualizar a visualização de pedidos (`/account#orders`) para exibir botões de download, contador de limite e data de expiração.

- [ ] Task 6: Single Page Checkout (`/produto/{slug}/single`)
  - [ ] SubTask 6.1: Criar template `product_single.php` sem header/footer.
  - [ ] SubTask 6.2: Implementar One-Step Checkout embutido (nome + e-mail -> PIX -> polling -> liberação do download/mensagem na mesma tela).

- [ ] Task 7: Widget Embed e Checkout Express
  - [ ] SubTask 7.1: Criar script público `embed.js` para renderizar o botão.
  - [ ] SubTask 7.2: Criar página `checkout_express.php` (`/checkout/express/{slug}`) para receber o tráfego do widget e processar a compra de forma rápida.

# Task Dependencies
- Task 2 depende da Task 1
- Task 3 depende da Task 1 e 2
- Task 4 depende da Task 3
- Task 5 depende da Task 1 e 4
- Task 6 pode ser iniciada em paralelo com Task 2
- Task 7 depende da Task 6