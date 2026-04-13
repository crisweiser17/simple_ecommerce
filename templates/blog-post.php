<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <a href="/blog" class="text-indigo-600 hover:text-indigo-800 font-medium">
            &larr; <?php echo __('Back to Blog'); ?>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <?php if (!empty($blog_post['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($blog_post['image_url']); ?>" alt="<?php echo htmlspecialchars($blog_post['title']); ?>" class="w-full h-[400px] object-cover">
        <?php endif; ?>
        
        <div class="p-8 md:p-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($blog_post['title']); ?></h1>
            <div class="text-sm text-gray-500 mb-8 border-b pb-4">
                <?php echo date('F j, Y', strtotime($blog_post['created_at'])); ?>
            </div>
            
            <div class="prose max-w-none text-gray-700">
                <?php echo $blog_post['content']; ?>
            </div>
        </div>
    </div>
</div>