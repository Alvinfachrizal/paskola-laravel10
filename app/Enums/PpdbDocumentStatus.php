<?php

namespace App\Enums;

/**
 * Status verifikasi setiap dokumen yang diupload pendaftar.
 *
 * - pending : Belum diproses panitia
 * - valid   : Dokumen dinyatakan valid / diterima
 * - invalid : Dokumen ditolak, pendaftar harus upload ulang
 */
enum PpdbDocumentStatus: string
{
    case Pending = 'pending';
    case Valid   = 'valid';
    case Invalid = 'invalid';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Valid   => 'Valid',
            self::Invalid => 'Tidak Valid',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending => 'bg-secondary',
            self::Valid   => 'bg-success',
            self::Invalid => 'bg-danger',
        };
    }
}
