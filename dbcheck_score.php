<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$exam = App\Models\Exam::where('title', 'UTS Matematika Kelas X')->first();
$p = App\Models\ExamParticipant::where('exam_id', $exam->id)->first();
echo "Status: {$p->status->value}\n";
echo "Score: {$p->score}\n";
echo "Grade Status: {$p->grade_status->value}\n";
