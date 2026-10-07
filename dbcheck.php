<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$exams = \App\Models\Exam::with('teacher')->get();
echo "=== EXAM STATUS ===\n";
foreach ($exams as $exam) {
    echo "Status: {$exam->status->value} | Title: {$exam->title} | Teacher_id: {$exam->teacher_id}\n";
}

$user = \App\Models\User::with('roles')->where('email', 'admin@paskola.com')->first();
echo "\n=== LOGGED-IN USER ===\n";
echo "Email: {$user->email} | Roles: " . $user->roles->pluck('name')->join(', ') . "\n";

$guruUser = \App\Models\User::role('Guru')->first();
echo "\n=== GURU USER ===\n";
echo "Email: {$guruUser->email} | ID: {$guruUser->id}\n";
echo "Exam teacher_id matches: " . ($exams->first()?->teacher_id === $guruUser->id ? 'YES' : 'NO') . "\n";
