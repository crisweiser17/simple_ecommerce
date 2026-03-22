# Plano de Ação: Permitir Login sem Pedidos Anteriores

## 1. Objetivo
Modificar o sistema para permitir que qualquer usuário (com ou sem pedidos anteriores) receba o código de login por e-mail. Isso possibilita que novos usuários façam login antes de finalizar a primeira compra no carrinho, ou apenas criem/completem seu cadastro clicando em "Sign In".

## 2. Arquivos Afetados
- `index.php`

## 3. Passos de Implementação

1. **Atualizar a rota `/api/login-request.php` no arquivo `index.php`:**
   - Localizar o bloco de código correspondente ao endpoint `/api/login-request.php`.
   - Remover a lógica de validação que exige a existência de pedidos na tabela `orders` ou privilégios de administrador na tabela `users` (`$orderCount > 0 || $isAdmin`).
   - Remover as consultas SQL associadas a essa verificação, pois não serão mais necessárias.
   - Manter apenas a chamada direta para `generateLoginToken($email)` (que já lida com a criação automática do usuário no banco de dados caso seja um e-mail novo).
   - Retornar sempre a resposta de sucesso em formato JSON com a mensagem de que o código foi enviado.

### Código Antes:
```php
// Check if user has orders or is admin
global $pdo;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_email = ?");
$stmt->execute([$email]);
$orderCount = $stmt->fetchColumn();

$stmtAdmin = $pdo->prepare("SELECT is_admin FROM users WHERE email = ?");
$stmtAdmin->execute([$email]);
$isAdmin = $stmtAdmin->fetchColumn();

if ($orderCount > 0 || $isAdmin) {
    $token = generateLoginToken($email);
    echo json_encode(['success' => true, 'message' => __('Login token sent to your email. Please enter the received code in the field below')]);
} else {
    echo json_encode(['success' => false, 'message' => __('This email does not contain orders')]);
}
```

### Código Depois:
```php
$token = generateLoginToken($email);
echo json_encode(['success' => true, 'message' => __('Login token sent to your email. Please enter the received code in the field below')]);
```

## 4. Resultado Esperado
Qualquer visitante que inserir seu e-mail no formulário do carrinho de compras ou na página de Sign In receberá um código de autenticação, sendo cadastrado automaticamente como cliente no banco de dados e podendo acessar ou completar seus dados.