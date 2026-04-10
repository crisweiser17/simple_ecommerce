<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Visualizar Movimentação #<?php echo $movement['id']; ?></h1>
        <div class="flex gap-2">
            <a href="/movement/edit?id=<?php echo $movement['id']; ?>" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Editar</a>
            <a href="javascript:history.back()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Voltar</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Detalhes da Movimentação</h3>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Tipo</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-bold"><?php echo $movement['type']; ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Data</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo date('d/m/Y H:i', strtotime($movement['created_at'])); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Descrição / Cliente</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($movement['description'] ?: '-'); ?></dd>
                </div>
                <?php if ($movement['supplier_name']): ?>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Fornecedor</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($movement['supplier_name']); ?></dd>
                </div>
                <?php endif; ?>

                <?php if ($movement['type'] === 'PURCHASE_CHINA'): ?>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Frete Total (USD)</dt>
                    <dd class="mt-1 text-sm text-gray-900">$ <?php echo number_format($movement['total_freight'], 2); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Custos Extras (USD)</dt>
                    <dd class="mt-1 text-sm text-gray-900">$ <?php echo number_format($movement['total_extra_costs'], 2); ?></dd>
                </div>
                <?php elseif ($movement['type'] === 'TRANSFER_BRAZIL'): ?>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Cotação Dólar</dt>
                    <dd class="mt-1 text-sm text-gray-900">R$ <?php echo number_format($movement['exchange_rate'], 4, ',', '.'); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Custos de Frete (BRL)</dt>
                    <dd class="mt-1 text-sm text-gray-900">R$ <?php echo number_format($movement['total_other_costs'], 2, ',', '.'); ?></dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Custos Extras (BRL)</dt>
                    <dd class="mt-1 text-sm text-gray-900">R$ <?php echo number_format($movement['total_extra_costs'], 2, ',', '.'); ?></dd>
                </div>
                <?php elseif (in_array($movement['type'], ['SALE_USA', 'SALE_BRAZIL'])): ?>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500 uppercase">Cotação Dólar Referência</dt>
                    <dd class="mt-1 text-sm text-gray-900">R$ <?php echo number_format($movement['exchange_rate'], 4, ',', '.'); ?></dd>
                </div>
                <?php endif; ?>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Itens</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                        <?php 
                        if ($movement['type'] === 'TRANSFER_BRAZIL') echo 'Custo Base (USD)';
                        elseif (in_array($movement['type'], ['SALE_USA', 'SALE_BRAZIL'])) echo 'Preço Venda Unit.';
                        else echo 'Custo Unit. (USD)';
                        ?>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase bg-yellow-50" title="Custo final no estoque de destino / Custo do produto vendido">
                        <?php echo in_array($movement['type'], ['SALE_USA', 'SALE_BRAZIL']) ? 'Custo de Venda (CMV)' : 'Custo Unit. Final'; ?>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php 
                $totalBase = 0;
                $totalFinal = 0;
                foreach ($items as $item): 
                    $subtotal = $item['quantity'] * $item['unit_price_or_cost'];
                    $subtotalFinal = $item['quantity'] * $item['final_unit_cost'];
                    $totalBase += $subtotal;
                    $totalFinal += $subtotalFinal;
                    
                    $currency = (in_array($movement['type'], ['TRANSFER_BRAZIL', 'SALE_BRAZIL'])) ? 'R$ ' : '$ ';
                    $currencyBase = ($movement['type'] === 'TRANSFER_BRAZIL') ? '$ ' : $currency;
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                        <?php echo htmlspecialchars($item['product_name'] . ' - ' . $item['concentration']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        <?php echo $item['quantity']; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                        <?php echo $currencyBase . number_format($item['unit_price_or_cost'], 2, $currency === 'R$ ' ? ',' : '.', $currency === 'R$ ' ? '.' : ','); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-medium">
                        <?php echo $currencyBase . number_format($subtotal, 2, $currency === 'R$ ' ? ',' : '.', $currency === 'R$ ' ? '.' : ','); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-yellow-700 bg-yellow-50 font-bold">
                        <?php echo $currency . number_format($item['final_unit_cost'], 2, $currency === 'R$ ' ? ',' : '.', $currency === 'R$ ' ? '.' : ','); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-900 uppercase">Totais:</td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                        <?php 
                        $currencyBase = ($movement['type'] === 'TRANSFER_BRAZIL') ? '$ ' : ((in_array($movement['type'], ['TRANSFER_BRAZIL', 'SALE_BRAZIL'])) ? 'R$ ' : '$ ');
                        echo $currencyBase . number_format($totalBase, 2, $currencyBase === 'R$ ' ? ',' : '.', $currencyBase === 'R$ ' ? '.' : ','); 
                        ?>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-bold text-yellow-700 bg-yellow-50">
                        <?php 
                        $currency = (in_array($movement['type'], ['TRANSFER_BRAZIL', 'SALE_BRAZIL'])) ? 'R$ ' : '$ ';
                        echo $currency . number_format($totalFinal, 2, $currency === 'R$ ' ? ',' : '.', $currency === 'R$ ' ? '.' : ','); 
                        ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
