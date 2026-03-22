# Plano de Implementação — Motor de Pagamento PIX Multi‑Vendor

## Objetivo
Criar um motor de pagamento para checkout que:
- Recebe dados do pedido (principalmente valor total e identificação do pedido);
- Cria cobrança PIX via API do vendor selecionado;
- Retorna QR Code e chave “copia e cola” para o front;
- Escuta confirmação de pagamento (webhook/listener) e atualiza status do pedido;
- Persiste no banco todas as informações relevantes de transação;
- Permite configurar múltiplos módulos/vendors e suas credenciais no painel administrativo.

## Princípios de Arquitetura
1. **Abstração por interface**: o checkout chama um serviço único do motor, sem acoplamento ao vendor.
2. **Vendor plugável**: cada gateway implementa o mesmo contrato de criação de cobrança e consulta/notificação.
3. **Idempotência e rastreabilidade**: webhook e criação de cobrança devem evitar duplicidade e manter histórico.
4. **Persistência orientada a auditoria**: salvar request/response essenciais, status e metadados de pagamento por pedido.
5. **Segurança**: validar assinatura/segredo de webhook e nunca expor segredos em resposta/renderização.

## Escopo Técnico

### 1) Normalização de schema e modelagem de pagamentos
1. Revisar divergência entre `installer.php` e `setup_db.php` para garantir base consistente.
2. Criar tabela dedicada de pagamentos (recomendado) para desacoplar de `orders`:
   - `id`, `order_id`, `provider`, `provider_payment_id`, `provider_reference`, `amount`, `currency`;
   - `pix_qr_code`, `pix_copy_paste`, `pix_expires_at`;
   - `status` (`pending`, `authorized`, `paid`, `failed`, `expired`, `canceled`);
   - `gateway_payload` (JSON), `gateway_last_event`, `paid_at`, `created_at`, `updated_at`.
3. Adicionar índice/unique para idempotência:
   - unique em (`provider`, `provider_payment_id`) e/ou (`provider`, `provider_reference`).
4. Manter em `orders` somente relacionamento e status de negócio:
   - `payment_status`, `paid_at`, `payment_provider` (ou derivar pela tabela `payments`).

### 2) Camada de domínio para pagamentos
1. Criar módulo `src/payments.php` com operações:
   - criar registro inicial de pagamento;
   - atualizar status/metadados;
   - buscar por `order_id`, `provider_payment_id` e `provider_reference`;
   - registrar eventos recebidos no webhook.
2. Definir regras de transição de status (ex.: `pending -> paid` permitido; `paid` imutável para regressão).
3. Garantir atualização transacional de `orders.status` e `orders.payment_status` quando pagamento confirmar.

### 3) Contrato do motor e drivers de vendor
1. Criar contrato (interface por convenção PHP) em `src/payment_gateway.php` com métodos:
   - `createPixCharge(array $orderData): array`
   - `parseWebhook(array|string $payload, array $headers): array`
   - `verifyWebhookSignature(array|string $payload, array $headers): bool`
   - `getProviderName(): string`
2. Criar `PaymentEngine` que:
   - lê vendor ativo em `settings`;
   - carrega driver correspondente;
   - recebe total do pedido e devolve dados de pagamento padronizados.
3. Implementar primeiro driver real (ex.: Mercado Pago) e manter estrutura pronta para novos vendors.
4. Definir formato de resposta unificado para checkout:
   - `provider`, `transaction_id`, `status`, `pix.qr_code`, `pix.copy_paste`, `pix.expires_at`.

### 4) Configuração administrativa de módulos/vendors
1. Evoluir `settings` para armazenar:
   - vendor ativo (`payment_provider_active`);
   - lista/módulos disponíveis (`payment_provider_modules`);
   - credenciais por provider (token, client_id, secret, webhook_secret, ambiente).
2. Atualizar tela de admin (`templates/admin/dashboard.php`) com seção “Pagamentos”:
   - seleção do provider ativo;
   - campos de credenciais por provider;
   - opção de habilitar/desabilitar módulo.
3. Garantir persistência segura:
   - mascarar segredos no front admin;
   - manter segredos fora de logs e respostas públicas.

### 5) Integração no fluxo de checkout
1. Refatorar rota `/checkout` em `index.php` para:
   - criar pedido com status inicial de pagamento pendente;
   - invocar `PaymentEngine->createPixCharge(...)`;
   - persistir retorno na tabela de pagamentos;
   - redirecionar para página de sucesso com dados PIX.
2. Ajustar `templates/order-success.php` para mostrar:
   - QR Code PIX;
   - chave copia-e-cola;
   - status atual e instruções.
3. Preservar fallback seguro quando provider não estiver configurado (mensagem de indisponibilidade).

### 6) Listener/Webhook para confirmação de pagamento
1. Criar rota dedicada no router:
   - `POST /webhooks/payment/{provider}` (ou equivalente estável).
2. Fluxo do listener:
   - validar assinatura/autenticidade;
   - normalizar evento recebido no formato interno;
   - aplicar idempotência por `event_id`/`provider_payment_id`;
   - atualizar `payments` e `orders` para status `paid`;
   - persistir payload/evento para auditoria.
3. Retorno HTTP adequado para reentrega do provider (200 em sucesso idempotente; 4xx/5xx quando aplicável).

### 7) Consulta de status para front (opcional recomendado)
1. Criar endpoint simples para polling no front:
   - `GET /api/orders/{id}/payment-status`.
2. Permitir atualização visual automática da tela de sucesso até confirmar pagamento.

### 8) Qualidade, testes e validação end-to-end
1. Testar fluxo completo:
   - criação do pedido -> criação da cobrança PIX -> render QR/copia-cola.
2. Testar webhook:
   - evento válido atualiza pedido para pago;
   - evento duplicado não duplica efeitos.
3. Testar cenários de erro:
   - credencial inválida, timeout da API, assinatura inválida.
4. Validar lint/diagnósticos e realizar teste manual em browser preview.

## Sequência de Execução (ordem recomendada)
1. Normalizar schema e criar estrutura de persistência de pagamentos.
2. Implementar domínio `payments` + regras de status.
3. Implementar contrato do motor + `PaymentEngine` + primeiro driver.
4. Integrar checkout e tela de sucesso para PIX.
5. Implementar webhook/listener com validação e idempotência.
6. Implementar configuração admin de vendors/credenciais.
7. Implementar endpoint de consulta de status (se aplicado).
8. Executar bateria de testes e validação final no navegador.

## Critérios de Aceite
1. Pedido criado com pagamento PIX pendente e dados de QR/copia-cola disponíveis ao cliente.
2. Confirmação via webhook altera pagamento para `paid` e pedido para status de pago/processando.
3. Informações de transação e eventos ficam persistidas e auditáveis por pedido.
4. É possível escolher vendor ativo e configurar credenciais no admin.
5. Arquitetura permite adicionar novo vendor sem alterar o fluxo principal de checkout.
