<?php

namespace App\Enums;

/**
 * Status approval nilai oleh guru.
 *
 * Alur:
 *   menunggu_approve → (guru approve) → approved
 *
 * Catatan: approve TIDAK menulis ke student_grades / rapor.
 */
enum ExamGradeStatus: string
{
    case MenungguApprove = 'menunggu_approve';
    case Approved        = 'approved';

    public function label(): string
    {
        return match($this) {
            self::MenungguApprove => 'Menunggu Approval',
            self::Approved        => 'Disetujui',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::MenungguApprove => 'bg-warning text-dark',
            self::Approved        => 'bg-success',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::MenungguApprove => 'bi-hourglass-split',
            self::Approved        => 'bi-patch-check',
        };
    }
}
