# Plano de Implementação de E-mails Transacionais

O objetivo deste plano é implementar novos alertas de e-mail (Criação de Pedido e Atualização de Status) e padronizar **todos** os e-mails disparados pelo sistema para que utilizem o mesmo template (mesmo cabeçalho e cores) do site atual, trazendo uma experiência unificada para o cliente.

## Lembretes de E-mail do Sistema (Lista Solicitada)

Abaixo estão todos os gatilhos de e-mail que o sistema possuirá após essa implementação, e quando cada um é disparado:

1. **Token de Login (`auth.php`)**:
   - **Quando dispara**: Sempre que um cliente insere o e-mail para fazer login ou cadastro na loja.
   - **Destinatário**: Cliente.
2. **Entrega de Produto Digital (`EmailDigital.php`)**:
   - **Quando dispara**: Automaticamente quando o status de um pedido contendo produtos digitais é alterado para `paid` (pago), seja pelo webhook (Mercado Pago/Pix) ou manualmente no Admin.
   - **Destinatário**: Cliente.
3. **Confirmação de Novo Pedido (NOVO - `EmailOrder.php`)**:
   - **Quando dispara**: No exato momento em que o cliente finaliza o checkout (criação do pedido no banco de dados).
   - **Destinatário**: Cliente.
4. **Atualização de Status do Pedido (NOVO - `EmailOrder.php`)**:
   - **Quando dispara**: Sempre que o status do pedido muda (ex: de pendente para pago, ou para enviado, cancelado, etc.).
   - **Destinatário**: Cliente.
5. **Formulário de Contato (`contact.php`)**:
   - **Quando dispara**: Quando um usuário preenche a página "Contact Us".
   - **Destinatário**: Administrador da loja.

## Passos da Implementação

### 1. Criar um Layout Base de E-mail (`src/mailer.php`)
- Criar a função genérica `renderEmailLayout(string $title, string $content)`.
- Esta função vai gerar o HTML do e-mail. No cabeçalho, ela buscará dinamicamente a configuração do nome da loja (`store_name`) e a cor de fundo do cabeçalho da loja (`theme_header_bg`). O texto do cabeçalho será branco (`#ffffff`) para garantir a legibilidade.
- Refatorar a função existente `renderLoginTokenEmail` para usar essa nova função base `renderEmailLayout`, reduzindo repetição de código HTML.

### 2. Atualizar o E-mail de Entrega Digital (`src/EmailDigital.php`)
- Refatorar o método `sendDeliveryEmail` para que o corpo da mensagem seja injetado dentro de `renderEmailLayout`. Dessa forma, o e-mail de links de download também herdará a cor do site e o logotipo em texto.

### 3. Criar a Classe para E-mails de Pedidos (`src/EmailOrder.php`)
- Criar uma nova classe ou arquivo responsável pelos e-mails de transação de compra.
- **`sendOrderCreatedEmail($order, $items, $total)`**: Monta o HTML informando que o pedido foi recebido, listando os produtos e informando um link para a área "Minha Conta" no site. Passa o HTML pelo `renderEmailLayout`.
- **`sendOrderStatusEmail($order, $newStatus)`**: Monta o HTML informando que houve atualização. O status será traduzido e renderizado dinamicamente. Também incluirá um link de retorno para o site. Passa o HTML pelo `renderEmailLayout`.

### 4. Conectar os Gatilhos no Sistema (`src/orders.php`)
- **Novo Pedido**: Dentro da função `createOrder(...)`, logo após o `INSERT` ser executado e o ID gerado, instanciar e chamar `sendOrderCreatedEmail`.
- **Atualização de Status**: Dentro da função `updateOrder($id, $data)`. Antes de realizar o `UPDATE`, buscar o pedido atual (`getOrder($id)`). Se a variável `$data['status']` estiver presente e for diferente do status atual salvo no banco, instanciar e chamar `sendOrderStatusEmail`.
  - *Nota*: Ao colocar isso dentro de `updateOrder`, cobrimos automaticamente as mudanças manuais feitas pelo Painel Admin e as mudanças automáticas de pagamento via Webhooks.

### 5. Padronizar E-mail de Contato (Opcional/Recomendado)
- Refatorar o e-mail disparado por `src/contact.php` para também usar `renderEmailLayout`. Isso deixará o sistema 100% padronizado visualmente, mesmo nos e-mails internos.