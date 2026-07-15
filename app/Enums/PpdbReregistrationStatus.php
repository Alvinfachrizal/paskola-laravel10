<?php

namespace App\Enums;

/**
 * Status proses daftar ulang calon siswa yang sudah lolos seleksi.
 *
 * - pending   : Lolos seleksi, belum menyelesaikan daftar ulang
 * - completed : Daftar ulang selesai, sudah masuk ke tabel students
 */
enum PpdbReregistrationStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Belum Selesai',
            self::Completed => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'bg-warning text-dark',
            self::Completed => 'bg-success',
        };
    }
}
