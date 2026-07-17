<?php

namespace App\Http\Controllers\Ppdb;

use App\Enums\PpdbApplicantStatus;
use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbUniformOrder;
use App\Models\PpdbWave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PpdbPublicController extends Controller
{
    /**
     * Landing page portal PPDB.
     * Menampilkan info gelombang yang sedang aktif.
     */
    public function index()
    {
        $activeWaves = PpdbWave::where('is_active', true)
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        return view('ppdb.index', compact('activeWaves'));
    }

    /**
     * Tampilkan form pendaftaran baru.
     * Hanya tampil jika ada gelombang yang aktif.
     */
    public function showForm()
    {
        $activeWaves = PpdbWave::where('is_active', true)
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        if ($activeWaves->isEmpty()) {
            return redirect()->route('ppdb.index')
                ->with('error', 'Tidak ada gelombang pendaftaran yang sedang aktif saat ini.');
        }

        $docTypes    = PpdbDocument::$docTypes;
        $ukuranList  = PpdbUniformOrder::$ukuranOptions;

        return view('ppdb.register', compact('activeWaves', 'docTypes', 'ukuranList'));
    }

    /**
     * Simpan data pendaftaran ke database.
     * Satu transaksi DB: applicant + dokumen + seragam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wave_id'       => 'required|exists:ppdb_waves,id',
            'full_name'     => 'required|string|max:150',
            'nisn'          => 'nullable|string|size:10',
            'birth_date'    => 'required|date|before:today',
            'birth_place'   => 'required|string|max:100',
            'gender'        => 'required|in:laki-laki,perempuan',
            'address'       => 'required|string|max:500',
            'parent_name'   => 'required|string|max:150',
            'parent_phone'  => 'required|string|max:20',
            'email'         => 'nullable|email|max:150',

            // Dokumen upload (semua wajib)
            'dokumen'                => 'required|array',
            'dokumen.*'              => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',

            // Data seragam
            'ukuran'                 => 'required|in:S,M,L,XL,XXL',
            // Field kerudung & jenis_bawahan hanya wajib jika perempuan
            'pakai_kerudung'         => 'nullable|in:ya,tidak',
            'jenis_bawahan'          => 'nullable|in:rok,celana',
        ]);

        // Pastikan gelombang masih aktif dan belum tutup
        $wave = PpdbWave::findOrFail($validated['wave_id']);
        if (!$wave->is_active || $wave->end_date < now()->toDateString()) {
            return back()->withErrors(['wave_id' => 'Gelombang pendaftaran ini sudah ditutup.']);
        }

        DB::transaction(function () use ($validated, $wave, $request) {

            // ── 1. Buat pendaftar ────────────────────────────────────────────
            $applicant = PpdbApplicant::create([
                'wave_id'           => $validated['wave_id'],
                'registration_code' => PpdbApplicant::generateRegistrationCode(),
                'full_name'         => $validated['full_name'],
                'nisn'              => $validated['nisn'] ?? null,
                'birth_date'        => $validated['birth_date'],
                'birth_place'       => $validated['birth_place'],
                'gender'            => $validated['gender'],
                'address'           => $validated['address'],
                'parent_name'       => $validated['parent_name'],
                'parent_phone'      => $validated['parent_phone'],
                'email'             => $validated['email'] ?? null,
                'status'            => PpdbApplicantStatus::Pending->value,
            ]);

            // ── 2. Simpan dokumen ────────────────────────────────────────────
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $docType => $file) {
                    $path = $file->store("ppdb/dokumen/{$applicant->registration_code}", 'public');

                    PpdbDocument::create([
                        'applicant_id'  => $applicant->id,
                        'doc_type'      => $docType,
                        'file_path'     => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'status'        => 'pending',
                    ]);
                }
            }

            // ── 3. Simpan data seragam ───────────────────────────────────────
            $gender = $validated['gender'];

            // Aturan bisnis: jika laki-laki, set otomatis
            $pakaiKerudung = false;
            $jenisBawahan  = 'celana';

            if ($gender === 'perempuan') {
                $pakaiKerudung = ($validated['pakai_kerudung'] ?? 'tidak') === 'ya';
                $jenisBawahan  = $validated['jenis_bawahan'] ?? 'rok';
            }

            PpdbUniformOrder::create([
                'applicant_id'   => $applicant->id,
                'gender'         => $gender,
                'pakai_kerudung' => $pakaiKerudung,
                'jenis_bawahan'  => $jenisBawahan,
                'ukuran'         => $validated['ukuran'],
            ]);

            // Simpan kode ke session untuk ditampilkan di halaman sukses
            session([
                'ppdb_new_code' => $applicant->registration_code,
                'ppdb_new_name' => $applicant->full_name,
            ]);

            // Buat tagihan jika gelombang berbayar
            if ($wave->hasFee()) {
                $applicant->payments()->create([
                    'payment_type' => 'registration_fee',
                    'amount'       => $wave->registration_fee,
                    'status'       => 'pending',
                ]);
            }
        });

        return redirect()->route('ppdb.register.success');
    }

    /**
     * Halaman sukses setelah pendaftaran berhasil.
     * Tampilkan kode pendaftaran dari session.
     */
    public function success()
    {
        if (!session('ppdb_new_code')) {
            return redirect()->route('ppdb.index');
        }

        $code = session('ppdb_new_code');
        $name = session('ppdb_new_name');

        // Hapus session setelah ditampilkan agar tidak bisa diakses ulang via reload
        session()->forget(['ppdb_new_code', 'ppdb_new_name']);

        return view('ppdb.success', compact('code', 'name'));
    }

    /**
     * Tampilkan form cek status (login ulang: kode + tanggal lahir).
     */
    public function cekStatusForm()
    {
        return view('ppdb.cek-status');
    }

    /**
     * Proses cek status: validasi kode pendaftaran + tanggal lahir.
     * Tanpa OTP — cukup 2 data yang hanya diketahui pendaftar asli.
     */
    public function cekStatus(Request $request)
    {
        $request->validate([
            'registration_code' => 'required|string',
            'birth_date'        => 'required|date',
        ]);

        $applicant = PpdbApplicant::where('registration_code', $request->registration_code)
            ->whereDate('birth_date', $request->birth_date)
            ->first();

        if (!$applicant) {
            return back()->withErrors([
                'registration_code' => 'Kode pendaftaran atau tanggal lahir tidak cocok. Periksa kembali data Anda.',
            ])->withInput();
        }

        // Tandai di session bahwa akses ke halaman status ini sah
        session(["ppdb_auth_{$applicant->registration_code}" => true]);

        return redirect()->route('ppdb.status', $applicant->registration_code);
    }

    /**
     * Halaman detail status pendaftaran.
     * Hanya bisa diakses jika session valid (dari cekStatus di atas).
     */
    public function showStatus(string $registrationCode)
    {
        // Cek session keamanan — mencegah orang menebak URL
        if (!session("ppdb_auth_{$registrationCode}")) {
            return redirect()->route('ppdb.cek-status.form')
                ->with('error', 'Silakan masukkan kode pendaftaran dan tanggal lahir terlebih dahulu.');
        }

        $applicant = PpdbApplicant::where('registration_code', $registrationCode)
            ->with(['wave', 'documents', 'uniformOrder', 'selectionScores', 'payments', 'reregistration'])
            ->firstOrFail();

        return view('ppdb.status', compact('applicant'));
    }

    /**
     * Tampilkan form upload ulang dokumen yang ditolak.
     * Hanya bisa diakses jika status applicant = need_revision.
     */
    public function showReupload(string $registrationCode)
    {
        if (!session("ppdb_auth_{$registrationCode}")) {
            return redirect()->route('ppdb.cek-status.form')
                ->with('error', 'Silakan masukkan kode pendaftaran dan tanggal lahir terlebih dahulu.');
        }

        $applicant = PpdbApplicant::where('registration_code', $registrationCode)
            ->with('documents')
            ->firstOrFail();

        // Hanya pendaftar dengan status need_revision yang bisa upload ulang
        if ($applicant->status->value !== 'need_revision') {
            return redirect()->route('ppdb.status', $registrationCode)
                ->with('error', 'Upload ulang hanya tersedia jika ada dokumen yang perlu diperbaiki.');
        }

        // Ambil hanya dokumen yang statusnya invalid (perlu diupload ulang)
        // Status adalah Enum, tidak bisa compare dengan string biasa
        $invalidDocs = $applicant->documents->filter(
            fn($doc) => $doc->status->value === 'invalid'
        );

        return view('ppdb.reupload', compact('applicant', 'invalidDocs'));
    }

    /**
     * Simpan file upload ulang, reset status dokumen → pending, reset status applicant → pending.
     */
    public function storeReupload(Request $request, string $registrationCode)
    {
        if (!session("ppdb_auth_{$registrationCode}")) {
            return redirect()->route('ppdb.cek-status.form');
        }

        $applicant = PpdbApplicant::where('registration_code', $registrationCode)
            ->with('documents')
            ->firstOrFail();

        if ($applicant->status->value !== 'need_revision') {
            return redirect()->route('ppdb.status', $registrationCode);
        }

        $request->validate([
            'dokumen'   => 'required|array|min:1',
            'dokumen.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::transaction(function () use ($request, $applicant) {
            foreach ($request->file('dokumen') as $docId => $file) {
                // firstWhere pada primary key (id) — tidak ada masalah casting
                $doc = $applicant->documents->first(fn($d) => (string)$d->id === (string)$docId);
                if (!$doc || $doc->status->value !== 'invalid') continue;

                // Hapus file lama jika ada
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }

                // Simpan file baru
                $path = $file->store("ppdb/dokumen/{$applicant->registration_code}", 'public');

                // Reset dokumen → pending, hapus catatan penolakan
                $doc->update([
                    'file_path'       => $path,
                    'original_name'   => $file->getClientOriginalName(),
                    'status'          => 'pending',
                    'rejection_notes' => null,
                    'verified_by'     => null,
                    'verified_at'     => null,
                ]);
            }

            // Reset status applicant → pending agar panitia tahu ada dokumen baru
            $applicant->update([
                'status'      => \App\Enums\PpdbApplicantStatus::Pending->value,
                'admin_notes' => 'Pendaftar telah mengupload ulang dokumen yang ditolak.',
            ]);
        });

        return redirect()->route('ppdb.status', $registrationCode)
            ->with('success', 'Dokumen berhasil diupload ulang. Panitia akan memverifikasi kembali dokumen Anda.');
    }
}
