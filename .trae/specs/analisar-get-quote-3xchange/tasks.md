# Tasks
- [x] Task 1: Consolidar requisitos do endpoint de cotação
  - [x] SubTask 1.1: Confirmar parâmetros obrigatórios e opcionais do payload
  - [x] SubTask 1.2: Confirmar regras de interpretação de `currency_type`
  - [x] SubTask 1.3: Confirmar autenticação por `api_key` e `api_secret`

- [x] Task 2: Definir contrato de resposta para consumo interno
  - [x] SubTask 2.1: Mapear campos de `quote` necessários para o fluxo
  - [x] SubTask 2.2: Mapear campos de `wallet` e `token`
  - [x] SubTask 2.3: Definir critérios mínimos de sucesso para resposta HTTP 200

- [x] Task 3: Validar consistência para próxima etapa de integração
  - [x] SubTask 3.1: Revisar se requisitos cobrem cenário sender e receiver
  - [x] SubTask 3.2: Revisar se requisitos cobrem cálculo com `cover_fees`
  - [x] SubTask 3.3: Preparar checklist de validação da implementação futura

# Task Dependencies
- Task 2 depends on Task 1
- Task 3 depends on Task 2
