# Especificação de Leitura do Endpoint Get Quote (3xchange)

## Why
Há necessidade de confirmar e consolidar o entendimento do endpoint de cotação para BRL → cripto antes de qualquer integração. Isso reduz erros de implementação e garante alinhamento com os campos obrigatórios e regras de cálculo.

## What Changes
- Definir claramente os parâmetros obrigatórios do endpoint `get-quote`.
- Documentar como `currency_type` altera a interpretação de `request_amount`.
- Documentar os principais campos de resposta necessários para fluxo de cotação/pagamento.
- Registrar requisitos mínimos de uso de autenticação por cabeçalhos.

## Impact
- Affected specs: integração de cotação, cálculo de valor sender/receiver, validação de payload, consumo de API externa.
- Affected code: camada de integração HTTP, validação de request, parsing de resposta de cotação.

## ADDED Requirements
### Requirement: Compreensão do Endpoint de Cotação
O sistema SHALL mapear corretamente os parâmetros obrigatórios e opcionais do endpoint de criação de cotação.

#### Scenario: Sucesso na leitura dos parâmetros
- **WHEN** a equipe consultar a referência do endpoint
- **THEN** deve identificar `network`, `token`, `request_amount`, `address` e `currency_type` como obrigatórios, e `cover_fees` como opcional

### Requirement: Interpretação de `currency_type`
O sistema SHALL aplicar a semântica correta de `request_amount` conforme `currency_type`.

#### Scenario: Sender e Receiver
- **WHEN** `currency_type` for `sender`
- **THEN** `request_amount` representa o valor enviado pelo pagador
- **WHEN** `currency_type` for `receiver`
- **THEN** `request_amount` representa o valor que o recebedor deve receber

### Requirement: Mapeamento da Resposta de Cotação
O sistema SHALL reconhecer os campos-chave de resposta para exibição e continuidade do fluxo.

#### Scenario: Payload de resposta válido
- **WHEN** a API retornar sucesso
- **THEN** o sistema deve mapear `quote_id`, `quotation`, `expires_at`, `receiver_amount`, `sender_amount`, `payin_quote_id`, dados de `wallet` e `token`

## MODIFIED Requirements
### Requirement: Fluxo de pré-validação de integração externa
Antes de implementar integração com endpoints de cotação, a equipe deve validar requisitos de autenticação (`api_key` e `api_secret`) e estrutura de payload.

## REMOVED Requirements
### Requirement: Inferência sem documentação oficial
**Reason**: inferir comportamento do endpoint sem fonte oficial aumenta risco de inconsistência.
**Migration**: substituir qualquer suposição por mapeamento explícito da documentação e exemplos oficiais.
