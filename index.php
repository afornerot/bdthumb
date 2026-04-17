<?php

require_once __DIR__ . '/functions.php';

$init = init_mode();
$data = init_data($init['mode'], $init['group'], $init['bd']);

$config = $data['config'];
$mode = $init['mode'];
$group = $init['group'];
$bd = $init['bd'];
$pageTitle = $data['pageTitle'] ?? 'BD';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($config['description'] ?? ''); ?>">
    <link rel="stylesheet" href="asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body <?php if ($mode === 'home'): ?>class="home-page"<?php endif; ?>>

<style>
:root {
    --thumb-width: <?php echo ($config['display']['thumb_width'] ?? 220); ?>px;
    --thumb-ratio: <?php echo (($config['display']['thumb_ratio'] ?? 1.414) * 100); ?>%;
    --grid-gap: <?php echo ($config['display']['grid_gap'] ?? 20); ?>px;
}
[data-theme="dark"] {
    --bs-primary: <?php echo $config['themes']['dark']['primary_color'] ?? '#e94560'; ?>;
    --bs-secondary: <?php echo $config['themes']['dark']['secondary_color'] ?? '#ff6b6b'; ?>;
    --bs-body-bg: <?php echo $config['themes']['dark']['background_color'] ?? '#1a1a2e'; ?>;
    --bs-body-color: <?php echo $config['themes']['dark']['text_color'] ?? '#eee'; ?>;
    --bs-card-bg: <?php echo $config['themes']['dark']['card_color'] ?? '#16213e'; ?>;
}
[data-theme="light"] {
    --bs-primary: <?php echo $config['themes']['light']['primary_color'] ?? '#dc3545'; ?>;
    --bs-secondary: <?php echo $config['themes']['light']['secondary_color'] ?? '#ff6b6b'; ?>;
    --bs-body-bg: <?php echo $config['themes']['light']['background_color'] ?? '#f8f9fa'; ?>;
    --bs-body-color: <?php echo $config['themes']['light']['text_color'] ?? '#212529'; ?>;
    --bs-card-bg: <?php echo $config['themes']['light']['card_color'] ?? '#ffffff'; ?>;
}
.bg-card { background-color: var(--bs-card-bg); }
.text-primary { color: var(--bs-primary) !important; }
.btn-outline-light { color: var(--bs-body-color); border-color: var(--bs-body-color); }
</style>

<script>
const themes = <?php echo json_encode(array_keys($config['themes'])); ?>;
let savedTheme = localStorage.getItem('theme') || 'dark';
let savedReader = localStorage.getItem('readerMode') || 'vertical';

if (window.innerWidth <= 768) {
    savedReader = 'horizontal';
}

document.documentElement.setAttribute('data-theme', savedTheme);
document.documentElement.setAttribute('data-reader', savedReader);

document.getElementById('theme-toggle').innerHTML = savedTheme === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
document.getElementById('reader-toggle').innerHTML = savedReader === 'vertical' ? '<i class="fas fa-arrow-down"></i>' : '<i class="fas fa-arrow-right"></i>';

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = themes.find(t => t !== current) || 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    document.getElementById('theme-toggle').innerHTML = next === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
}

function toggleReader() {
    if (window.innerWidth <= 768) return;
    const current = localStorage.getItem('readerMode') || 'vertical';
    const next = current === 'vertical' ? 'horizontal' : 'vertical';
    document.documentElement.setAttribute('data-reader', next);
    localStorage.setItem('readerMode', next);
    document.getElementById('reader-toggle').innerHTML = next === 'vertical' ? '<i class="fas fa-arrow-down"></i>' : '<i class="fas fa-arrow-right"></i>';
}
</script>

<div id="loading-overlay">
    <div class="spinner"></div>
    <div id="loading-text">Analyse des fichiers...</div>
</div>

<?php
if (function_exists('apache_setenv')) { @apache_setenv('no-gzip', 1); }
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level() > 0) { ob_end_flush(); }
flush();
?>

<nav class="nav px-3">
    <div class="d-flex align-items-center">
        <a href="?"><img src="asset/logo.png" alt="Accueil" class="nav-logo"></a>
        <a href="?"><span class="nav-title"><?php echo htmlspecialchars($config['title']); ?></span></a>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm" id="reader-toggle" onclick="toggleReader()" title="Mode lecture"><i class="fa-solid fa-down-to-line"></i></button>
        <button class="btn btn-sm" id="theme-toggle" onclick="toggleTheme()"><i class="fa-solid fa-moon"></i></button>
    </div>
</nav>

<main>

<?php if ($mode === 'home'): ?>
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
                    <a href="?mode=bd&group=<?php echo urlencode($groupName); ?>&bd=<?php echo urlencode($b); ?>">
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
<?php endif; ?>

<?php if ($mode === 'group'): ?>
<div class="container">
    <h2><?php echo htmlspecialchars($group); ?></h2>
    <div class="grid" id="gallery-grid">
        <?php foreach ($data['bds'] as $b): ?>
            <?php 
                $bdPath = $sourceDirectory . '/' . $group . '/' . $b;
                $firstPage = get_first_image($bdPath);
            ?>
            <div class="grid-item">
                <div class="card">
                    <a href="?mode=bd&group=<?php echo urlencode($group); ?>&bd=<?php echo urlencode($b); ?>">
                        <?php if ($firstPage): ?>
                            <div class="thumb-container">
                                <img src="<?php echo $thumbDirectory . '/' . $group . '/' . $b . '/' . $firstPage; ?>" alt="<?php echo htmlspecialchars($b); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="title"><?php echo htmlspecialchars($b); ?></div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($mode === 'bd'): ?>
<div class="reader">
    <?php foreach ($data['pages'] as $page): ?>
        <div class="reader-page">
            <a href="<?php echo $sourceDirectory . '/' . $group . '/' . $bd . '/' . $page; ?>" data-fancybox="reader">
                <img src="<?php echo $sourceDirectory . '/' . $group . '/' . $bd . '/' . $page; ?>" alt="<?php echo htmlspecialchars($page); ?>">
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</main>

<script src="asset/js/main.js"></script>

</body>
</html>