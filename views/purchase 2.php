<div x-data="purchaseForm()">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Nova Compra (China 🇨🇳 &rarr; USA 🇺🇸)</h1>

    <form action="/purchase" method="POST" class="space-y-6">
        
        <!-- General Info -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Custos do Lote</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fornecedor (Obrigatório)</label>
                    <select name="supplier_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione um fornecedor...</option>
                        <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição do Lote</label>
                    <input type="text" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Pedido Janeiro 2024">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Frete Total ($)</label>
                    <input type="number" step="0.01" name="total_freight" x-model.number="freight" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Custos Extras ($)</label>
                    <input type="number" step="0.01" name="total_extra_costs" x-model.number="extraCosts" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Itens</h3>
                <button type="button" @click="addRow()" class="text-blue-600 hover:text-blue-800 font-medium">+ Adicionar Item</button>
            </div>
            
            <div class="space-y-4">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="flex gap-4 items-end border-b pb-4">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500">Produto</label>
                            <select :name="'items['+index+'][product_id]'" x-model="row.product_id" @change="updateCost(index)" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?php echo $p['id']; ?>" data-cost="<?php echo $p['default_cost'] ?? 0; ?>">
                                    <?php echo htmlspecialchars($p['name'] . ' ' . $p['concentration'] . ($p['sku'] ? " [{$p['sku']}]" : '')); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500">Qtd</label>
                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="row.quantity" required min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="w-40">
                            <label class="block text-xs font-medium text-gray-500">Custo Unit. China ($)</label>
                            <input type="number" step="0.01" :name="'items['+index+'][unit_cost]'" x-model.number="row.unit_cost" required min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="w-10 pb-2">
                            <button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700">
                                &times;
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h4 class="font-medium text-gray-900 mb-2">Resumo da Simulação</h4>
            <p class="text-sm text-gray-600">Valor Total Produtos: <span class="font-bold text-gray-900" x-text="'$' + totalProductValue().toFixed(2)"></span></p>
            <p class="text-sm text-gray-600">Frete: <span class="font-bold text-gray-900" x-text="'$' + (freight || 0).toFixed(2)"></span></p>
            <p class="text-sm text-gray-600">Extras: <span class="font-bold text-gray-900" x-text="'$' + (extraCosts || 0).toFixed(2)"></span></p>
            <p class="text-lg font-bold text-blue-800 mt-2">Custo Total: <span x-text="'$' + (totalProductValue() + (freight || 0) + (extraCosts || 0)).toFixed(2)"></span></p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 shadow-lg font-medium">
                Confirmar Entrada em Estoque USA
            </button>
        </div>
    </form>
</div>

<script>
function purchaseForm() {
    return {
        freight: 0,
        extraCosts: 0,
        rows: [
            { product_id: '', quantity: 1, unit_cost: 0 }
        ],
        addRow() {
            this.rows.push({ product_id: '', quantity: 1, unit_cost: 0 });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },
        updateCost(index) {
            let select = document.getElementsByName('items['+index+'][product_id]')[0];
            if (select && select.selectedOptions.length > 0) {
                let cost = parseFloat(select.selectedOptions[0].dataset.cost || 0);
                // Only update if current value is 0 or user hasn't typed (implied by logic)
                // Actually, let's always update when product changes, as it's a new selection
                this.rows[index].unit_cost = cost;
            }
        },
        totalProductValue() {
            return this.rows.reduce((acc, row) => {
                return acc + (row.quantity * row.unit_cost);
            }, 0);
        }
    }
}
</script>
