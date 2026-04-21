<?php
$showHomeLink = $showHomeLink ?? true;
$showReaderToggle = $showReaderToggle ?? false;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($config['description'] ?? ''); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="asset/css/style.css">
</head>
<body>

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
document.addEventListener('DOMContentLoaded', function() {
const themes = <?php echo json_encode(array_keys($config['themes'])); ?>;
let savedTheme = localStorage.getItem('theme') || 'dark';
<?php if ($showReaderToggle): ?>
let savedReader = localStorage.getItem('readerMode') || 'vertical';

if (window.innerWidth <= 768) {
    savedReader = 'horizontal';
}

document.documentElement.setAttribute('data-reader', savedReader);
document.getElementById('reader-toggle').innerHTML = savedReader === 'vertical' ? '<i class="fas fa-arrow-down"></i>' : '<i class="fas fa-arrow-right"></i>';
<?php endif; ?>

document.documentElement.setAttribute('data-theme', savedTheme);
document.getElementById('theme-toggle').innerHTML = savedTheme === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
});

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
const themes = <?php echo json_encode(array_keys($config['themes'] ?? [])); ?>;
    const next = themes.find(t => t !== current) || 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    document.getElementById('theme-toggle').innerHTML = next === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
}

<?php if ($showReaderToggle): ?>
function toggleReader() {
    if (window.innerWidth <= 768) return;
    const current = localStorage.getItem('readerMode') || 'vertical';
    const next = current === 'vertical' ? 'horizontal' : 'vertical';
    document.documentElement.setAttribute('data-reader', next);
    localStorage.setItem('readerMode', next);
    document.getElementById('reader-toggle').innerHTML = next === 'vertical' ? '<i class="fas fa-arrow-down"></i>' : '<i class="fas fa-arrow-right"></i>';
}
<?php endif; ?>

function toggleLens() {
    const body = document.body;
    const btn = document.getElementById('lens-toggle');
    const isActive = body.classList.contains('lens-active');
    
    if (isActive) {
        body.classList.remove('lens-active');
        btn.classList.remove('active');
    } else {
        body.classList.add('lens-active');
        btn.classList.add('active');
        initLensMode();
    }
}

function initLensMode() {
    const lensCursor = document.getElementById('lens-cursor');
    if (!lensCursor) return;
    
    const lensWidth = 300;
    const lensHeight = 200;
    const lensScale = 0.5;
    
    const readerPages = document.querySelectorAll('.reader-page');
    readerPages.forEach(function(page) {
        const img = page.querySelector('img');
        if (!img) return;
        
        page.addEventListener('mousemove', function(e) {
            const rect = img.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const scaleX = (img.naturalWidth / rect.width) * lensScale;
            const scaleY = (img.naturalHeight / rect.height) * lensScale;
            
            const lensX = (x * scaleX) - (lensWidth / 2);
            const lensY = (y * scaleY) - (lensHeight / 2);
            
            lensCursor.style.left = e.clientX + 'px';
            lensCursor.style.top = e.clientY + 'px';
            lensCursor.style.backgroundImage = 'url(' + img.src + ')';
            lensCursor.style.backgroundPosition = '-' + lensX + 'px -' + lensY + 'px';
            lensCursor.style.backgroundSize = (img.naturalWidth * lensScale) + 'px ' + (img.naturalHeight * lensScale) + 'px';
        });
        
        page.addEventListener('mouseleave', function() {
            lensCursor.style.backgroundImage = 'none';
        });
    });
}
</script>

<nav class="nav px-3">
    <div class="d-flex align-items-center">
        <a href="index.php"><img src="asset/logo.png" alt="Accueil" class="nav-logo"></a>
        <a href="index.php"><span class="nav-title"><?php echo htmlspecialchars($config['title']); ?></span></a>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <?php if (!empty($author)): ?>
        <a href="<?php echo htmlspecialchars($author['url']); ?>" target="_blank" rel="noopener" title="<?php echo htmlspecialchars($author['name']); ?>">
            <img src="<?php echo htmlspecialchars($author['avatar']); ?>" alt="<?php echo htmlspecialchars($author['name']); ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
        </a>
        <?php endif; ?>
        <?php if ($showReaderToggle): ?>
        <button class="btn btn-sm" id="reader-toggle" onclick="toggleReader()" title="Mode lecture"><i class="fa-solid fa-down-to-line"></i></button>
        <?php endif; ?>
        <button class="btn btn-sm" id="lens-toggle" onclick="toggleLens()" title="Mode loupe"><i class="fa-solid fa-magnifying-glass"></i></button>
        <button class="btn btn-sm" id="theme-toggle" onclick="toggleTheme()"><i class="fa-solid fa-moon"></i></button>
    </div>
</nav>

