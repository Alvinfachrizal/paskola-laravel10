<?php
$files = glob('app/Models/*.php');
foreach ($files as $file) {
    if (strpos($file, 'User.php') !== false) continue;
    $content = file_get_contents($file);
    if (strpos($content, 'HasUuids') === false) {
        $content = str_replace(
            'use HasFactory;',
            "use HasFactory, \Illuminate\Database\Eloquent\Concerns\HasUuids;",
            $content
        );
        file_put_contents($file, $content);
    }
}
echo "Done adding HasUuids";
