<div x-data="editMovementForm()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar Custos de Movimento</h1>
        <a href="/" class="text-gray-600 hover:text-gray-900">Voltar</a>
    </div>

    <form action="/movement/edit" method="POST" class="space-y-6">
        <input type="hidden" name="id" value="<?php echo $movement['id']; ?>">
        <input type="hidden" name="type" value="<?php echo $movement['type']; ?>">

        <!-- Info Header -->
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="block text-xs text-gray-500 uppercase">Tipo</span>
                    <span class="font-bold text-gray-800"><?php echo $movement['type']; ?></span>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase mb-1">Data Original</label>
                    <input type="datetime-local" name="created_at" value="<?php echo date('Y-m-d\TH:i', strtotime($movement['created_at'])); ?>" class="w-full text-sm font-bold text-gray-800 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 uppercase mb-1">Descrição</label>
                    <input type="text" name="description" value="<?php echo htmlspecialchars($movement['description']); ?>" class="w-full text-sm font-bold text-gray-800 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Costs Editing -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Atualização de Custos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <?php if ($movement['type'] === 'PURCHASE_CHINA'): ?>
                <!-- Purchase Fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Frete Total (USD)</label>
                    <input type="number" step="0.01" name="total_freight" x-model.number="totalFreight" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Custos Extras / Impostos (USD)</label>
                    <input type="number" step="0.01" name="total_extra_costs" x-model.number="totalExtraCosts" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Valores adicionais pós-chegada (Ex: Taxas alfandegárias).</p>
                </div>

                <?php elseif ($movement['type'] === 'TRANSFER_BRAZIL'): ?>
                <!-- Transfer Fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cotação Dólar (USD &rarr; BRL)</label>
                    <input type="number" step="0.0001" name="exchange_rate" x-model.number="exchangeRate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Custos de Frete/Despacho (BRL)</label>
                    <input type="number" step="0.01" name="total_other_costs" x-model.number="totalOtherCosts" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Custos Extras / Impostos Brasil (BRL)</label>
                    <input type="number" step="0.01" name="total_extra_costs" x-model.number="totalExtraCosts" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Valores adicionais pós-chegada no Brasil.</p>
                </div>

                <?php elseif (in_array($movement['type'], ['SALE_USA', 'SALE_BRAZIL'])): ?>
                <!-- Sale Fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cotação Dólar (Referência)</label>
                    <input type="number" step="0.0001" name="exchange_rate" x-model.number="exchangeRate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Items Edit -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-medium text-gray-900">Itens da Movimentação</h4>
                <button type="button" @click="addItem()" class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                    + Adicionar Item
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qtd</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                <?php 
                                if ($movement['type'] === 'TRANSFER_BRAZIL') {
                                    echo 'Custo Base (USD)';
                                } elseif (in_array($movement['type'], ['SALE_USA', 'SALE_BRAZIL'])) {
                                    echo 'Preço Venda Unit. (' . ($movement['type'] === 'SALE_USA' ? 'USD' : 'BRL') . ')';
                                } else {
                                    echo 'Custo Unit. (USD)';
                                }
                                ?>
                            </th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-2">
                                    <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" class="block w-full text-sm border-gray-300 rounded-md">
                                        <option value="">Selecione...</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['name'] . ' - ' . $p['concentration']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" class="block w-full text-sm border-gray-300 rounded-md">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" :name="'items[' + index + '][unit_price_or_cost]'" x-model="item.unit_price_or_cost" class="block w-full text-sm border-gray-300 rounded-md">
                                </td>
                                <td class="px-4 py-2 text-right font-medium text-gray-700">
                                    <span x-text="'$ ' + (item.quantity * item.unit_price_or_cost).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 text-sm font-bold">X</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100">
                            <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-700">Total Produtos:</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-700">
                                <span x-text="'$ ' + totalProducts().toFixed(2)"></span>
                            </td>
                            <td></td>
                        </tr>
                        <!-- Grand Total Row -->
                        <tr class="bg-gray-200 border-t-2 border-gray-300">
                            <td colspan="3" class="px-4 py-3 text-right font-extrabold text-gray-800 uppercase">
                                Total Geral <span x-text="(type === 'TRANSFER_BRAZIL' || type === 'SALE_BRAZIL') ? '(BRL)' : '(USD)'"></span>:
                            </td>
                            <td class="px-4 py-3 text-right font-extrabold text-green-700 text-lg">
                                <span x-text="((type === 'TRANSFER_BRAZIL' || type === 'SALE_BRAZIL') ? 'R$ ' : '$ ') + grandTotal().toFixed(2)"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="mt-4 text-sm text-yellow-600">
                ⚠️ Ao salvar, o sistema recalculará o custo unitário final destes itens e atualizará o <strong>Custo Médio</strong> do estoque atual proporcionalmente.
            </p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 shadow-lg font-medium">
                Recalcular e Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
function editMovementForm() {
    return {
        // Form Data
        totalFreight: <?php echo floatval($movement['total_freight'] ?? 0); ?>,
        totalOtherCosts: <?php echo floatval($movement['total_other_costs'] ?? 0); ?>,
        totalExtraCosts: <?php echo floatval($movement['total_extra_costs'] ?? 0); ?>,
        exchangeRate: <?php echo floatval($movement['exchange_rate'] ?? 1); ?>,
        type: '<?php echo $movement['type']; ?>',

        items: <?php 
            $jsItems = array_map(function($i) {
                return [
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'unit_price_or_cost' => $i['unit_price_or_cost']
                ];
            }, $items);
            echo json_encode($jsItems); 
        ?>,
        addItem() {
            this.items.push({
                product_id: '',
                quantity: 1,
                unit_price_or_cost: 0
            });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        totalProducts() {
            return this.items.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price_or_cost || 0));
            }, 0);
        },
        grandTotal() {
            let productTotal = this.totalProducts();
            
            if (this.type === 'PURCHASE_CHINA') {
                // USD Total
                return productTotal + parseFloat(this.totalFreight || 0) + parseFloat(this.totalExtraCosts || 0);
            } else if (this.type === 'TRANSFER_BRAZIL') {
                // Estimated BRL Total
                // Product Base (USD) -> BRL
                let rate = parseFloat(this.exchangeRate || 1);
                let productTotalBRL = productTotal * rate;
                
                return productTotalBRL + parseFloat(this.totalOtherCosts || 0) + parseFloat(this.totalExtraCosts || 0);
            } else if (this.type === 'SALE_USA' || this.type === 'SALE_BRAZIL') {
                return productTotal;
            }
            return 0;
        }
    }
}
</script>
