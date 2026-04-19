<?php

require_once __DIR__ . '/functions.php';

$init = init_mode();
$data = init_data($init['group'], $init['bd']);

$config = $data['config'];
$group = $init['group'];
$bd = $init['bd'];
$pageTitle = $data['pageTitle'] ?? 'BD';

$showReaderToggle = true;
$showHomeLink = true;

require_once __DIR__ . '/template/header.php';
?>

<main>
    <?php if ($bd && $group): ?>
    <div id="reader-loading-overlay" style="display:flex;">
        <div class="spinner"></div>
        <div id="loading-text">Chargement...</div>
    </div>
    <div class="reader" style="visibility:hidden;">
        <?php foreach ($data['pages'] as $page): ?>
            <div class="reader-page">
                <img src="<?php echo $sourceDirectory . '/' . $group . '/' . $bd . '/' . $page; ?>" alt="<?php echo htmlspecialchars($page); ?>">
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($group && $bd): ?>
    sessionStorage.setItem('fromReader', '1');
    localStorage.setItem('lastRead', JSON.stringify({
        group: <?php echo json_encode($group); ?>,
        bd: <?php echo json_encode($bd); ?>
    }));
    <?php endif; ?>

    const images = document.querySelectorAll('.reader-page img');
    const overlay = document.getElementById('reader-loading-overlay');
    const reader = document.querySelector('.reader');
    let loadedCount = 0;
    const totalImages = images.length;

    if (totalImages === 0) {
        overlay.style.display = 'none';
        return;
    }

    images.forEach(function(img) {
        if (img.complete) {
            loadedCount++;
            checkLoaded();
        } else {
            img.addEventListener('load', checkLoaded);
            img.addEventListener('error', checkLoaded);
        }
    });

    function checkLoaded() {
        loadedCount++;
        const percent = Math.round((loadedCount / totalImages) * 100);
        document.getElementById('loading-text').textContent = 'Chargement ' + percent + '%';

        if (loadedCount >= totalImages) {
            reader.style.visibility = 'visible';
            overlay.remove();
        }
    }
});
</script>

<script>
document.addEventListener('keydown', function(e) {
    const readerMode = document.documentElement.getAttribute('data-reader');
    const reader = document.querySelector('.reader');
    if (!reader || readerMode !== 'horizontal') return;
    
    const firstPage = reader.querySelector('.reader-page');
    const scrollAmount = firstPage ? firstPage.offsetWidth : window.innerWidth * 0.3;
    if (e.key === 'ArrowRight') {
        reader.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else if (e.key === 'ArrowLeft') {
        reader.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
});
</script>

<script src="asset/js/main.js"></script>

</body>
</html>