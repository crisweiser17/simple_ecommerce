<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pep-Estoque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        /* Global Input Styling for larger touch targets */
        input[type="text"], input[type="number"], select, textarea {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/" class="text-xl font-bold">Pep-Estoque</a>
            <div class="flex items-center space-x-4">
                <a href="/" class="hover:text-blue-200">Dashboard</a>
                <a href="/products" class="hover:text-blue-200">Produtos</a>
                <a href="/suppliers" class="hover:text-blue-200">Fornecedores</a>
                
                <!-- Dropdown Movimentação -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="flex items-center hover:text-blue-200 focus:outline-none gap-1">
                        Movimentação
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 text-gray-800" x-transition x-cloak>
                        <a href="/purchase" class="block px-4 py-2 hover:bg-gray-100">Compra (China)</a>
                        <a href="/transfer" class="block px-4 py-2 hover:bg-gray-100">Transf. (Brasil)</a>
                        <a href="/sales" class="block px-4 py-2 hover:bg-gray-100">Vendas</a>
                    </div>
                </div>

                <a href="/logout" class="ml-4 text-red-200 hover:text-white border border-red-400 hover:bg-red-500 hover:border-transparent rounded px-3 py-1 text-sm transition">Sair</a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        <?php if (isset($content)) echo $content; ?>
    </main>

    <footer class="bg-gray-800 text-white text-center py-4 mt-auto">
        <p>&copy; <?php echo date('Y'); ?> Pep-Estoque Internacional</p>
    </footer>
</body>
</html>
