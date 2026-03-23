# Plano de Implementação: Validação de E-mail

## Objetivo
Varrer a codebase e inserir validação consistente de e-mail em todos os pontos onde o e-mail do usuário é capturado, seja no backend (PHP) ou no frontend (HTML/AlpineJS).

## Pontos de Alteração Identificados

### 1. Backend (PHP - `index.php`)
Precisamos validar as entradas de e-mail usando `filter_var($email, FILTER_VALIDATE_EMAIL)`. Caso o e-mail seja inválido, o sistema deve retornar um erro amigável.

Rotas que precisam de atualização no `index.php`:
- **`/api/login-request.php`**: Validar `$input['email']` antes de gerar o token.
- **`/api/express-checkout`**: Validar `$input['email']` antes de prosseguir com a criação do pedido.
- **`/checkout`**: Validar `$_POST['email']` antes de prosseguir com o pedido.
- **`/contact/send`**: Validar `$_POST['email']` antes de enviar a mensagem de contato.
- **`/account` (POST)**: Validar `$_POST['email']` antes de atualizar o perfil do usuário.
- **`/admin/users/promote`**: (Já possui validação, mas é bom garantir que segue o padrão).

### 2. Frontend (HTML / AlpineJS)
Garantir que todos os campos de input de e-mail no HTML possuam o atributo `type="email"` e `required` (onde aplicável) para aproveitar a validação nativa do navegador. E no AlpineJS (onde o form é enviado via fetch), verificar se faz sentido adicionar uma validação regex antes de fazer a requisição para evitar chamadas desnecessárias à API.

Arquivos a serem revisados e ajustados:
- **`templates/public/product_single.php`**: Adicionar validação de formato no submit do `singleCheckout()`.
- **`templates/public/checkout_express.php`**: Adicionar validação de formato no submit do `expressCheckout()`.
- **`templates/cart.php`**: Verificar inputs de login e do form principal.
- **`templates/login.php`**: Validar input antes de enviar o request de token.
- **`templates/contact.php`**: Confirmar se o formulário tem os atributos corretos.
- **`templates/account.php`**: Confirmar se o formulário tem os atributos corretos.

### 3. Idiomas / Traduções (`lang/pt.php` e `lang/en.php`)
Garantir que exista uma string de erro unificada para e-mail inválido, como:
- `pt.php`: `'Invalid email format' => 'Formato de e-mail inválido'`
- `en.php`: `'Invalid email format' => 'Invalid email format'`

## Passos da Execução
1. Atualizar as chaves de idioma nos arquivos `lang/pt.php` e `lang/en.php`.
2. Inserir as validações com `filter_var()` no arquivo `index.php` para cada uma das rotas listadas.
3. Revisar os arquivos de template no frontend para adicionar validações do lado do cliente (Alpine.js ou atributos HTML nativos `type="email"` e `pattern`).
4. Testar os fluxos (Login, Checkout, Express Checkout, Contato, Perfil) tentando submeter um e-mail inválido para garantir que o erro correto é retornado.