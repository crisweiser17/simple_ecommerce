<?php
$lang = $_SESSION['lang'] ?? 'en';
$displayTitle = ($lang === 'pt' && !empty($page['title_pt'])) ? $page['title_pt'] : $page['title'];
$displayContent = ($lang === 'pt' && !empty($page['content_pt'])) ? $page['content_pt'] : $page['content'];
?>
<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-8 rounded shadow-sm">
        <h1 class="text-3xl font-bold mb-6"><?php echo htmlspecialchars($displayTitle); ?></h1>
        <div class="prose max-w-none text-gray-800">
            <?php echo $displayContent; ?>
        </div>
    </div>
</div>
