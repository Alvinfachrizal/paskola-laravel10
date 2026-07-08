<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo view('admin.lms.materials.index', [
        'materials' => app(\App\Models\LmsMaterial::class)->paginate(),
        'classes' => collect([]),
        'subjects' => collect([])
    ])->render();
} catch (\Exception $e) {
    echo $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
}
