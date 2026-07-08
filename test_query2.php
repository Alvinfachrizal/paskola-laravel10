<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schoolId = 'e0a133f9-192a-4467-883a-7a554ccf6ba2';
try {
    $count = App\Models\User::role(['Admin', 'Super Admin'])->where('school_id', $schoolId)->count();
    echo "Count: " . $count . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
