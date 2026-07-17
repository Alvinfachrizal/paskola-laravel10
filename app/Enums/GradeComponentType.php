<?php

namespace App\Enums;

/** Tipe komponen nilai — digunakan di grade_weights dan student_grades */
enum GradeComponentType: string
{
    case Tugas       = 'tugas';
    case Kuis        = 'kuis';
    case UjianOnline = 'ujian_online';
    case UTS         = 'uts';
    case UAS         = 'uas';
    case Praktik     = 'praktik';

    public function label(): string
    {
        return match($this) {
            self::Tugas       => 'Tugas Harian',
            self::Kuis        => 'Kuis',
            self::UjianOnline => 'Ujian Online',
            self::UTS         => 'UTS (Ujian Tengah Semester)',
            self::UAS         => 'UAS (Ujian Akhir Semester)',
            self::Praktik     => 'Praktik / Praktikum',
        };
    }

    public function labelShort(): string
    {
        return match($this) {
            self::Tugas       => 'Tugas',
            self::Kuis        => 'Kuis',
            self::UjianOnline => 'Ujian Online',
            self::UTS         => 'UTS',
            self::UAS         => 'UAS',
            self::Praktik     => 'Praktik',
        };
    }

    /** Sumber default: mana yang biasanya berasal dari LMS vs manual */
    public function defaultSource(): string
    {
        return match($this) {
            self::Tugas, self::Kuis, self::UjianOnline => 'lms',
            default                                    => 'manual',
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($c) => $c->label(), self::cases())
        );
    }
}
