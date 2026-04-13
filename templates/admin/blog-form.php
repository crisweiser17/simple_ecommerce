<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title><?php echo (isset($post['id']) ? __('Edit Post') : __('Add New Post')); ?> - <?php echo htmlspecialchars(getSetting('store_name', 'Store')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    
    <div class="flex h-screen overflow-hidden">
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="/admin/blog" class="text-gray-500 hover:text-gray-700 mr-4">
                            <i class="fa-solid fa-arrow-left"></i> <?php echo __('Back to List'); ?>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo (isset($post['id']) ? __('Edit Post') : __('Add New Post')); ?></h1>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6 lg:p-8">
                <form action="/admin/blog/save" method="POST" id="blogForm" enctype="multipart/form-data">
                    <?php if (isset($post['id'])): ?>
                        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white p-6 shadow rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Post Title'); ?></label>
                                <input type="text" name="title" id="blog_title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required class="block w-full border border-gray-300 rounded-md shadow-sm p-3 text-lg font-bold focus:ring-indigo-500 focus:border-indigo-500">
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Slug'); ?></label>
                                    <div class="flex rounded-md shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">/blog/</span>
                                        <input type="text" name="slug" id="blog_slug" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>" required class="flex-1 block w-full border border-gray-300 rounded-none rounded-r-md shadow-sm p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 shadow rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo __('Content'); ?></label>
                                <input type="hidden" name="content" id="content_input">
                                <div id="blog_editor" class="bg-white" style="height: 500px;">
                                    <?php echo $post['content'] ?? ''; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar / Settings -->
                        <div class="space-y-6">
                            <div class="bg-white p-6 shadow rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Publish'); ?></h3>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Status'); ?></label>
                                    <select name="status" class="block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="published" <?php echo (isset($post['status']) && $post['status'] === 'published') ? 'selected' : ''; ?>><?php echo __('Published'); ?></option>
                                        <option value="draft" <?php echo (isset($post['status']) && $post['status'] === 'draft') ? 'selected' : ''; ?>><?php echo __('Draft'); ?></option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 font-medium">
                                    <i class="fa-solid fa-save mr-1"></i> <?php echo __('Save Post'); ?>
                                </button>
                            </div>

                            <div class="bg-white p-6 shadow rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2"><?php echo __('Cover Image'); ?></h3>
                                <?php if (!empty($post['image_url'])): ?>
                                    <div class="mb-4">
                                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="w-full h-40 object-cover rounded-lg border border-gray-200">
                                        <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($post['image_url']); ?>">
                                    </div>
                                <?php endif; ?>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('Upload Image'); ?></label>
                                <input type="file" name="image_file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo __('OR Image URL'); ?></label>
                                    <input type="text" name="image_url_manual" value="<?php echo htmlspecialchars($post['image_url'] ?? ''); ?>" placeholder="https://..." class="block w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quill = new Quill('#blog_editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ]
                }
            });

            var form = document.getElementById('blogForm');
            var titleInput = document.getElementById('blog_title');
            var slugInput = document.getElementById('blog_slug');
            var slugTouched = slugInput.value.trim() !== '';

            var slugify = function(value) {
                return String(value || '')
                    .toLowerCase()
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            };

            titleInput.addEventListener('input', function() {
                if (!slugTouched) {
                    slugInput.value = slugify(titleInput.value);
                }
            });

            slugInput.addEventListener('input', function() {
                slugTouched = true;
            });

            form.addEventListener('submit', function() {
                document.getElementById('content_input').value = quill.root.innerHTML;
            });
        });
    </script>
</body>
</html>