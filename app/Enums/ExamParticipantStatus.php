<?php

namespace App\Enums;

/**
 * Status seorang peserta (siswa) dalam sebuah ujian.
 *
 * Alur normal:
 *   belum_mulai → (siswa masukkan kode & mulai) → mengerjakan
 *             → (siswa klik selesai) → selesai
 *             → (waktu habis tanpa submit) → waktu_habis
 */
enum ExamParticipantStatus: string
{
    case BelumMulai  = 'belum_mulai';
    case Mengerjakan = 'mengerjakan';
    case Selesai     = 'selesai';
    case WaktuHabis  = 'waktu_habis';

    public function label(): string
    {
        return match($this) {
            self::BelumMulai  => 'Belum Mulai',
            self::Mengerjakan => 'Sedang Mengerjakan',
            self::Selesai     => 'Selesai',
            self::WaktuHabis  => 'Waktu Habis',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::BelumMulai  => 'bg-secondary',
            self::Mengerjakan => 'bg-warning text-dark',
            self::Selesai     => 'bg-success',
            self::WaktuHabis  => 'bg-danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BelumMulai  => 'bi-clock',
            self::Mengerjakan => 'bi-pencil',
            self::Selesai     => 'bi-check-circle',
            self::WaktuHabis  => 'bi-alarm',
        };
    }

    /**
     * Apakah peserta sudah menyelesaikan ujian (baik submit maupun waktu habis)?
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::Selesai, self::WaktuHabis]);
    }

    /**
     * Apakah peserta masih bisa aktif mengerjakan?
     */
    public function isActive(): bool
    {
        return in_array($this, [self::BelumMulai, self::Mengerjakan]);
    }
}
