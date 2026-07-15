<?php

namespace App\Enums;

/**
 * Status perjalanan seorang calon siswa (pendaftar) di sistem PPDB.
 *
 * Urutan alur normal:
 *   pending → verified → (payment_pending →) selected / rejected → re_registered
 *
 * - pending          : Baru mendaftar, dokumen belum diverifikasi panitia
 * - verified         : Semua dokumen sudah valid, siap diproses
 * - need_revision    : Ada dokumen yang ditolak, pendaftar harus upload ulang
 * - payment_pending  : Lolos verifikasi dokumen, menunggu bayar biaya pendaftaran (jika ada)
 * - selected         : Dinyatakan LOLOS seleksi oleh panitia
 * - rejected         : Dinyatakan TIDAK LOLOS seleksi
 * - re_registered    : Sudah menyelesaikan daftar ulang, sudah masuk ke tabel students
 */
enum PpdbApplicantStatus: string
{
    case Pending         = 'pending';
    case Verified        = 'verified';
    case NeedRevision    = 'need_revision';
    case PaymentPending  = 'payment_pending';
    case Selected        = 'selected';
    case Rejected        = 'rejected';
    case ReRegistered    = 're_registered';

    /**
     * Label tampilan human-readable untuk di-render di view.
     */
    public function label(): string
    {
        return match($this) {
            self::Pending        => 'Menunggu Verifikasi',
            self::Verified       => 'Dokumen Terverifikasi',
            self::NeedRevision   => 'Perlu Revisi Dokumen',
            self::PaymentPending => 'Menunggu Pembayaran',
            self::Selected       => 'Lolos Seleksi',
            self::Rejected       => 'Tidak Lolos Seleksi',
            self::ReRegistered   => 'Daftar Ulang Selesai',
        };
    }

    /**
     * Warna badge Bootstrap untuk tampilan di tabel/dashboard.
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::Pending        => 'bg-secondary',
            self::Verified       => 'bg-info text-dark',
            self::NeedRevision   => 'bg-warning text-dark',
            self::PaymentPending => 'bg-warning text-dark',
            self::Selected       => 'bg-success',
            self::Rejected       => 'bg-danger',
            self::ReRegistered   => 'bg-primary',
        };
    }

    /**
     * Ambil semua nilai enum sebagai array [value => label] untuk dropdown filter.
     */
    public static function options(): array
    {
        return array_column(
            array_map(fn($e) => ['value' => $e->value, 'label' => $e->label()], self::cases()),
            'label',
            'value'
        );
    }
}
