# Plano de Ação: Sessão Persistente de Verdade (24 Horas)

## 1. Objetivo
Garantir que, uma vez feito o login, o usuário permaneça logado por 24 horas consecutivas em **todas as rotas** do site (incluindo "My Profile"). Resolver o problema de perda de sessão silenciosa.

## 2. Análise do Problema (Por que está pedindo login novamente?)
Apesar de termos configurado o `session.gc_maxlifetime` e o `session_set_cookie_params()` para 86400 segundos (24h) no `index.php`, o PHP (especialmente no macOS/servidor embutido) muitas vezes possui uma pasta de sessões compartilhada (ex: `/tmp` ou a pasta padrão do sistema) ou gerida por outros processos que limpam os arquivos de sessão aleatoriamente de acordo com configurações globais do `php.ini`.

Se o arquivo físico da sessão for excluído do servidor antes de 24h, mesmo que o navegador do usuário ainda tenha o cookie com validade de 24h, o PHP vai olhar pro ID daquele cookie, não vai achar o arquivo correspondente, e vai gerar uma sessão em branco nova – o que desloga o usuário e exige um novo login ao acessar rotas protegidas como `/account` (My Profile).

## 3. Arquivos Afetados
- `index.php`
- `.gitignore` (para não comitarmos as sessões)

## 4. Passos de Implementação

### Passo 1: Criar um diretório de sessões exclusivo para o projeto
Isolar o salvamento das sessões dentro da pasta do próprio projeto para que o sistema operacional não as delete por acidente.
- A pasta `sessions/` será criada na raiz do projeto (já foi criada via terminal nos testes preparatórios).
- Adicionaremos `sessions/` ao `.gitignore` para evitar poluir o controle de versão.

### Passo 2: Atualizar o `index.php` para usar o caminho customizado
Antes do `session_start()`, adicionaremos a diretiva `session_save_path()` para forçar o PHP a salvar as sessões em nossa pasta isolada, com a validade que definimos.

```php
// Configure session directory and lifetime to 24 hours (86400 seconds)
$sessionPath = __DIR__ . '/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### Passo 3: Limpar os dados estáticos antigos
O código já está chamando `getUser($_SESSION['user_id'])` em todas as rotas (Carrinho, Account, etc). Desde que a sessão não seja destruída no backend, o comportamento permanecerá logado por exatas 24 horas ininterruptas.

## 5. Resultado Esperado
Nenhuma rota perderá mais o login aleatoriamente. O usuário clica em "My Profile", "Cart" ou "Checkout" e, desde que esteja no intervalo de 24 horas, será reconhecido instantaneamente.