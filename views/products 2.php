<div x-data="productsManager()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Produtos</h1>
        <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Novo Produto
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concentração</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo Padrão</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($product['sku'] ?? '-'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($product['concentration']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="font-bold text-gray-900">$ <?php echo number_format($product['default_cost'] ?? 0, 2); ?> /un</div>
                        <?php if (($product['default_batch_quantity'] ?? 1) > 1): ?>
                        <div class="text-xs text-gray-400">
                            Lote: $ <?php echo number_format($product['default_batch_cost'] ?? 0, 2); ?> 
                            (<?php echo $product['default_batch_quantity']; ?> un)
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <button @click='editProduct(<?php echo json_encode($product); ?>)' class="text-blue-600 hover:text-blue-900">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="/products" method="POST">
                    <input type="hidden" name="id" x-model="form.id">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="isEditing ? 'Editar Produto' : 'Novo Produto'"></h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="sku" class="block text-sm font-medium text-gray-700">SKU (Opcional)</label>
                                <input type="text" name="sku" x-model="form.sku" id="sku" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nome da Substância</label>
                                <input type="text" name="name" x-model="form.name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Ex: Retatrutide">
                            </div>
                            <div>
                                <label for="concentration" class="block text-sm font-medium text-gray-700">Concentração</label>
                                <input type="text" name="concentration" x-model="form.concentration" id="concentration" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Ex: 5mg">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="default_batch_cost" class="block text-sm font-medium text-gray-700">Preço do Lote Padrão ($)</label>
                                    <input type="number" step="0.01" name="default_batch_cost" x-model.number="form.default_batch_cost" id="default_batch_cost" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="85.00">
                                </div>
                                <div>
                                    <label for="default_batch_quantity" class="block text-sm font-medium text-gray-700">Unidades por Lote</label>
                                    <input type="number" step="1" name="default_batch_quantity" x-model.number="form.default_batch_quantity" id="default_batch_quantity" min="1" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="10">
                                </div>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-md border border-blue-100">
                                <p class="text-sm text-blue-800">
                                    Custo Unitário Calculado: 
                                    <span class="font-bold text-lg block" x-text="'$ ' + (form.default_batch_quantity > 0 ? (form.default_batch_cost / form.default_batch_quantity) : 0).toFixed(2)"></span>
                                </p>
                                <input type="hidden" name="default_cost" :value="(form.default_batch_quantity > 0 ? (form.default_batch_cost / form.default_batch_quantity) : 0).toFixed(2)">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function productsManager() {
    return {
        showModal: false,
        isEditing: false,
        form: {
            id: '',
            sku: '',
            name: '',
            concentration: '',
            default_batch_cost: 0,
            default_batch_quantity: 1
        },
        openModal() {
            this.isEditing = false;
            this.form = {
                id: '',
                sku: '',
                name: '',
                concentration: '',
                default_batch_cost: 0,
                default_batch_quantity: 1
            };
            this.showModal = true;
        },
        editProduct(product) {
            this.isEditing = true;
            this.form = {
                id: product.id,
                sku: product.sku || '',
                name: product.name,
                concentration: product.concentration,
                default_batch_cost: parseFloat(product.default_batch_cost || 0),
                default_batch_quantity: parseInt(product.default_batch_quantity || 1)
            };
            this.showModal = true;
        }
    }
}
</script>
