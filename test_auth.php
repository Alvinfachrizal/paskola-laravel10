<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::login(\App\Models\User::first());
try {
    echo view('profile.edit')->render();
} catch (\Exception $e) {
    echo $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
}
