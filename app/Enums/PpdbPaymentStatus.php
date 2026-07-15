<?php

namespace App\Enums;

/**
 * Status pembayaran di modul PPDB.
 *
 * - pending  : Tagihan dibuat, belum ada bukti bayar
 * - paid     : Sudah bayar dan dikonfirmasi panitia
 * - failed   : Pembayaran gagal / ditolak
 * - expired  : Batas waktu bayar sudah lewat
 */
enum PpdbPaymentStatus: string
{
    case Pending = 'pending';
    case Paid    = 'paid';
    case Failed  = 'failed';
    case Expired = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid    => 'Lunas',
            self::Failed  => 'Gagal',
            self::Expired => 'Kadaluarsa',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending => 'bg-warning text-dark',
            self::Paid    => 'bg-success',
            self::Failed  => 'bg-danger',
            self::Expired => 'bg-secondary',
        };
    }
}
