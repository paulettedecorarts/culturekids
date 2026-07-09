<?php

$root = dirname(__DIR__);
$html = file_get_contents($root.'/mumpe/heritage.html');

if (preg_match('/<style>(.*?)<\/style>/s', $html, $m)) {
    file_put_contents($root.'/resources/css/heritage-client.css', trim($m[1])."\n");
    echo 'Wrote heritage-client.css ('.strlen($m[1])." bytes)\n";
}

// Engine JS starts after auth block; data block ends before CATS
$start = strpos($html, "const TRIBE_IMAGES");
$cats = strpos($html, 'const CATS=', $start);
$engineEnd = strpos($html, '</script>', $cats);

if ($start === false || $cats === false) {
    fwrite(STDERR, "Could not locate engine markers\n");
    exit(1);
}

$engineTail = substr($html, $cats, $engineEnd - $cats);
file_put_contents($root.'/resources/js/heritage-engine-tail.js', trim($engineTail)."\n");
echo 'Wrote heritage-engine-tail.js ('.strlen($engineTail)." bytes)\n";
