<?php

namespace App\Enums;

/**
 * Kapan nilai ditampilkan ke siswa setelah ujian selesai.
 *
 *   after_submit  → nilai langsung terlihat setelah siswa klik selesai
 *   after_approve → nilai baru terlihat setelah guru menyetujui (approve)
 */
enum ExamShowScore: string
{
    case AfterSubmit  = 'after_submit';
    case AfterApprove = 'after_approve';

    public function label(): string
    {
        return match($this) {
            self::AfterSubmit  => 'Langsung setelah submit',
            self::AfterApprove => 'Setelah disetujui guru',
        };
    }
}
