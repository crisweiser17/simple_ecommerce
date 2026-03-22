# Plano: checkout com botão único no carrinho e ações condicionais no sucesso

## Objetivo funcional
- No carrinho (`/cart`), manter apenas um botão: **Finalizar pedido**.
- Na página seguinte (`/order-success`):
  - Mostrar **Enviar para o WhatsApp** somente se a opção estiver habilitada.
  - Mostrar **Baixar pedido em PDF** sempre.
  - Mostrar conteúdo de pagamento (QR Code, PIX copia e cola e/ou instruções) somente se houver módulo de pagamento habilitado.

## Escopo técnico
- Ajustar UI do carrinho para remover múltiplas ações de envio.
- Ajustar fluxo backend de `/checkout` para redirecionamento único para `/order-success`.
- Introduzir checagem efetiva de módulos de pagamento habilitados em runtime (não só no admin).
- Ajustar renderização de `order-success` para blocos condicionais de WhatsApp/PDF/pagamento.
- Preservar fluxo atual de criação de pedido e compatibilidade com provedores Mercado Pago e Manual PIX.

## Arquivos alvo
- `templates/cart.php`
- `index.php`
- `src/payment_engine.php`
- `templates/order-success.php`

## Plano de implementação detalhado

### 1) Simplificar o carrinho para botão único de conclusão
- Em `templates/cart.php`, no formulário de checkout:
  - Remover os dois botões de ação atuais (`action=pdf` e `action=whatsapp`).
  - Inserir apenas um botão submit com rótulo **Finalizar pedido**.
  - Remover dependência de `name="action"` no submit, já que a escolha não será mais feita no carrinho.
- Manter intactos os campos de cliente/endereço e o envio de itens/total.

### 2) Unificar o backend de checkout para redirecionar sempre ao sucesso
- Em `index.php` na rota `POST /checkout`:
  - Remover lógica de branch por `$_POST['action']`.
  - Sempre redirecionar para `/order-success?id={orderId}`.
- Preservar criação do pedido (`createOrder`) e as atualizações já existentes de pagamento/status.

### 3) Aplicar módulos habilitados de pagamento no runtime
- Em `src/payment_engine.php`, adicionar métodos de suporte:
  - Método para listar módulos habilitados a partir de `payment_provider_modules`.
  - Método para verificar se um provider específico está habilitado.
  - Método para informar se existe ao menos um módulo de pagamento habilitado.
- Ajustar `createPixCharge` para respeitar habilitação:
  - Se o provider ativo não estiver habilitado, retornar falha controlada com mensagem clara.
  - Se estiver habilitado, seguir fluxo atual normalmente.
- Em `index.php` no checkout:
  - Antes de iniciar cobrança PIX, verificar se existe módulo habilitado.
  - Sem módulos habilitados: não tentar criar cobrança; seguir com pedido e sucesso sem bloco de pagamento.
  - Com módulos habilitados: tentar cobrança como hoje e persistir pagamento/erro como já ocorre.

### 4) Reorganizar `order-success` por disponibilidade de recursos
- Em `templates/order-success.php`:
  - Manter botão de PDF sempre visível.
  - Exibir botão de WhatsApp somente se `enable_whatsapp_button=1` e houver número configurado.
  - Exibir seção de pagamento somente quando o pedido tiver contexto de pagamento habilitado (provider permitido e/ou dados de cobrança disponíveis).
  - Dentro da seção de pagamento:
    - Mostrar QR Code quando disponível.
    - Mostrar código PIX copia e cola quando disponível.
    - Mostrar instruções textuais quando não houver QR/copia-cola, usando dados disponíveis do pagamento (ex.: payload/manual).
  - Garantir que polling de status rode apenas quando seção de pagamento estiver ativa.

### 5) Validação técnica obrigatória (sem commit)
- Executar validação sintática PHP dos arquivos alterados.
- Rodar verificações existentes do projeto (lint/testes aplicáveis) para garantir regressão zero.
- Subir a aplicação local e validar no navegador:
  - Carrinho com botão único “Finalizar pedido”.
  - `order-success` com PDF sempre visível.
  - WhatsApp aparecendo/desaparecendo conforme setting.
  - Blocos de pagamento aparecendo apenas quando módulo habilitado, incluindo QR/copia-cola/instruções.
- Validar cenários mínimos:
  - Sem módulos habilitados.
  - Módulo Manual PIX habilitado.
  - Módulo Mercado Pago habilitado (ou falha controlada sem credencial, com comportamento consistente).

## Critérios de aceite
- O carrinho não apresenta mais botões separados para PDF/WhatsApp; apenas **Finalizar pedido**.
- `POST /checkout` não depende de `action` e sempre leva para `/order-success`.
- Em `order-success`, PDF é sempre exibido.
- Em `order-success`, WhatsApp só aparece quando habilitado nas configurações.
- Em `order-success`, informações de pagamento só aparecem quando houver módulo de pagamento habilitado e contexto aplicável.
- Não há quebra no fluxo de criação de pedido, status e integração de pagamento já existente.
