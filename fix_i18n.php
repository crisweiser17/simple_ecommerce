<?php
$files = [
    'templates/admin/dashboard.php',
    'templates/admin/product-form.php',
    'templates/cart.php',
    'templates/order-success.php',
    'templates/quote-success.php'
];

$replacements = [
    'Solicitar Orçamento' => '<?php echo __(\'Request Quote\'); ?>',
    'Copiar código PIX' => '<?php echo __(\'Copy PIX code\'); ?>',
    'Instruções de Pagamento' => '<?php echo __(\'Payment Instructions\'); ?>',
    'Resumo da Solicitação' => '<?php echo __(\'Quote Summary\'); ?>',
    'Exibição do logo' => '<?php echo __(\'Logo Display\'); ?>',
    'Modo de Operação da Loja' => '<?php echo __(\'Store Operation Mode\'); ?>',
    '>Português<' => '><?php echo __(\'Portuguese\'); ?><',
    '>Usuário<' => '><?php echo __(\'User\'); ?><',
    'Módulos habilitados' => '<?php echo __(\'Enabled modules\'); ?>',
    'Habilitar módulo Pix manual' => '<?php echo __(\'Enable manual Pix module\'); ?>',
    'Exibir Instruções de Pagamento no Pedido Concluído' => '<?php echo __(\'Display Payment Instructions on Order Success\'); ?>',
    'Texto das Instruções' => '<?php echo __(\'Instructions Text\'); ?>',
    'Deixe em branco para não expirar' => '<?php echo __(\'Leave blank to not expire\'); ?>',
    '\'Tipo de arquivo inválido\'' => '\'<?php echo __("Invalid file type"); ?>\'',
    'title="Produto Físico"' => 'title="<?php echo __(\'Physical Product\'); ?>"',
    'title="Produto Digital"' => 'title="<?php echo __(\'Digital Product\'); ?>"',
    'Apenas listar itens' => '<?php echo __(\'Only list items\'); ?>'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    file_put_contents($file, $content);
}
echo "Done replacing.\n";
