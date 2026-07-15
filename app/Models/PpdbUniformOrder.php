<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbUniformOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'gender',
        'pakai_kerudung',
        'jenis_bawahan',
        'ukuran',
    ];

    protected $casts = [
        'pakai_kerudung' => 'boolean',
    ];

    /**
     * Pilihan ukuran seragam yang tersedia.
     */
    public static array $ukuranOptions = ['S', 'M', 'L', 'XL', 'XXL'];

    public function applicant()
    {
        return $this->belongsTo(PpdbApplicant::class, 'applicant_id');
    }

    /**
     * Deskripsi lengkap seragam untuk rekap panitia.
     * Contoh output: "L - Perempuan, Berkerudung, Rok"
     */
    public function description(): string
    {
        $parts = [$this->ukuran, ucfirst($this->gender)];

        if ($this->gender === 'perempuan') {
            $parts[] = $this->pakai_kerudung ? 'Berkerudung' : 'Tidak Berkerudung';
            $parts[] = ucfirst($this->jenis_bawahan);
        }

        return implode(' - ', $parts);
    }
}
