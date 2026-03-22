# Plano de implementação — Admin > Clientes com status de pedidos

## Objetivo
Implementar a aba **Clientes** no admin para:
- listar clientes existentes;
- indicar quais têm pedido e quais não têm;
- mostrar quantidade de pedidos por cliente;
- exibir nome, e-mail e WhatsApp na listagem;
- permitir abrir uma visualização completa dos dados do cliente.

## Escopo técnico
- Backend em PHP com SQLite.
- Reaproveitar o dashboard admin atual e o padrão de rotas já existente.
- Não alterar fluxo de login nem estrutura visual global fora da aba Clientes.

## Etapas detalhadas

1. **Mapear e padronizar fonte de dados de clientes para o admin**
   - Usar `users` como base da listagem de clientes (garante exibição de clientes com e sem pedido).
   - Cruzar com `orders` por e-mail (`users.email = orders.customer_email`) para agregações de pedidos.
   - Tratar valores ausentes de nome/whatsapp com fallback seguro para não quebrar renderização.

2. **Criar função de consulta agregada para a listagem de clientes**
   - Adicionar no backend (arquivo de domínio de usuários) uma função para retornar:
     - `id`, `name`, `email`, `whatsapp`;
     - `orders_count`;
     - `has_orders` (derivado de `orders_count > 0`);
     - data do último pedido (quando existir), para ordenação útil.
   - Definir ordenação padrão priorizando clientes com pedido e, dentro disso, mais recentes.
   - Garantir que a consulta continue funcionando quando não houver pedidos na base.

3. **Criar função de detalhe completo do cliente para visualização**
   - Implementar consulta por `user_id` retornando todos os dados cadastrais do cliente.
   - Incluir, no mesmo retorno, resumo de pedidos (quantidade total e último pedido).
   - Garantir resposta consistente para cliente sem pedido.

4. **Integrar dados de clientes na rota do dashboard admin**
   - Na rota `/admin`, carregar a coleção de clientes junto dos demais dados do painel.
   - Manter proteção `requireAdmin()` e padrão de renderização existente.
   - Preparar dados para uso direto no template, sem lógica pesada na view.

5. **Implementar seção visual da aba Clientes no dashboard**
   - Criar bloco `x-show="tab === 'customers'"` no template admin.
   - Renderizar tabela/lista com colunas:
     - Nome
     - E-mail
     - WhatsApp
     - Pedidos (contagem)
     - Status (com pedido / sem pedido)
     - Ação de visualização
   - Manter consistência com estilos e componentes já usados no dashboard.

6. **Implementar visualização completa do cliente ao clicar em “Visualizar”**
   - Adotar padrão já existente no projeto para detalhe:
     - opção A: rota dedicada de detalhe no admin;
     - opção B: modal/expansão na própria aba.
   - Priorizar a abordagem mais aderente ao padrão atual do admin após comparar com o fluxo de “View Order”.
   - Exibir no detalhe:
     - dados pessoais e de contato;
     - endereço completo;
     - indicadores de pedidos (tem/não tem, quantidade total, último pedido).

7. **Adicionar textos de interface com suporte de idioma atual**
   - Incluir chaves de idioma necessárias em `lang/pt.php` e `lang/en.php`.
   - Reaproveitar função de tradução existente no template.
   - Evitar hardcode de novos textos fora do sistema de i18n.

8. **Validar qualidade técnica e funcional**
   - Executar checagem de sintaxe PHP nos arquivos alterados.
   - Rodar verificações de qualidade/lint disponíveis no projeto.
   - Validar no navegador o fluxo completo:
     - abrir aba Clientes;
     - conferir status e contagem de pedidos;
     - abrir visualização de cliente com e sem pedido;
     - confirmar ausência de regressões nas abas existentes.

9. **Verificação de casos de borda**
   - Cliente sem nome/WhatsApp cadastrado.
   - Cliente sem pedidos.
   - Cliente com múltiplos pedidos.
   - Base vazia (sem usuários) sem erro de renderização.

## Critérios de aceite
- Aba **Clientes** visível e funcional no admin.
- Cada cliente exibe **nome, e-mail, WhatsApp, quantidade de pedidos**.
- Status de pedido por cliente visível (tem pedido / não tem pedido).
- Ação de **visualizar** abre todos os dados do cliente.
- Funciona para clientes com e sem pedido.
- Sem quebra nas funcionalidades existentes do painel admin.
