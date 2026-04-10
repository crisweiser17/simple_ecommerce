<div x-data="transferForm()">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Nova Transferência (USA 🇺🇸 &rarr; Brasil 🇧🇷)</h1>

    <?php if (empty($inventory)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p>Não há produtos em estoque nos EUA para transferir.</p>
            <a href="/purchase" class="font-bold underline">Realizar Compra na China</a>
        </div>
    <?php else: ?>

    <form action="/transfer" method="POST" class="space-y-6">
        
        <!-- General Info -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Custos de Envio</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição do Envio</label>
                    <input type="text" name="description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Remessa Aérea Fev">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cotação Dólar (USD &rarr; BRL)</label>
                    <div class="flex gap-2">
                        <input type="number" step="0.0001" name="exchange_rate" x-model.number="rate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" @click="fetchRate()" class="mt-1 bg-gray-200 px-3 py-2 rounded border border-gray-300 hover:bg-gray-300 text-sm">
                            <span x-show="!loadingRate">Atualizar</span>
                            <span x-show="loadingRate">...</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Valor de saída em USD será convertido para BRL.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Custos Totais (Frete + Taxas) (R$)</label>
                    <input type="number" step="0.01" name="total_costs" x-model.number="costs" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Informe o valor em REAIS.</p>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Itens para Envio</h3>
                <button type="button" @click="addRow()" class="text-purple-600 hover:text-purple-800 font-medium">+ Adicionar Item</button>
            </div>
            
            <div class="space-y-4">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="flex gap-4 items-end border-b pb-4">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500">Produto (Estoque USA)</label>
                            <select :name="'items['+index+'][product_id]'" x-model="row.product_id" @change="updateCost(index)" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                <?php foreach ($inventory as $item): ?>
                                <option value="<?php echo $item['id']; ?>" data-cost="<?php echo $item['avg_unit_cost']; ?>" data-max="<?php echo $item['quantity']; ?>">
                                    <?php echo htmlspecialchars($item['name'] . ' ' . $item['concentration'] . ' (Disp: ' . $item['quantity'] . ')'); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500">Qtd</label>
                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="row.quantity" required min="1" :max="row.max_quantity" @input="if(row.max_quantity && row.quantity > row.max_quantity) row.quantity = row.max_quantity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-400 mt-1" x-show="row.max_quantity">Max: <span x-text="row.max_quantity"></span></p>
                        </div>
                        <div class="w-40">
                            <label class="block text-xs font-medium text-gray-500">Custo Base USA ($)</label>
                            <input type="text" :value="'$' + row.base_cost.toFixed(2)" disabled class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm text-gray-500">
                            <input type="hidden" :name="'items['+index+'][base_cost]'" :value="row.base_cost">
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
            <h4 class="font-medium text-gray-900 mb-2">Resumo da Transferência</h4>
            <p class="text-sm text-gray-600">Valor Base (USA): <span class="font-bold text-gray-900" x-text="'$' + totalBaseValueUSD().toFixed(2)"></span></p>
            <p class="text-sm text-gray-600">Valor Convertido (BRL): <span class="font-bold text-gray-900" x-text="'R$' + totalBaseValueBRL().toFixed(2)"></span></p>
            <p class="text-sm text-gray-600">Frete/Taxas (BRL): <span class="font-bold text-gray-900" x-text="'R$' + (costs || 0).toFixed(2)"></span></p>
            <p class="text-lg font-bold text-purple-800 mt-2">Valor Total Entrada Brasil: <span x-text="'R$' + (totalBaseValueBRL() + (costs || 0)).toFixed(2)"></span></p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 shadow-lg font-medium">
                Confirmar Transferência
            </button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
function transferForm() {
    return {
        costs: 0,
        rate: 1.0,
        loadingRate: false,
        rows: [
            { product_id: '', quantity: 1, base_cost: 0, max_quantity: null }
        ],
        async fetchRate() {
            this.loadingRate = true;
            try {
                const response = await fetch('https://economia.awesomeapi.com.br/last/USD-BRL');
                const data = await response.json();
                if (data.USDBRL && data.USDBRL.bid) {
                    this.rate = parseFloat(data.USDBRL.bid);
                }
            } catch (e) {
                alert('Erro ao buscar cotação. Insira manualmente.');
            } finally {
                this.loadingRate = false;
            }
        },
        addRow() {
            this.rows.push({ product_id: '', quantity: 1, base_cost: 0, max_quantity: null });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },
        updateCost(index) {
            let select = document.getElementsByName('items['+index+'][product_id]')[0];
            if (select && select.selectedOptions.length > 0) {
                if (select.value === "") {
                    this.rows[index].base_cost = 0;
                    this.rows[index].max_quantity = null;
                    return;
                }
                let option = select.selectedOptions[0];
                let cost = parseFloat(option.dataset.cost || 0);
                let max = parseInt(option.dataset.max || 0);
                this.rows[index].base_cost = cost;
                this.rows[index].max_quantity = max;
                if (this.rows[index].quantity > max) {
                    this.rows[index].quantity = max;
                }
            }
        },
        totalBaseValueUSD() {
            return this.rows.reduce((acc, row) => {
                return acc + (row.quantity * row.base_cost);
            }, 0);
        },
        totalBaseValueBRL() {
            return this.totalBaseValueUSD() * this.rate;
        }
    }
}
</script>
