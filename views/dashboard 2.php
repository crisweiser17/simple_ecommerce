<!-- Main Wrapper for Backup Modal State -->
<div x-data="{ backupModalOpen: false, backupTab: 'export' }">

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Ações Rápidas</h3>
        </div>
        <div class="p-6 flex flex-wrap gap-4">
            <a href="/purchase" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition shadow-sm">
                <span>🚢</span> Nova Compra
            </a>
            <a href="/transfer" class="flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition shadow-sm">
                <span>✈️</span> Transferir
            </a>
            <a href="/sales" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition shadow-sm">
                <span>💰</span> Nova Venda
            </a>
            <a href="/products" class="flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition shadow-sm">
                <span>📦</span> Produtos
            </a>
            <!-- Backup Button triggers Modal -->
            <button @click="backupModalOpen = true" class="flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900 transition ml-auto shadow-sm">
                <span>💾</span> Backup
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Atividade Recente (Geral)</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Itens</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($recentMovements as $mov): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        #<?php echo $mov['id']; ?>
                    </td>
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                    <?php 
                    if (in_array($mov['type'], ['TRANSFER_BRAZIL', 'SALE_BRAZIL'])) {
                        echo 'R$ ' . number_format($mov['total_value'] ?? 0, 2, ',', '.');
                    } else {
                        echo '$ ' . number_format($mov['total_value'] ?? 0, 2);
                    }
                    ?>
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
                <?php if (empty($recentMovements)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 text-sm">Nenhuma atividade recente.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 bg-gray-50 text-center">
            <a href="/movements" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium">
                Ver todas as atividades
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>

    <div class="mb-6">
        <form method="GET" action="/" class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap gap-4 items-end border border-gray-200">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data Início</label>
                <input type="date" name="start_date" value="<?php echo $startDate; ?>" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Data Fim</label>
                <input type="date" name="end_date" value="<?php echo $endDate; ?>" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded text-sm font-bold hover:bg-blue-700 transition shadow-sm">
                FILTRAR
            </button>
        </form>
    </div>

    <!-- Inventory Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                <span>🇺🇸</span> Estoque USA (Matriz)
            </h2>
            <div class="text-3xl font-bold text-blue-600 mb-2">
                $ <?php echo number_format($totalUSA, 2); ?>
            </div>
            <p class="text-gray-500 text-sm">Custo Total em Inventário (Landed Cost)</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500" x-data="{ rate: 0, totalBRL: <?php echo $totalBRA; ?>, loading: true }" x-init="
            fetch('https://economia.awesomeapi.com.br/last/USD-BRL')
                .then(res => res.json())
                .then(data => { 
                    rate = parseFloat(data.USDBRL.bid); 
                    loading = false; 
                })
                .catch(() => loading = false)
        ">
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                <span>🇧🇷</span> Estoque Brasil (Filial)
            </h2>
            <div class="flex items-baseline gap-2 mb-2">
                <div class="text-3xl font-bold text-green-600">
                    R$ <?php echo number_format($totalBRA, 2, ',', '.'); ?>
                </div>
                <div class="text-lg text-gray-500 font-medium" x-show="!loading && rate > 0">
                    (<span x-text="'$ ' + (totalBRL / rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>)
                </div>
            </div>
            <p class="text-gray-500 text-sm">
                Custo Total em Inventário (Reais)
                <span x-show="!loading && rate > 0" class="block mt-1 text-xs text-green-700 bg-green-50 inline-block px-2 py-1 rounded">
                    Dólar Hoje: R$ <span x-text="rate.toFixed(4)"></span>
                </span>
            </p>
        </div>
    </div>

    <!-- Financial Stats (New) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-600">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Faturamento (Período)</h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">$ <?php echo number_format($revenue, 2); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-emerald-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Lucro Bruto</h3>
            <p class="text-2xl font-bold text-emerald-600 mt-2">$ <?php echo number_format($profit, 2); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-indigo-500">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Margem de Lucro</h3>
            <p class="text-2xl font-bold text-indigo-600 mt-2"><?php echo number_format($margin, 1); ?>%</p>
        </div>
    </div>

    <!-- Chart & Sales Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>📈</span> Evolução de Vendas
            </h3>
            <div class="relative h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Sales List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-800 text-sm uppercase">Vendas do Período</h3>
            </div>
            <div class="overflow-y-auto max-h-[300px] flex-1">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Lucro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($salesList as $sale): 
                            $saleProfit = $sale['total_amount'] - $sale['total_cost'];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600"><?php echo date('d/m', strtotime($sale['created_at'])); ?></td>
                            <td class="px-4 py-3 text-right font-medium">$<?php echo number_format($sale['total_amount'], 2); ?></td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-bold">$<?php echo number_format($saleProfit, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($salesList)): ?>
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">Nenhuma venda no período selecionado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Backup Modal -->
    <div x-show="backupModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50" @click="backupModalOpen = false"></div>
        
        <!-- Modal -->
        <div class="relative bg-white rounded-lg max-w-lg mx-auto mt-20 p-6 shadow-xl">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Gerenciamento de Backup</h2>
            
            <!-- Tabs -->
            <div class="flex border-b mb-6">
                <button @click="backupTab = 'export'" :class="{'border-b-2 border-blue-600 text-blue-600': backupTab === 'export'}" class="flex-1 py-2 text-center font-medium text-gray-600 hover:text-blue-600 transition">Exportar</button>
                <button @click="backupTab = 'import'" :class="{'border-b-2 border-blue-600 text-blue-600': backupTab === 'import'}" class="flex-1 py-2 text-center font-medium text-gray-600 hover:text-blue-600 transition">Importar</button>
            </div>

            <!-- Export Content -->
            <div x-show="backupTab === 'export'">
                <form action="/backup/export" method="POST">
                    <div class="mb-4">
                        <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="export_type" value="sqlite" class="form-radio text-blue-600 mt-1" checked onclick="document.getElementById('partialOptions').style.display='none'">
                            <div>
                                <span class="font-bold block text-gray-800">Backup Completo (.sqlite)</span>
                                <span class="text-xs text-gray-500">Arquivo original do banco de dados. Cópia exata de todo o sistema.</span>
                            </div>
                        </label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="export_type" value="json" class="form-radio text-blue-600 mt-1" onclick="document.getElementById('partialOptions').style.display='block'">
                            <div>
                                <span class="font-bold block text-gray-800">Exportação Seletiva (.json)</span>
                                <span class="text-xs text-gray-500">Escolha quais dados exportar. Ideal para migrações parciais.</span>
                            </div>
                        </label>
                    </div>

                    <div id="partialOptions" style="display: none;" class="ml-8 mb-6 space-y-2 border-l-2 border-gray-200 pl-4">
                        <p class="text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Selecionar Itens:</p>
                        <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="selection[]" value="products" checked class="rounded text-blue-600"> <span class="text-sm">Produtos</span></label>
                        <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="selection[]" value="suppliers" checked class="rounded text-blue-600"> <span class="text-sm">Fornecedores</span></label>
                        <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="selection[]" value="purchases" checked class="rounded text-blue-600"> <span class="text-sm">Compras (China)</span></label>
                        <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="selection[]" value="transfers" checked class="rounded text-blue-600"> <span class="text-sm">Transferências (Brasil)</span></label>
                        <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="selection[]" value="sales" checked class="rounded text-blue-600"> <span class="text-sm">Vendas</span></label>
                        <label class="flex items-center space-x-2 cursor-pointer"><input type="checkbox" name="selection[]" value="inventory" checked class="rounded text-blue-600"> <span class="text-sm">Estoque Atual (Snapshot)</span></label>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md transition flex items-center gap-2">
                            <span>⬇️</span> Baixar Arquivo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Import Content -->
            <div x-show="backupTab === 'import'">
                <form action="/backup/import" method="POST" enctype="multipart/form-data">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-6 hover:bg-gray-50 transition">
                        <p class="mb-2 text-gray-600 font-medium">Selecione o arquivo de backup</p>
                        <p class="text-xs text-gray-400 mb-4">Suporta .sqlite, .db ou .json</p>
                        <input type="file" name="backup_file" accept=".sqlite,.db,.json" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="text-yellow-400 text-xl">⚠️</span>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Como funciona a importação:</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Arquivos <strong>.sqlite</strong> substituem TODO o banco de dados atual.</li>
                                        <li>Arquivos <strong>.json</strong> atualizam ou inserem dados novos (mantém IDs existentes).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-green-700 shadow-md transition flex items-center gap-2" onclick="return confirm('Tem certeza? A importação pode sobrescrever dados existentes.')">
                            <span>⬆️</span> Restaurar / Importar
                        </button>
                    </div>
                </form>
            </div>
            
            <button @click="backupModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
        </div>
    </div>

</div>
<!-- End Main Wrapper -->

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const chartData = <?php echo json_encode($chartData); ?>;
        
        // Prepare Data
        const labels = chartData.map(d => {
            const parts = d.sale_date.split('-');
            return parts[2] + '/' + parts[1]; // DD/MM
        });
        const revenueData = chartData.map(d => parseFloat(d.daily_revenue));
        const profitData = chartData.map(d => parseFloat(d.daily_revenue) - parseFloat(d.daily_cost));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Faturamento ($)',
                        data: revenueData,
                        backgroundColor: 'rgba(59, 130, 246, 0.6)', // Blue-500
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 2
                    },
                    {
                        label: 'Lucro ($)',
                        data: profitData,
                        type: 'line',
                        borderColor: 'rgb(16, 185, 129)', // Emerald-500
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>