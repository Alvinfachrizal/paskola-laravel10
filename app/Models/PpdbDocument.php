<?php

namespace App\Models;

use App\Enums\PpdbDocumentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpdbDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'doc_type',
        'file_path',
        'original_name',
        'status',
        'rejection_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'status'      => PpdbDocumentStatus::class,
        'verified_at' => 'datetime',
    ];

    /**
     * Daftar tipe dokumen yang wajib diupload.
     * Key = value di DB, Value = label tampilan.
     */
    public static array $docTypes = [
        'kk'              => 'Kartu Keluarga',
        'akta_lahir'      => 'Akta Kelahiran',
        'foto'            => 'Pas Foto 3x4',
        'ijazah'          => 'Ijazah / SKL',
        'skhun'           => 'SKHUN',
        'raport'          => 'Raport Semester Terakhir',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────

    public function applicant()
    {
        return $this->belongsTo(PpdbApplicant::class, 'applicant_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ─── Helper ────────────────────────────────────────────────────────────

    /** Label tipe dokumen yang readable */
    public function docTypeLabel(): string
    {
        return self::$docTypes[$this->doc_type] ?? ucfirst(str_replace('_', ' ', $this->doc_type));
    }
}
