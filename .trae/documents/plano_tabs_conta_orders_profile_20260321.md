# Plano: Separar visualização de `#orders` e `#profile` em `/account`

## Objetivo
Garantir que:
- `http://127.0.0.1:8081/account#orders` exiba somente a seção de pedidos.
- `http://127.0.0.1:8081/account#profile` exiba somente a seção de informações pessoais.

## Contexto atual mapeado
- A rota `/account` já carrega dados de usuário e pedidos em `index.php`.
- O template `templates/account.php` renderiza as duas seções ao mesmo tempo.
- Os links `#profile` e `#orders` atualmente funcionam como âncoras de scroll, sem controle real de visibilidade por hash.

## Estratégia de implementação
1. Revisar `templates/account.php` para identificar blocos da seção de perfil e da seção de pedidos.
2. Introduzir um estado de aba ativa no frontend da página de conta, derivado de `window.location.hash`.
3. Aplicar visibilidade condicional para renderização:
   - Exibir perfil apenas quando a aba ativa for `profile`.
   - Exibir pedidos apenas quando a aba ativa for `orders`.
4. Sincronizar a navegação lateral com o estado:
   - Clique em “Profile” ativa `#profile`.
   - Clique em “Orders” ativa `#orders`.
5. Adicionar escuta de mudança de hash (`hashchange`) para atualizar o conteúdo ao trocar hash manualmente na URL.
6. Definir fallback seguro:
   - Hash inválido ou vazio abre `profile` por padrão.
7. Ajustar estado visual dos links laterais (ativo/inativo) para refletir a aba selecionada.
8. Preservar compatibilidade com o fluxo de update de perfil (POST em `/account`) sem alterar regras de backend.

## Arquivos previstos para alteração
- `templates/account.php` (principal).
- `lang/pt.php` e `lang/en.php` somente se surgir novo texto de interface necessário para estado visual.

## Critérios de aceite
- Em `/account#orders`, a seção de perfil não aparece.
- Em `/account#profile`, a seção de pedidos não aparece.
- Ao alternar entre links laterais, a URL e o conteúdo ficam sincronizados.
- Ao acessar `/account` sem hash, a aba inicial é `#profile`.
- Não há regressão no envio/atualização dos dados pessoais.

## Validação planejada
1. Executar lint PHP nos arquivos alterados.
2. Abrir preview da aplicação.
3. Testar manualmente:
   - `/account`
   - `/account#profile`
   - `/account#orders`
   - alternância entre abas via clique e troca direta de hash.
4. Conferir ausência de novos diagnósticos no editor.
