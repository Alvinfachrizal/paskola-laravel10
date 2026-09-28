<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\StudentGrade::class    => \App\Policies\StudentGradePolicy::class,
        \App\Models\ReportCard::class      => \App\Policies\ReportCardPolicy::class,
        \App\Models\AcademicEvent::class   => \App\Policies\AcademicEventPolicy::class,
        \App\Models\EventCategory::class   => \App\Policies\EventCategoryPolicy::class,
        // ── Modul Ujian Online ─────────────────────────────────────────────
        \App\Models\Question::class        => \App\Policies\QuestionPolicy::class,
        \App\Models\Exam::class            => \App\Policies\ExamPolicy::class,
        \App\Models\ExamParticipant::class => \App\Policies\ExamParticipantPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
