<?php

namespace App\Enums;

/**
 * Status sebuah ujian yang dibuat guru.
 *
 * Alur normal:
 *   draft → (guru publish) → published → (guru atau sistem tutup) → closed
 */
enum ExamStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Closed    = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Published => 'Dipublikasi',
            self::Closed    => 'Ditutup',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft     => 'bg-secondary',
            self::Published => 'bg-success',
            self::Closed    => 'bg-dark',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Draft     => 'bi-pencil-square',
            self::Published => 'bi-broadcast',
            self::Closed    => 'bi-lock',
        };
    }
}
