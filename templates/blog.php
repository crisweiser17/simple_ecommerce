<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <h1 class="text-4xl font-bold text-gray-900 mb-8"><?php echo __('Blog'); ?></h1>

    <?php if (empty($posts)): ?>
        <p class="text-gray-500"><?php echo __('No posts found.'); ?></p>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach ($posts as $post): ?>
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-transform hover:scale-[1.02]">
                    <?php if (!empty($post['image_url'])): ?>
                        <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>">
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-48 object-cover">
                        </a>
                    <?php else: ?>
                        <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="w-full h-48 bg-gray-100 flex items-center justify-center text-gray-400">
                            <i class="fa-solid fa-image text-4xl"></i>
                        </a>
                    <?php endif; ?>
                    <div class="p-6">
                        <div class="text-sm text-gray-500 mb-2">
                            <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-3">
                            <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="hover:text-indigo-600">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>
                        <div class="text-gray-600 line-clamp-3">
                            <?php echo strip_tags($post['content']); ?>
                        </div>
                        <div class="mt-4">
                            <a href="/blog/<?php echo htmlspecialchars($post['slug']); ?>" class="text-indigo-600 font-medium hover:text-indigo-800">
                                <?php echo __('Read more'); ?> &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>