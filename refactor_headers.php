<?php

$files = glob('resources/views/admin/*/*.blade.php');
foreach ($files as $file) {
    $content = file_get_contents($file);

    // Pattern for typical back/add button
    $pattern = '/@section\(\'header\'\)\s*<div class="d-flex justify-content-between align-items-center">\s*<h2[^>]*>(.*?)<\/h2>\s*<a href="(.*?)" class="(.*?)">(.*?)<\/a>\s*<\/div>\s*@endsection/s';
    
    if (preg_match($pattern, $content, $matches)) {
        $title = trim($matches[1]);
        $url = trim($matches[2]);
        $btnClass = trim($matches[3]);
        $btnText = trim($matches[4]);
        
        // Clean up the text if it contains HTML (like icons)
        $btnTextClean = strip_tags($btnText);
        
        // Determine icon based on text
        $icon = '';
        if (stripos($btnTextClean, 'Tambah') !== false) {
            $icon = '<i class="bi bi-plus-lg"></i> ';
            // If it's add button, we might want it to be primary
            $btnClass = 'btn btn-primary';
        } else if (stripos($btnTextClean, 'Kembali') !== false) {
            $icon = '<i class="bi bi-arrow-left"></i> ';
            $btnClass = 'btn btn-light text-secondary border';
        }

        $newHeader = "@section('header')\n" .
                     "    <h2 class=\"h4 mb-0 text-dark fw-bold\">$title</h2>\n" .
                     "@endsection\n\n" .
                     "@section('header_action')\n" .
                     "    <a href=\"$url\" class=\"$btnClass d-flex align-items-center gap-2\">\n" .
                     "        $icon" . trim($btnText) . "\n" .
                     "    </a>\n" .
                     "@endsection";
                     
        $newContent = preg_replace($pattern, $newHeader, $content);
        file_put_contents($file, $newContent);
        echo "Updated: $file\n";
    }
}
echo "Done.\n";
