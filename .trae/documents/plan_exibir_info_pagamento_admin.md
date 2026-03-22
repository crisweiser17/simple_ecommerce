# Plano: Exibir Informações de Pagamento e Webhooks no Detalhe do Pedido (Admin)

## Objetivo
Atender à solicitação para exibir dados de pagamento e eventos de webhook (data/hora, valor, payload) na página de detalhes do pedido no painel de administração (`/admin/order/{id}`), com o propósito de auditoria.

## Passos da Implementação

### 1. Atualizar o arquivo `src/payments.php`
- Adicionar uma nova função `getPaymentEventsByProviderPaymentId(string $provider, string $paymentId)` para consultar a tabela `payment_events`.
- A função fará um `SELECT * FROM payment_events WHERE provider = ? AND payment_id = ? ORDER BY created_at DESC` para obter todo o histórico de webhooks e notificações vinculadas àquela transação.

### 2. Atualizar o Controlador da Rota em `index.php`
- Localizar o bloco que gerencia a rota `/admin/order/` (por volta da linha 1052).
- Após buscar os dados do pedido (`$order = getOrder($id);`), adicionar a chamada para buscar o pagamento associado: `$payment = getPaymentByOrderId($id);`.
- Se existir um pagamento com um `provider_payment_id` válido, buscar também os eventos chamando a função criada no passo 1: `$paymentEvents = getPaymentEventsByProviderPaymentId(...)`.
- Com isso, as variáveis `$payment` e `$paymentEvents` estarão disponíveis para a visualização no template.

### 3. Modificar o Template `templates/admin/order_detail.php`
- Inserir um novo painel/bloco visual chamado **"Informações de Pagamento (Auditoria)"** logo abaixo do bloco "Status & Tracking" (aproximadamente linha 83).
- **Dados Gerais do Pagamento:** Exibir os dados da tabela `payments`, como:
  - Provedor (ex: Mercado Pago, Manual Pix)
  - ID da Transação no provedor (`provider_payment_id`)
  - Status do Pagamento
  - Valor (`amount`)
  - Datas de Criação e Pagamento (`created_at`, `paid_at`)
- **Histórico de Webhooks/Eventos:** Iterar sobre o array `$paymentEvents`. Para cada evento:
  - Mostrar a Data/Hora do recebimento.
  - Mostrar o ID do Evento (`event_id`).
  - Disponibilizar um bloco de código (`<pre>`) embutido (com rolagem ou dentro de um `details`/`summary` expansível) com o `payload` bruto em formato JSON formatado. Isso facilitará a auditoria dos dados exatos enviados pelo provedor.

### 4. Validação
- Abrir um pedido no painel admin (ex: `/admin/order/15`) e verificar se o bloco de auditoria é exibido corretamente caso o pedido tenha um pagamento e webhooks registrados.
- Confirmar se o layout não foi quebrado e se o design está consistente com o restante da página (Tailwind CSS e Alpine.js).
