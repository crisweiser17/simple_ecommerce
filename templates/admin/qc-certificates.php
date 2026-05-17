<?php
if (!isset($qcCertificates)) $qcCertificates = [];
if (!isset($qcAllProducts)) $qcAllProducts = [];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo __('QC Certificates'); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'Store')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans" x-data="{ formOpen: false, editing: {id:'',code:'',product_name:'',product_id:'',batch_number:'',issued_at:'',pdf_url:'',notes:''} }">

    <div class="flex flex-col min-h-screen">
        <header class="bg-white shadow-sm z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="/admin" class="text-gray-500 hover:text-gray-700 mr-4">
                        <i class="fa-solid fa-arrow-left"></i> <?php echo __('Back to Dashboard'); ?>
                    </a>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900"><?php echo __('QC Certificates'); ?></h1>
                </div>
                <div class="flex items-center gap-2">
                    <form action="/admin/qc-certificates/import" method="POST" class="inline">
                        <button type="submit" class="bg-gray-700 text-white px-3 py-2 text-sm rounded hover:bg-gray-800" title="<?php echo __('Re-scan public/uploads/qc and import any missing certificates'); ?>">
                            <i class="fa-solid fa-folder-open mr-1"></i> <?php echo __('Import from folder'); ?>
                        </button>
                    </form>
                    <button @click="formOpen = true; editing = {id:'',code:'',product_name:'',product_id:'',batch_number:'',issued_at:'',pdf_url:'',notes:''}" class="bg-green-600 text-white px-4 py-2 rounded shadow-sm hover:bg-green-700 text-sm font-medium">
                        <i class="fa-solid fa-plus mr-1"></i> <?php echo __('Add Certificate'); ?>
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6 lg:p-8">

            <?php if (!empty($qcImportReport)): ?>
                <div class="mb-4 rounded border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3 text-sm">
                    <strong><?php echo __('Import complete'); ?>:</strong>
                    <?php echo sprintf(__('%d imported, %d skipped (already in DB or unrecognized filename).'), (int)$qcImportReport['imported'], (int)$qcImportReport['skipped']); ?>
                    <?php if (!empty($qcImportReport['errors'])): ?>
                        <ul class="list-disc pl-5 mt-1 text-xs">
                            <?php foreach ($qcImportReport['errors'] as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($qcSaveResult)): ?>
                <div class="mb-4 rounded border px-4 py-3 text-sm <?php echo !empty($qcSaveResult['success']) ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'; ?>">
                    <?php echo htmlspecialchars($qcSaveResult['message'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 text-sm text-gray-600">
                    <?php echo sprintf(__('%d certificates registered.'), count($qcCertificates)); ?>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Code'); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Product'); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Batch'); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Issued'); ?></th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('PDF'); ?></th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($qcCertificates)): ?>
                                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
                                    <?php echo __('No certificates yet.'); ?>
                                    <a href="/admin/qc-certificates/import" onclick="event.preventDefault(); this.closest('table').parentElement.parentElement.querySelector('form[action=\'/admin/qc-certificates/import\']')?.submit();" class="text-indigo-600 hover:text-indigo-800 underline ml-1">
                                        <?php echo __('Import from folder'); ?>
                                    </a>
                                </td></tr>
                            <?php endif; ?>
                            <?php foreach ($qcCertificates as $cert): ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs font-semibold text-gray-900"><?php echo htmlspecialchars($cert['code']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($cert['product_name'] ?? ''); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($cert['batch_number'] ?? ''); ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo !empty($cert['issued_at']) ? htmlspecialchars(date('Y-m-d', strtotime($cert['issued_at']))) : '—'; ?></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <?php if (!empty($cert['pdf_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($cert['pdf_url']); ?>" target="_blank" class="text-red-600 hover:text-red-800" title="PDF">
                                                <i class="fa-solid fa-file-pdf text-lg"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="/qc-verify?id=<?php echo urlencode($cert['code']); ?>" target="_blank" class="text-green-600 hover:text-green-800 mr-3" title="<?php echo __('View public verify page'); ?>">
                                            <i class="fa-solid fa-up-right-from-square"></i>
                                        </a>
                                        <button type="button"
                                                @click="formOpen = true; editing = <?php echo htmlspecialchars(json_encode([
                                                    'id' => $cert['id'],
                                                    'code' => $cert['code'],
                                                    'product_name' => $cert['product_name'] ?? '',
                                                    'product_id' => $cert['product_id'] ?? '',
                                                    'batch_number' => $cert['batch_number'] ?? '',
                                                    'issued_at' => $cert['issued_at'] ?? '',
                                                    'pdf_url' => $cert['pdf_url'] ?? '',
                                                    'notes' => $cert['notes'] ?? '',
                                                ]), ENT_QUOTES); ?>"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"><?php echo __('Edit'); ?></button>
                                        <a href="/admin/qc-certificates/delete?id=<?php echo (int)$cert['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('<?php echo __('Delete this certificate?'); ?>')"><?php echo __('Delete'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit/Add Modal -->
    <div x-show="formOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="formOpen = false"></div>
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <form action="/admin/qc-certificates/save" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="editing.id ? '<?php echo __('Edit Certificate'); ?>' : '<?php echo __('Add Certificate'); ?>'"></h3>
                        <button type="button" @click="formOpen = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                    </div>

                    <input type="hidden" name="id" :value="editing.id">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Certificate Code'); ?> *</label>
                            <input type="text" name="code" x-model="editing.code" required placeholder="R2QC-2026-12345-678" class="mt-1 block w-full border border-gray-300 rounded p-2 font-mono text-sm uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Product Name'); ?></label>
                            <input type="text" name="product_name" x-model="editing.product_name" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Link to Product (optional)'); ?></label>
                            <select name="product_id" x-model="editing.product_id" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                                <option value="">—</option>
                                <?php foreach ($qcAllProducts as $prod): ?>
                                    <option value="<?php echo (int)$prod['id']; ?>"><?php echo htmlspecialchars($prod['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Batch Number'); ?></label>
                            <input type="text" name="batch_number" x-model="editing.batch_number" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Issue Date'); ?></label>
                            <input type="date" name="issued_at" x-model="editing.issued_at" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('PDF URL'); ?> *</label>
                            <input type="text" name="pdf_url" x-model="editing.pdf_url" placeholder="/uploads/qc/filename.pdf" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm font-mono">
                            <p class="text-xs text-gray-500 mt-1"><?php echo __('Or upload a PDF below — it will replace this URL.'); ?></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Upload PDF (optional)'); ?></label>
                            <input type="file" name="pdf_file" accept="application/pdf" class="mt-1 block w-full text-sm text-gray-700">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700"><?php echo __('Notes'); ?></label>
                            <textarea name="notes" x-model="editing.notes" rows="2" class="mt-1 block w-full border border-gray-300 rounded p-2 text-sm"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-2 justify-end pt-4 border-t border-gray-200">
                        <button type="button" @click="formOpen = false" class="bg-white border border-gray-300 px-4 py-2 text-sm rounded text-gray-700 hover:bg-gray-50"><?php echo __('Cancel'); ?></button>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 text-sm rounded hover:bg-indigo-700"><?php echo __('Save'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
