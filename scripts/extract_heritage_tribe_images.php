<?php

/**
 * Extract TRIBE_IMAGES base64 JPEGs from mumpe/heritage.html into seed/assets/tribes/.
 *
 * Run: php scripts/extract_heritage_tribe_images.php
 */

declare(strict_types=1);

$base = dirname(__DIR__);
$htmlPath = $base.'/mumpe/heritage.html';
$outDir = $base.'/seed/assets/tribes';

if (! is_file($htmlPath)) {
    fwrite(STDERR, "heritage.html not found at {$htmlPath}\n");
    exit(1);
}

$html = file_get_contents($htmlPath);

if ($html === false) {
    fwrite(STDERR, "Failed to read heritage.html\n");
    exit(1);
}

if (! preg_match('/const TRIBE_IMAGES = \{([\s\S]*?)\n\};/', $html, $block)) {
    fwrite(STDERR, "TRIBE_IMAGES block not found in heritage.html\n");
    exit(1);
}

if (! is_dir($outDir) && ! mkdir($outDir, 0755, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Could not create {$outDir}\n");
    exit(1);
}

$pattern = "/'([a-z]+)':\\s*'data:image\\/(jpeg|png|webp);base64,([^']+)'/";
preg_match_all($pattern, $block[1], $matches, PREG_SET_ORDER);

if ($matches === []) {
    fwrite(STDERR, "No tribe images matched in TRIBE_IMAGES\n");
    exit(1);
}

$written = 0;

foreach ($matches as $match) {
    $tribeId = $match[1];
    $extension = $match[2] === 'jpeg' ? 'jpg' : $match[2];
    $binary = base64_decode($match[3], true);

    if ($binary === false) {
        fwrite(STDERR, "  skip {$tribeId}: invalid base64\n");
        continue;
    }

    $target = $outDir.'/'.$tribeId.'.'.$extension;
    file_put_contents($target, $binary);
    $kb = round(strlen($binary) / 1024, 1);
    echo "  wrote {$tribeId}.{$extension} ({$kb} KB)\n";
    $written++;
}

echo "Extracted {$written} tribe image(s) to seed/assets/tribes/\n";
