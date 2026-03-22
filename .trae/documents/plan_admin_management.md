# Plano: Gerenciamento de Usuários Administradores

Atualmente, o sistema possui o conceito de `is_admin` na tabela `users` (criado durante o setup inicial para o e-mail `admin@r2.com`). No entanto, não há uma interface no painel administrativo para listar, adicionar ou remover permissões de administrador de outros usuários.

Este plano detalha como implementar essa funcionalidade no painel Admin, dentro da aba "Settings" (Configurações) ou "Customers" (Clientes). Como a aba "Settings" já trata de configurações globais e "Customers" trata dos usuários, criaremos uma subseção em "Settings" para gerenciamento rápido ou adicionaremos a funcionalidade na tabela de "Customers".

A abordagem mais integrada é permitir que o super admin gerencie isso na tela de detalhes do cliente/usuário (`/admin/customer/{id}`) ou adicionar uma nova aba "Admins" no dashboard.
Para manter a simplicidade e segurança, vamos adicionar uma aba "Admins" no Dashboard.

## Passo a Passo

### 1. Criar Rota e Lógica de Backend para Gerenciar Admins
**Arquivo:** `index.php`
- Adicionar rotas para promover e revogar acesso de admin:
  - `POST /admin/users/promote`: Recebe um `email` ou `id`, verifica se o usuário existe (se não, cria) e seta `is_admin = 1`.
  - `POST /admin/users/revoke`: Recebe um `id`, e seta `is_admin = 0` (impedindo que o admin atual revogue o próprio acesso).

### 2. Criar Função para Listar Admins
**Arquivo:** `src/user.php`
- Criar a função `getAdminUsers()`:
  - `SELECT * FROM users WHERE is_admin = 1 ORDER BY id ASC`

### 3. Atualizar o Dashboard Admin
**Arquivos:** `index.php` e `templates/admin/dashboard.php`
- No `index.php` (rota `/admin`), carregar `$adminUsers = getAdminUsers();`.
- No `dashboard.php`:
  - Adicionar um botão no menu lateral para a nova aba "Admins", logo **abaixo de Settings (Configurações)**.
  - Criar a seção (`x-show="tab === 'admins'"`).
  - Adicionar uma tabela listando os administradores atuais (`$adminUsers`).
  - Adicionar um pequeno formulário no topo desta aba para "Adicionar novo Admin", que pede apenas o E-mail. Quando submetido para `/admin/users/promote`, o backend criará o usuário (se não existir) e o definirá como admin.
  - Na tabela, adicionar um botão "Remover acesso" (que chama `/admin/users/revoke`), exceto para o usuário logado atualmente (para evitar lockout acidental).

### 4. Testes e Validação
- Acessar o painel admin.
- Ir na aba "Admins".
- Adicionar um novo e-mail (ex: `cris@example.com`).
- Fazer logout, e tentar fazer login com esse novo e-mail (enviará código).
- Entrar com o código e verificar se o menu Admin aparece.
- Entrar como admin e testar revogar o acesso do usuário recém-criado.