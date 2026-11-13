<?php
// Script untuk generate icon PWA sederhana menggunakan GD
// Jalankan sekali: php public/icons/generate.php

$sizes = [72, 192, 512];

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);
    $bg  = imagecolorallocate($img, 13, 110, 253);   // Bootstrap primary blue
    $fg  = imagecolorallocate($img, 255, 255, 255);  // White text
    
    imagefill($img, 0, 0, $bg);
    
    // Rounded corner effect (simple)
    $radius = (int)($size * 0.2);
    
    // Text "P" di tengah
    $fontSize = (int)($size * 0.4);
    $fontPath = __DIR__ . '/../../resources/fonts/Arial.ttf';
    
    if (file_exists($fontPath)) {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, 'P');
        $x = ($size - ($bbox[2] - $bbox[0])) / 2;
        $y = ($size - ($bbox[1] - $bbox[7])) / 2 - $bbox[7];
        imagettftext($img, $fontSize, 0, (int)$x, (int)$y, $fg, $fontPath, 'P');
    } else {
        // Fallback tanpa font file
        $scale = max(1, (int)($size / 16));
        imagestring($img, $scale > 5 ? 5 : $scale, (int)($size/2 - 5*$scale), (int)($size/2 - 7*$scale), 'P', $fg);
    }
    
    $filename = $size === 72 ? 'badge-72.png' : "icon-{$size}.png";
    imagepng($img, __DIR__ . '/' . $filename);
    imagedestroy($img);
    echo "Generated: {$filename}\n";
}
echo "Done!\n";

