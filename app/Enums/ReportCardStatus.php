<?php

namespace App\Enums;

/**
 * Status alur rapor dari draft hingga published.
 *
 * Alur normal:
 *   draft → perlu_verifikasi → terverifikasi → published
 *
 * Alur revisi (wali kelas meragukan nilai):
 *   terverifikasi → perlu_verifikasi (guru revisi) → terverifikasi → published
 */
enum ReportCardStatus: string
{
    case Draft             = 'draft';
    case PerluVerifikasi   = 'perlu_verifikasi';
    case Terverifikasi     = 'terverifikasi';
    case Published         = 'published';

    /** Label bahasa Indonesia untuk ditampilkan di UI */
    public function label(): string
    {
        return match($this) {
            self::Draft           => 'Draft',
            self::PerluVerifikasi => 'Perlu Verifikasi',
            self::Terverifikasi   => 'Terverifikasi',
            self::Published       => 'Published',
        };
    }

    /** CSS class Bootstrap badge */
    public function badgeClass(): string
    {
        return match($this) {
            self::Draft           => 'bg-secondary',
            self::PerluVerifikasi => 'bg-warning text-dark',
            self::Terverifikasi   => 'bg-info text-dark',
            self::Published       => 'bg-success',
        };
    }

    /** Status yang boleh dilihat siswa/ortu */
    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }

    /** Array untuk dropdown/select */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($c) => $c->label(), self::cases())
        );
    }
}
