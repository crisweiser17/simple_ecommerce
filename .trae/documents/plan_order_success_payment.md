# Plano de Implementação Revisado: Exibição de Pagamento e Instruções Personalizadas

Este plano detalha as alterações necessárias para adaptar a tela de pedido recebido (`/order-success`), o painel de administração e a geração de QR Code para o módulo Pix Manual, atendendo a todos os requisitos solicitados.

## Passos para Implementação

### 1. Instruções de Pagamento com Editor WYSIWYG
**Arquivos:** `templates/admin/dashboard.php` e `index.php`
- **Admin (Frontend):** Na seção de Configurações de Pagamento, adicionar um checkbox "Habilitar Instruções de Pagamento".
- Adicionar o editor Quill (WYSIWYG) para o campo de texto das instruções (carregando os scripts/estilos do Quill via CDN, semelhante ao que já é feito em `product-form.php`).
- Criar um campo hidden que receberá o HTML gerado pelo Quill no momento do envio do formulário.
- **Admin (Backend):** Em `index.php` (rota `/admin/save-payment-settings`), receber e salvar o HTML das instruções via `updateSetting('payment_instructions_text', $_POST['...'])` e o status do checkbox.
- **Frontend (`order-success.php`):** Se a opção estiver habilitada, exibir a área de "Instruções de Pagamento" renderizando o conteúdo HTML (preservando negritos, listas, etc. criados no WYSIWYG).

### 2. Ajustes na Configuração do Pix Manual
**Arquivos:** `templates/admin/dashboard.php`, `index.php` e `src/payment_providers/manual_pix_gateway.php`
- **Admin:** Na configuração do "Manual PIX", remover o campo "Cidade". Deixar **apenas**:
  1. Chave PIX.
  2. Nome da Empresa / Titular da Conta.
- **Backend:** Remover qualquer referência a salvar ou carregar o campo "Cidade".

### 3. Geração de QR Code e Payload para Pix Manual
**Arquivo:** `src/payment_providers/manual_pix_gateway.php`
- Na função `createPixCharge()`, implementar um gerador de payload EMV (BR Code) válido para PIX Estático, utilizando:
  - A Chave PIX informada.
  - O Nome do Titular.
  - O Valor Total do Pedido (`$orderData['total']`).
- **Retorno do Gateway:**
  - `pix_qr_code`: Retornar uma URL de imagem chamando um serviço gerador de QR Code (ex: `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={BR_CODE}`) para que o cliente possa escanear.
  - `pix_copy_paste`: Retornar o próprio BR Code (caso queira usar) ou a chave.
  - `payload`: Adicionar a "Chave PIX" original informada, para exibição direta.

### 4. Refatoração da Tela de Pedido Recebido (`order-success.php`)
**Arquivo:** `templates/order-success.php`
- **Mercado Pago (Se habilitado e utilizado no pedido):**
  - Exibir a imagem do QR Code gerada pelo Mercado Pago.
  - Exibir o campo da chave Copia e Cola do Mercado Pago com o botão de copiar.
  - **Exibir o Valor Total a ser pago** em destaque (ex: `R$ number_format($order['total_amount'], 2, ',', '.')`).
- **Pix Manual (Se habilitado e utilizado no pedido):**
  - Ajustar a tag `<img>` para suportar URLs (como a do qrserver) ou base64, exibindo o QR Code gerado.
  - **Exibir o Valor Total a ser pago** em destaque.
  - **Exibir a Chave PIX informada** de forma clara, com um botão "Copiar Chave PIX" que copia exatamente a chave informada para a área de transferência.
- Remover a lógica antiga condicional que misturava os dados dos dois provedores e substituí-la por blocos específicos para cada método, garantindo que o valor total e as informações exigidas apareçam corretamente em seus respectivos cenários.

## Resultado Esperado
- O admin terá um editor rico (WYSIWYG) para as instruções de pagamento.
- O Pix Manual pedirá apenas Chave e Nome do Titular, e na tela final gerará magicamente um QR Code escaneável, além de exibir a chave nua com botão de copiar e o valor total.
- O Mercado Pago exibirá seu QR Code padrão, o Copia e Cola e o valor total.