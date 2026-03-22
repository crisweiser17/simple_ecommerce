# Plano de Ação: Manter o Login Ativo (Sessão de 24h) e Corrigir Tradução

## 1. Objetivo
Aumentar a duração da sessão de login para 24 horas (86400 segundos) para evitar que o usuário precise solicitar e inserir o código repetidas vezes. Além disso, ajustar a tradução do botão/texto "Sign In" para o português.

## 2. Arquivos Afetados
- `index.php` (para configurar a duração da sessão globalmente antes de qualquer output).
- `lang/pt.php` (para adicionar a tradução que está faltando para a chave "Sign in").

## 3. Análise do Problema Atual
1. **Sessão Expirando:** Atualmente o PHP está usando as configurações padrão do servidor, onde `session.cookie_lifetime` é 0 (a sessão expira ao fechar o navegador) e o `session.gc_maxlifetime` é de apenas 24 minutos (1440 segundos). Isso faz com que a sessão seja perdida muito rapidamente.
2. **Tradução:** No arquivo `templates/layout.php` na linha 59, o texto está sendo chamado como `__('Sign in')` com "i" minúsculo. No arquivo `lang/pt.php` (e no layout do próprio botão de Sign In), a chave de tradução cadastrada é `__('Sign In')` com "I" maiúsculo. O "i" minúsculo está impedindo o mapeamento da tradução.

## 4. Passos de Implementação

### Passo 1: Configurar a Sessão (24 Horas)
No topo do arquivo `index.php`, **antes de qualquer require** que inicialize a sessão (como `src/i18n.php` ou `src/auth.php`), adicionaremos os parâmetros de configuração do tempo de vida do cookie e da coleta de lixo da sessão:

```php
// Definir tempo de vida da sessão para 24 horas (86400 segundos)
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```
*Atenção:* Também removeremos as chamadas isoladas a `session_start()` dentro de `src/auth.php` e `src/i18n.php` se for mais adequado, ou deixaremos a do `index.php` como a principal. A melhor prática é centralizar o início da sessão no ponto de entrada (`index.php`).

### Passo 2: Corrigir a Tradução do "Sign in"
Atualizar a chave de tradução nos arquivos de idioma, incluindo a versão com o "i" minúsculo, ou simplesmente adicionar essa variação para evitar problemas de _case-sensitivity_.

Em `lang/pt.php`:
```php
'Sign in' => 'Entrar',
```

Em `lang/en.php`:
```php
'Sign in' => 'Sign in',
```

## 5. Resultado Esperado
- O usuário permanecerá logado no sistema por 24 horas, mesmo que feche o navegador ou saia do site, evitando o aborrecimento de pedir novos códigos frequentemente.
- O botão "Sign in" no cabeçalho aparecerá como "Entrar" quando o idioma estiver configurado para português.