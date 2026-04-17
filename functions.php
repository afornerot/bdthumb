<?php

$sourceDirectory = 'galery';
$thumbDirectory = 'thumb';
$coverDirectory = 'cover';

function get_config(): array
{
    $configFile = file_get_contents(__DIR__ . '/asset/config.json');
    return json_decode($configFile, true) ?? [];
}

function get_theme(string $themeName = 'dark'): array
{
    $config = get_config();
    return $config['themes'][$themeName] ?? $config['themes']['dark'] ?? [];
}

function init_mode(): array
{
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'home';
    $group = isset($_GET['group']) ? $_GET['group'] : '';
    $bd = isset($_GET['bd']) ? $_GET['bd'] : '';
    
    $group = str_replace(['..', '//'], '', $group);
    $group = trim($group, '/');
    $bd = str_replace(['..', '//'], '', $bd);
    $bd = trim($bd, '/');
    
    return [
        'mode' => $mode,
        'group' => $group,
        'bd' => $bd
    ];
}

function init_data(string $mode, string $group = '', string $bd = ''): array
{
    global $coverDirectory, $sourceDirectory, $thumbDirectory;
    $config = get_config();
    $thumbWidth = $config['display']['thumb_width'] ?? 200;
    
    $result = ['config' => $config];
    
    if ($mode === 'home') {
        if (!is_dir($coverDirectory)) {
            mkdir($coverDirectory, 0755, true);
        }
        
        createThumbnails($sourceDirectory, $thumbDirectory, $thumbWidth);
        
        $result['allBds'] = get_all_bds();
        $result['slides'] = get_slides();
        $result['pageTitle'] = 'BD';
    } elseif ($mode === 'group') {
        if (!is_dir($coverDirectory)) {
            mkdir($coverDirectory, 0755, true);
        }
        
        createThumbnails($sourceDirectory, $thumbDirectory, $thumbWidth);
        
        $result['bds'] = get_bds($group);
        $result['pageTitle'] = htmlspecialchars($group);
    } elseif ($mode === 'bd') {
        $result['pages'] = get_pages($group, $bd);
        $result['pageTitle'] = htmlspecialchars($bd);
    }
    
    return $result;
}

function createThumbnails(string $sourceDir, string $thumbDir, int $thumbWidth): void
{
    $dir = opendir($sourceDir);
    if (!$dir) {
        return;
    }

    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            $sourcePath = $sourceDir . '/' . $file;
            $thumbPath = $thumbDir . '/' . $file;

            if (is_dir($sourcePath)) {
                createThumbnails($sourcePath, $thumbPath, $thumbWidth);
            } else {
                $pathinfo = pathinfo($sourcePath);
                $extension = strtolower($pathinfo['extension']);

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    make_thumb($sourcePath, $thumbPath, $thumbWidth);
                }
            }
        }
    }
    closedir($dir);
}

function make_thumb(string $src, string $dest, int $desired_width): void
{
    if (file_exists($dest)) {
        return;
    }
    
    echo '<script>var lt = document.getElementById("loading-text"); if(lt) lt.textContent = "Génération : ' . addslashes(basename($src)) . '";</script>';
    flush();

    $extension = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'jpeg':
        case 'jpg':
            $source_image = imagecreatefromjpeg($src);
            break;
        case 'png':
            $source_image = imagecreatefrompng($src);
            break;
        case 'gif':
            $source_image = imagecreatefromgif($src);
            break;
        default:
            return;
    }

    if (!$source_image) {
        return;
    }

    $width = imagesx($source_image);
    $height = imagesy($source_image);

    $desired_height = floor($height * ($desired_width / $width));

    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    switch ($extension) {
        case 'jpeg':
        case 'jpg':
            imagejpeg($virtual_image, $dest);
            break;
        case 'png':
            imagepng($virtual_image, $dest);
            break;
        case 'gif':
            imagegif($virtual_image, $dest);
            break;
    }

    imagedestroy($source_image);
    imagedestroy($virtual_image);
}

function get_first_image(string $dir): ?string
{
    if (!is_dir($dir)) {
        return null;
    }
    
    $items = scandir($dir);
    $images = [];
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $images[] = $item;
            }
        }
    }
    
    natcasesort($images);
    
    return $images[0] ?? null;
}

function get_bds(string $group): array
{
    global $sourceDirectory;
    
    $groupPath = $sourceDirectory . '/' . $group;
    if (!is_dir($groupPath)) {
        return [];
    }
    
    $bds = [];
    $items = scandir($groupPath);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $groupPath . '/' . $item;
        if (is_dir($path)) {
            $bds[] = $item;
        }
    }
    
    natcasesort($bds);
    
    return $bds;
}

function get_all_bds(): array
{
    global $sourceDirectory;
    
    if (!is_dir($sourceDirectory)) {
        return [];
    }
    
    $groups = [];
    $items = scandir($sourceDirectory);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $sourceDirectory . '/' . $item;
        if (is_dir($path)) {
            $groups[] = $item;
        }
    }
    
    natcasesort($groups);
    $groups = array_reverse($groups);
    
    $result = [];
    foreach ($groups as $group) {
        $bds = get_bds($group);
        $result[$group] = $bds;
    }
    
    return $result;
}

function get_pages(string $group, string $bd): array
{
    global $sourceDirectory;
    
    $bdPath = $sourceDirectory . '/' . $group . '/' . $bd;
    if (!is_dir($bdPath)) {
        return [];
    }
    
    $pages = [];
    $items = scandir($bdPath);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $bdPath . '/' . $item;
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $pages[] = $item;
            }
        }
    }
    
    natcasesort($pages);
    
    return $pages;
}

function get_slides(): array
{
    $slideDir = __DIR__ . '/asset/slide';
    if (!is_dir($slideDir)) {
        return [];
    }
    
    $slides = [];
    $items = scandir($slideDir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $slideDir . '/' . $item;
        if (is_file($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $slides[] = $item;
            }
        }
    }
    
    natcasesort($slides);
    return $slides;
}