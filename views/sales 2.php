<div x-data="salesForm()">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Nova Venda 💰</h1>

    <form action="/sales" method="POST" class="space-y-6">
        
        <!-- Location Selection -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Origem da Venda</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700">Centro de Custo / Local de Saída</label>
                <select name="location" x-model="location" @change="resetRows()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="USA">Estados Unidos (Matriz) 🇺🇸</option>
                    <option value="BRA">Brasil (Filial) 🇧🇷</option>
                </select>
            </div>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                 <div>
                     <label class="block text-sm font-medium text-gray-700">Descrição / Cliente</label>
                     <input type="text" name="description" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500" placeholder="Ex: Cliente John Doe">
                 </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Cotação Dólar (Referência)</label>
                    <div class="flex gap-2">
                        <input type="number" step="0.0001" name="exchange_rate" x-model.number="rate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <button type="button" @click="fetchRate()" class="mt-1 bg-gray-200 px-3 py-2 rounded border border-gray-300 hover:bg-gray-300 text-sm">
                            <span x-show="!loadingRate">Atualizar</span>
                            <span x-show="loadingRate">...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Itens Vendidos</h3>
                <button type="button" @click="addRow()" class="text-green-600 hover:text-green-800 font-medium">+ Adicionar Item</button>
            </div>
            
            <div class="space-y-4">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="flex gap-4 items-end border-b pb-4">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500">Produto</label>
                            <select :name="'items['+index+'][product_id]'" x-model="row.product_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Selecione...</option>
                                <template x-for="p in getAvailableProducts()" :key="p.id">
                                    <option :value="p.id" x-text="p.name + ' ' + p.concentration + ' (Estoque: ' + p.quantity + ')'"></option>
                                </template>
                                <template x-if="getAvailableProducts().length === 0">
                                    <option value="" disabled>Nenhum produto em estoque neste local</option>
                                </template>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-500">Qtd</label>
                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="row.quantity" required min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="w-40">
                            <label class="block text-xs font-medium text-gray-500">Preço Venda Unit. ($/R$)</label>
                            <input type="number" step="0.01" :name="'items['+index+'][unit_price]'" x-model.number="row.unit_price" required min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
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
            <h4 class="font-medium text-gray-900 mb-2">Resumo da Venda</h4>
            <p class="text-lg font-bold text-green-800 mt-2">Total Venda: <span x-text="(location == 'USA' ? '$' : 'R$') + totalSaleValue().toFixed(2)"></span></p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 shadow-lg font-medium">
                Registrar Venda
            </button>
        </div>
    </form>
</div>

<script>
function salesForm() {
    return {
        location: 'USA',
        rate: 1.0,
        loadingRate: false,
        inventory: <?php echo json_encode($inventory ?? []); ?>,
        rows: [
            { product_id: '', quantity: 1, unit_price: 0 }
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
        getAvailableProducts() {
            return this.inventory.filter(i => i.location === this.location);
        },
        resetRows() {
            this.rows = [{ product_id: '', quantity: 1, unit_price: 0 }];
        },
        addRow() {
            this.rows.push({ product_id: '', quantity: 1, unit_price: 0 });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },
        totalSaleValue() {
            return this.rows.reduce((acc, row) => {
                return acc + (row.quantity * row.unit_price);
            }, 0);
        }
    }
}
</script>
