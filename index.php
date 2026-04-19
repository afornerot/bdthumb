<?php

require_once __DIR__ . '/functions.php';

$init = init_mode();
$data = init_data($init['group'], $init['bd']);

$config = $data['config'];
$group = $init['group'];
$bd = $init['bd'];
$pageTitle = $data['pageTitle'] ?? 'BD';

$showReaderToggle = false;
$showHomeLink = true;

require_once __DIR__ . '/template/header.php';
require_once __DIR__ . '/template/loading.php';
?>

<div class="hero-full">
    <div class="hero-header">
        <img class="hero-logo" src="asset/logo.png" alt="<?php echo htmlspecialchars($config['title']); ?>">
        <h1><?php echo htmlspecialchars($config['title']); ?></h1>
        <p class="description"><?php echo htmlspecialchars($config['description']); ?></p>
    </div>
    <?php if (!empty($data['slides'])): ?>
    <div class="carousel" id="carousel">
        <div class="carousel-track" id="carousel-track">
            <?php foreach ($data['slides'] as $slide): ?>
                <div class="carousel-slide">
                    <img src="asset/slide/<?php echo htmlspecialchars($slide); ?>" alt="">
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-nav carousel-prev" onclick="moveSlide(-1)">&#10094;</button>
        <button class="carousel-nav carousel-next" onclick="moveSlide(1)">&#10095;</button>
        <div class="carousel-dots" id="carousel-dots">
            <?php foreach ($data['slides'] as $i => $slide): ?>
                <span class="carousel-dot<?php echo $i === 0 ? ' active' : ''; ?>" onclick="goToSlide(<?php echo $i; ?>)"></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<main>
    <div class="container">
    <?php foreach ($data['allBds'] as $groupName => $bdsList): ?>
        <h2><?php echo htmlspecialchars($groupName); ?></h2>
        <div class="grid mb-3" id="gallery-grid-<?php echo htmlspecialchars($groupName); ?>">
            <?php foreach ($bdsList as $b): ?>
                <?php 
                    $bdPath = $sourceDirectory . '/' . $groupName . '/' . $b;
                    $firstPage = get_first_image($bdPath);
                ?>
                <div class="grid-item">
                    <div class="card">
                        <a href="reader.php?group=<?php echo urlencode($groupName); ?>&bd=<?php echo urlencode($b); ?>">
                            <?php if ($firstPage): ?>
                                <div class="thumb-container">
                                    <img src="<?php echo $thumbDirectory . '/' . $groupName . '/' . $b . '/' . $firstPage; ?>" alt="<?php echo htmlspecialchars($b); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="title"><?php echo htmlspecialchars($b); ?></div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>
</main>

<script src="asset/js/main.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('fromReader')) return;
    sessionStorage.removeItem('fromReader');
    
    const lastRead = localStorage.getItem('lastRead');
    if (!lastRead) return;
    
    try {
        const data = JSON.parse(lastRead);
        if (data.group && data.bd) {
            const card = document.querySelector(`.card a[href*="group=${encodeURIComponent(data.group)}&bd=${encodeURIComponent(data.bd)}"]`);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    } catch (e) {}
});</script>

</body>
</html>