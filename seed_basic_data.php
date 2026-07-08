<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Str;

$school = School::first();

if(SchoolYear::count() == 0) {
    SchoolYear::create([
        'school_id' => $school->id,
        'name' => '2023/2024',
        'is_active' => true,
    ]);
}

if(Major::count() == 0) {
    Major::create([
        'school_id' => $school->id,
        'name' => 'MIPA',
        'code' => 'MIPA',
    ]);
}

$major = Major::first();
$schoolYear = SchoolYear::first();

if(SchoolClass::count() == 0) {
    SchoolClass::create([
        'school_id' => $school->id,
        'school_year_id' => $schoolYear->id,
        'major_id' => $major->id,
        'name' => 'X MIPA 1',
        'grade' => '10',
    ]);
}

if(Subject::count() == 0) {
    Subject::create([
        'school_id' => $school->id,
        'name' => 'Biologi',
        'code' => 'BIO',
        'type' => 'Muatan Peminatan',
    ]);
    Subject::create([
        'school_id' => $school->id,
        'name' => 'Matematika',
        'code' => 'MTK',
        'type' => 'Muatan Nasional',
    ]);
}

echo "Data Kelas dan Mapel berhasil digenerate!\n";
