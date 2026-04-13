<?php
if (!isset($posts)) $posts = [];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo __('Blog Admin'); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'Store')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar (Reusing same style as dashboard or simple back link) -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="/admin" class="text-gray-500 hover:text-gray-700 mr-4">
                            <i class="fa-solid fa-arrow-left"></i> <?php echo __('Back to Dashboard'); ?>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo __('Blog Posts'); ?></h1>
                    </div>
                    <a href="/admin/blog/form" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow-sm hover:bg-indigo-700 text-sm font-medium">
                        <i class="fa-solid fa-plus mr-1"></i> <?php echo __('Add Post'); ?>
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6 lg:p-8">
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Title'); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Status'); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Date'); ?></th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('Actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($posts)): ?>
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500"><?php echo __('No posts found.'); ?></td></tr>
                                <?php endif; ?>
                                <?php foreach ($posts as $p): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="text-sm text-gray-500">/blog/<?php echo htmlspecialchars($p['slug']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $p['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo htmlspecialchars($p['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($p['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="/blog/<?php echo $p['slug']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3" title="<?php echo __('View'); ?>"><i class="fa-solid fa-eye"></i></a>
                                        <a href="/admin/blog/form?id=<?php echo $p['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3" title="<?php echo __('Edit'); ?>"><i class="fa-solid fa-pen"></i></a>
                                        <a href="/admin/blog/delete?id=<?php echo $p['id']; ?>" onclick="return confirm('<?php echo __('Are you sure?'); ?>')" class="text-red-600 hover:text-red-900" title="<?php echo __('Delete'); ?>"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>