<?php

namespace App\Enums;

/**
 * Status pembayaran (record di tabel payments).
 *
 * Alur normal (manual):
 *   pending → (admin verifikasi) → verified
 *   pending → (admin tolak)      → rejected → siswa upload ulang → pending baru
 */
enum PaymentStatus: string
{
    case Pending  = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending  => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending  => 'bg-warning text-dark',
            self::Verified => 'bg-success',
            self::Rejected => 'bg-danger',
        };
    }
}
