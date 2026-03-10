<?php
$file = 'public/assets/css/invoice-print.css';
$content = file_get_contents($file);

$content = preg_replace_callback('/font-size:\s*([\d.]+)pt;/', function ($matches) {
    if ($matches[1] == '34') {
        return 'font-size: 36pt;';
    } else if ($matches[1] == '20') {
        return 'font-size: 22pt;';
    } else if ($matches[1] == '15.5') {
        return 'font-size: 17pt;';
    } else if ($matches[1] == '12.6') {
        return 'font-size: 14pt;';
    } else if ($matches[1] == '12') {
        return 'font-size: 13.5pt;';
    }
    $newSize = floatval($matches[1]) + 1.5;
    return 'font-size: ' . rtrim(number_format($newSize, 1), '.0') . 'pt;';
}, $content);

file_put_contents($file, $content);
echo "Updated font sizes.\n";
