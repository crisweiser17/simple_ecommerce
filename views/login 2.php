<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pep-Estoque</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Pep-Estoque</h1>
            <p class="text-gray-500 text-sm">Acesso Restrito</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">Senha incorreta. Tente novamente.</span>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Senha de Acesso
                </label>
                <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="******************" required autofocus>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded w-full focus:outline-none focus:shadow-outline transition" type="submit">
                    Entrar
                </button>
            </div>
        </form>
        <p class="text-center text-gray-400 text-xs mt-6">
            &copy; <?php echo date('Y'); ?> Pep-Estoque Internacional
        </p>
    </div>
</body>
</html>
