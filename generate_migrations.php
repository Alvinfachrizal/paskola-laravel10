<?php
$migrations = glob('database/migrations/*.php');

$schemas = [
    'create_schools_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('npsn')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_url')->nullable();
            $table->enum('level', ['SD', 'SMP', 'SMA', 'SMK'])->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
PHP,

    'create_users_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->string('nik')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('role')->default('Siswa');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
PHP,

    'create_audit_logs_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->uuid('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
PHP,

    'create_teachers_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('nip')->unique()->nullable();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('last_education')->nullable();
            $table->string('subject_specialty')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('join_date')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
PHP,

    'create_students_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('nisn')->unique()->nullable();
            $table->string('nis')->nullable();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('religion')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo_url')->nullable();
            $table->integer('entry_year')->nullable();
            $table->string('status')->default('aktif');
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('parent_job')->nullable();
            $table->uuid('parent_user_id')->nullable();
            $table->timestamps();
PHP,

    'create_school_years_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('academic_year');
            $table->enum('semester', ['ganjil', 'genap']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
PHP,

    'create_majors_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('Umum');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
PHP,

    'create_school_classes_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignUuid('major_id')->nullable()->constrained('majors')->nullOnDelete();
            $table->string('name');
            $table->integer('grade');
            $table->foreignUuid('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('room_number')->nullable();
            $table->integer('max_students')->default(36);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
PHP,

    'create_student_classes_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignUuid('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->integer('roll_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
PHP,

    'create_subjects_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('wajib');
            $table->integer('weekly_hours')->default(2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
PHP,

    'create_lms_materials_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('document');
            $table->string('file_url')->nullable();
            $table->timestamps();
PHP,

    'create_lms_assignments_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->integer('max_score')->default(100);
            $table->timestamps();
PHP,

    'create_lms_submissions_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('assignment_id')->constrained('lms_assignments')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_url')->nullable();
            $table->text('text_content')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamps();
PHP,

    'create_announcements_table' => <<<'PHP'
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignUuid('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->json('target_roles')->nullable();
            $table->json('target_class_ids')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
PHP,
];

foreach ($migrations as $file) {
    $content = file_get_contents($file);
    foreach ($schemas as $key => $schema) {
        if (strpos($file, $key) !== false) {
            $tableName = str_replace('create_', '', str_replace('_table', '', $key));
            $content = preg_replace(
                '/Schema::create\([\'"].*?[\'"], function \(Blueprint \$table\) \{.*?\}\);/s', 
                "Schema::create('$tableName', function (Blueprint \$table) {\n$schema\n        });", 
                $content
            );
            file_put_contents($file, $content);
        }
    }
}
echo "Done";
