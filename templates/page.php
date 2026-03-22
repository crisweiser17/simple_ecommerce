<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded shadow-sm">
        <h1 class="text-3xl font-bold mb-6"><?php echo htmlspecialchars($page['title']); ?></h1>
        <div class="prose max-w-none text-gray-800">
            <?php echo $page['content']; ?>
        </div>
    </div>
</div>
