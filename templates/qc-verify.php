<?php
$storeName = htmlspecialchars(getSetting('store_name', 'R2 Research Labs'));
$verifiedCertificate = !empty($qcCertificate);
$displayCode = htmlspecialchars($qcCode);
?>

<div class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-5xl">

        <?php if ($verifiedCertificate): ?>
            <!-- VERIFIED Banner -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="flex-shrink-0 w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold uppercase tracking-wide flex items-center gap-2">
                            <?php echo __('Verified'); ?>
                            <span class="inline-block bg-white text-green-700 text-xs font-bold px-2 py-1 rounded uppercase">✓ Authentic</span>
                        </h1>
                        <p class="text-sm text-green-100 mt-1">
                            <?php echo sprintf(__('This is an authentic Certificate of Analysis issued by %s.'), $storeName); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Certificate Metadata -->
            <div class="bg-white rounded-lg shadow border border-gray-200 mb-6 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-shield text-green-600"></i>
                        <?php echo __('Certificate Details'); ?>
                    </h2>
                </div>
                <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Certificate Code'); ?></span>
                        <span class="block font-mono font-semibold text-gray-900 mt-0.5"><?php echo htmlspecialchars($qcCertificate['code']); ?></span>
                    </div>
                    <?php if (!empty($qcCertificate['issued_at'])): ?>
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Issue Date'); ?></span>
                        <span class="block text-gray-900 mt-0.5"><?php echo htmlspecialchars(date('F j, Y', strtotime($qcCertificate['issued_at']))); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($qcCertificate['product_name'])): ?>
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Product'); ?></span>
                        <span class="block text-gray-900 mt-0.5"><?php echo htmlspecialchars($qcCertificate['product_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($qcCertificate['batch_number'])): ?>
                    <div>
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Batch Number'); ?></span>
                        <span class="block font-mono text-gray-900 mt-0.5"><?php echo htmlspecialchars($qcCertificate['batch_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($qcCertificate['linked_product'])): ?>
                    <div class="sm:col-span-2">
                        <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Linked Product Page'); ?></span>
                        <a href="<?php echo htmlspecialchars(getProductUrl($qcCertificate['linked_product'])); ?>" class="text-green-700 hover:text-green-900 font-medium inline-flex items-center gap-1 mt-0.5">
                            <?php echo htmlspecialchars($qcCertificate['linked_product']['name']); ?>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex flex-wrap gap-3 justify-end">
                    <a href="<?php echo htmlspecialchars($qcCertificate['pdf_url']); ?>" download class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 text-sm rounded hover:bg-gray-100 font-medium">
                        <i class="fa-solid fa-download"></i>
                        <?php echo __('Download PDF'); ?>
                    </a>
                    <a href="<?php echo htmlspecialchars($qcCertificate['pdf_url']); ?>" target="_blank" class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 text-sm rounded hover:bg-green-700 font-medium">
                        <i class="fa-solid fa-up-right-from-square"></i>
                        <?php echo __('Open in New Tab'); ?>
                    </a>
                </div>
            </div>

            <!-- PDF Viewer with VERIFIED Overlay -->
            <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                <div class="relative" style="height: 75vh; min-height: 600px;">
                    <iframe
                        src="<?php echo htmlspecialchars($qcCertificate['pdf_url']); ?>#toolbar=1&navpanes=0"
                        class="w-full h-full border-0"
                        title="<?php echo __('Certificate of Analysis'); ?>">
                    </iframe>

                    <!-- VERIFIED Watermark overlay (corner stamp, doesn't block reading) -->
                    <div class="absolute top-4 right-4 pointer-events-none z-10 select-none">
                        <div class="bg-green-600 text-white text-xs font-bold uppercase tracking-wider px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 opacity-90 border-2 border-white">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span><?php echo __('Verified by'); ?> <?php echo $storeName; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500 text-center mt-4">
                <?php echo __('This verification page confirms the certificate code is registered in our system. The document above is the original PDF.'); ?>
            </p>

        <?php else: ?>
            <!-- NOT FOUND -->
            <div class="bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="flex-shrink-0 w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold uppercase tracking-wide"><?php echo __('Not Verified'); ?></h1>
                        <p class="text-sm text-red-100 mt-1">
                            <?php echo $qcCode !== ''
                                ? __('We could not find a certificate with the code provided. The code may be incorrect, expired, or this document may be counterfeit.')
                                : __('No certificate code was provided.'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <p class="text-sm text-gray-700 mb-4"><?php echo __('Try checking the code for typos. The expected format is:'); ?></p>
                <div class="bg-gray-100 rounded px-4 py-3 font-mono text-sm text-gray-800 mb-4">R2QC-YYYY-NNNNN-NNN</div>
                <?php if ($qcCode !== ''): ?>
                    <p class="text-sm text-gray-600"><?php echo __('Code provided:'); ?> <span class="font-mono font-semibold text-red-700"><?php echo $displayCode; ?></span></p>
                <?php endif; ?>

                <form action="/qc-verify" method="GET" class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Verify another code:'); ?></label>
                    <div class="flex gap-2">
                        <input type="text" name="id" placeholder="R2QC-2026-12345-678" required class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 font-mono">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 text-sm font-medium rounded hover:bg-green-700"><?php echo __('Verify'); ?></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>
