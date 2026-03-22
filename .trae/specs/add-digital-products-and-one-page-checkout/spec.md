# Produto Digital + One-Page Checkout Spec

## Why
A plataforma precisa suportar a venda de produtos digitais com entrega automática e segura, além de oferecer opções de checkout otimizadas como Landing Pages dedicadas (One-Page Checkout) e um widget de incorporação (embed) para aumentar a conversão.

## What Changes
- Adicionar suporte a produtos digitais (upload de arquivos, limite de downloads, expiração de link).
- Criar fluxo de entrega pós-pagamento com envio de e-mail contendo links tokenizados seguros.
- Atualizar a Área do Cliente para permitir o download dos produtos adquiridos.
- Implementar uma página de produto no Modo Single (`/produto/{slug}/single`) focada em conversão com checkout de uma etapa.
- Desenvolver um widget embed (`embed.js`) e uma página de Checkout Express (`/checkout/express/{slug}`) para compras fora da loja principal.
- Modificar o painel de administração para permitir a configuração de produtos digitais e o upload seguro de arquivos.

## Impact
- Affected specs: Checkout, Payment Processing, Customer Area, Admin Panel, Email Notifications.
- Affected code:
  - Banco de Dados (novas tabelas e colunas).
  - Backend PHP (DeliveryManager, DownloadHandler, EmailDigital, FileUploader).
  - Frontend Público (`product_single.php`, `checkout_express.php`, `embed.js`).
  - Painel Admin (Formulário de produto).

## ADDED Requirements
### Requirement: Suporte a Produtos Digitais
A plataforma DEVE permitir a criação de produtos digitais com arquivos associados.
- **Scenario:** Admin cria um produto digital
  - **WHEN** admin salva um produto marcando a opção "Produto Digital" e faz o upload de um arquivo.
  - **THEN** o arquivo é salvo em um diretório protegido e as configurações de expiração e limite de downloads são registradas.

### Requirement: Entrega Digital Segura
O sistema DEVE entregar produtos digitais automaticamente após a confirmação do pagamento.
- **Scenario:** Pagamento confirmado
  - **WHEN** o webhook do PIX ou Mercado Pago confirma o pagamento de um pedido com itens digitais.
  - **THEN** o sistema gera tokens únicos, registra as entregas e envia um e-mail ao cliente com o link de download.

### Requirement: Checkout Single Page & Express
O sistema DEVE prover páginas de conversão rápida e suporte a widget externo.
- **Scenario:** Cliente acessa a página Single
  - **WHEN** o cliente acessa `/produto/{slug}/single`.
  - **THEN** ele vê uma landing page sem distrações e pode comprar com um formulário de etapa única (One-Step Checkout).

## MODIFIED Requirements
### Requirement: Painel Admin - Gestão de Produtos
O formulário de produtos no painel admin DEVE incluir novas opções para produtos digitais.
- **Scenario:** Edição de Produto
  - **WHEN** o admin acessa a edição de um produto.
  - **THEN** ele pode alternar entre Físico e Digital, fazer upload de arquivo, configurar limite e validade de downloads.

### Requirement: Área do Cliente - Meus Pedidos
A lista de pedidos DEVE mostrar opções de download para produtos digitais adquiridos.
- **Scenario:** Cliente visualiza pedido pago
  - **WHEN** o cliente acessa "Meus Pedidos" e visualiza um pedido pago contendo produtos digitais.
  - **THEN** ele vê um botão de download, com o número de downloads restantes e a data de expiração.