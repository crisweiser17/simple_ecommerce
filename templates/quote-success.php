<div class="container mx-auto px-4 py-16 max-w-2xl text-center" x-data x-init="$store.cart.clear()">
    <div class="mb-8 text-green-500">
        <svg class="w-24 h-24 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    
    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4"><?php echo __('Orçamento Enviado!'); ?></h1>
    <p class="text-lg text-gray-600 mb-8">
        <?php echo __('Recebemos sua lista. Em breve entraremos em contato com você pelo e-mail ou WhatsApp informados.'); ?>
    </p>

    <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left border border-gray-200">
        <h2 class="font-bold text-gray-900 mb-4 text-center"><?php echo __('<?php echo __('Quote Summary'); ?>'); ?></h2>
        <div class="space-y-2 text-sm text-gray-600">
            <p><span class="font-medium text-gray-800"><?php echo __('Número:'); ?></span> #<?php echo str_pad((string)$order['id'], 5, '0', STR_PAD_LEFT); ?></p>
            <p><span class="font-medium text-gray-800"><?php echo __('Data:'); ?></span> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-center gap-4">
        <a href="/download-pdf?id=<?php echo $order['id']; ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded shadow-lg transition-colors inline-flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <?php echo __('Baixar PDF da Lista'); ?>
        </a>
        <a href="/" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-8 rounded shadow-lg transition-colors">
            <?php echo __('Voltar para o site'); ?>
        </a>
    </div>
</div>
