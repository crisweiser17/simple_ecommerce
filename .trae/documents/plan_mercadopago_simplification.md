# Plano de Ação: Simplificação do Módulo Mercado Pago

## 1. Análise da Integração Atual
- A integração atual utiliza chamadas cURL diretas para a API do Mercado Pago (semelhante à abordagem do repositório `hnqca/QRCode-PIX-MercadoPago-php`), em vez de exigir a instalação do pacote oficial `mercadopago/sdk-php` via Composer. Essa abordagem cumpre perfeitamente o seu requisito de manter a **integração a mais simples possível**, evitando dependências complexas e mantendo o código leve.
- Na página de sucesso do pedido (`templates/order-success.php`), o código já está configurado para exibir exclusivamente a imagem do QR Code e a chave "Copia e Cola" para pagamentos via Mercado Pago, exatamente como solicitado.

## 2. Passos para Implementação (Configurações Mínimas no Admin)
Para atender ao pedido de ter apenas a "chave de P" e as instruções do Webhook na área de configurações, faremos as seguintes alterações:

### Passo 2.1: Simplificar o Formulário no Dashboard Admin
**Arquivo:** `templates/admin/dashboard.php`
- Remover os campos "Webhook Secret" e "Ambiente" (Sandbox/Produção), pois a própria Chave de Produção (Access Token) já define o ambiente na API do Mercado Pago.
- Renomear o campo "Access Token" para **"Access Token (Chave de Produção)"**.
- Adicionar um bloco de texto com as **Instruções do Webhook**, exibindo a URL exata do sistema (`https://<seu-dominio>/webhooks/payment/mercadopago`) e orientando o usuário a configurá-la no painel de desenvolvedores do Mercado Pago (marcando o evento "Pagamentos").

### Passo 2.2: Limpar a Lógica de Salvamento
**Arquivo:** `index.php`
- Remover as linhas que salvam `payment_mercadopago_webhook_secret` e `payment_mercadopago_environment` na rota `/admin/save-payment-settings`, já que esses campos serão removidos do formulário.

## 3. Validação
- Acessar o painel Admin para garantir que a interface de configurações do Mercado Pago está limpa, exibindo apenas o campo do Access Token e as instruções do Webhook.
- Faremos um teste de interface visualizando a página para confirmar as alterações.

---
**Resumo:** O módulo já está no caminho certo. Os ajustes focarão em limpar a interface do painel de administração para deixá-la o mais minimalista possível, focando apenas na Chave de Produção e nas instruções do Webhook, conforme você solicitou.