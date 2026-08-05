<?php

namespace App\Enums;

/**
 * Status tagihan siswa di modul Keuangan Sekolah.
 *
 * Alur normal:
 *   belum_bayar → (siswa upload bukti) → menunggu_verifikasi → (admin verifikasi) → lunas
 *   belum_bayar → (melewati due_date)  → terlambat
 *   menunggu_verifikasi → (admin tolak) → belum_bayar
 */
enum StudentBillStatus: string
{
    case BelumBayar           = 'belum_bayar';
    case MenungguVerifikasi   = 'menunggu_verifikasi';
    case Lunas                = 'lunas';
    case Terlambat            = 'terlambat';

    public function label(): string
    {
        return match($this) {
            self::BelumBayar         => 'Belum Bayar',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Lunas              => 'Lunas',
            self::Terlambat          => 'Terlambat',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::BelumBayar         => 'bg-danger',
            self::MenungguVerifikasi => 'bg-warning text-dark',
            self::Lunas              => 'bg-success',
            self::Terlambat          => 'bg-secondary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BelumBayar         => 'bi-x-circle',
            self::MenungguVerifikasi => 'bi-hourglass-split',
            self::Lunas              => 'bi-check-circle',
            self::Terlambat          => 'bi-exclamation-triangle',
        };
    }
}
