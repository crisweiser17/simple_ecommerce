<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">Histórico de Atividades</h1>
    <a href="/" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
        &larr; Voltar
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Itens</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($movements as $mov): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo date('d/m/Y H:i', strtotime($mov['created_at'])); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <?php 
                    $colors = [
                        'PURCHASE_CHINA' => 'bg-blue-100 text-blue-800',
                        'TRANSFER_BRAZIL' => 'bg-purple-100 text-purple-800',
                        'SALE_USA' => 'bg-green-100 text-green-800',
                        'SALE_BRAZIL' => 'bg-green-100 text-green-800',
                    ];
                    $labels = [
                        'PURCHASE_CHINA' => 'Compra China',
                        'TRANSFER_BRAZIL' => 'Transf. Brasil',
                        'SALE_USA' => 'Venda USA',
                        'SALE_BRAZIL' => 'Venda Brasil',
                    ];
                    $type = $mov['type'];
                    $color = $colors[$type] ?? 'bg-gray-100 text-gray-800';
                    $label = $labels[$type] ?? $type;
                    ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color; ?>">
                        <?php echo $label; ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <?php echo htmlspecialchars($mov['description']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php echo $mov['item_count']; ?> itens
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                    <a href="/movement/view?id=<?php echo $mov['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-2" title="Visualizar">Visualizar</a>
                    <a href="/movement/edit?id=<?php echo $mov['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-2" title="Editar">Editar</a>

                    <form action="/movement/delete" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta movimentação? O estoque será revertido.');" class="inline-block">
                        <input type="hidden" name="id" value="<?php echo $mov['id']; ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900 font-bold" title="Excluir">
                            Excluir
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($movements)): ?>
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500 text-sm">Nenhuma atividade encontrada.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Anterior
            </a>
            <?php else: ?>
            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50">
                Anterior
            </span>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Próximo
            </a>
            <?php else: ?>
            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50">
                Próximo
            </span>
            <?php endif; ?>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando página <span class="font-medium"><?php echo $page; ?></span> de <span class="font-medium"><?php echo $totalPages; ?></span> (Total: <?php echo $total; ?>)
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <!-- First -->
                    <?php if ($page > 1): ?>
                    <a href="?page=1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Primeira</span>
                        &laquo;
                    </a>
                    <a href="?page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Anterior</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <?php endif; ?>

                    <!-- Current Page Indicator -->
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                        <?php echo $page; ?>
                    </span>

                    <!-- Next -->
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Próximo</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="?page=<?php echo $totalPages; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Última</span>
                        &raquo;
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
