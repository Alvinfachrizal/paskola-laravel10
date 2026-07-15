<?php

namespace App\Http\Controllers\Ppdb;

use App\Enums\PpdbApplicantStatus;
use App\Enums\PpdbDocumentStatus;
use App\Enums\PpdbReregistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\PpdbApplicant;
use App\Models\PpdbDocument;
use App\Models\PpdbReregistration;
use App\Models\PpdbSelectionScore;
use App\Models\PpdbUniformOrder;
use App\Models\PpdbWave;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpdbAdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // 6.3.c — Dashboard Panitia: Daftar Pendaftar + Filter
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = PpdbApplicant::with(['wave', 'documents'])
            ->latest();

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan gelombang
        if ($request->filled('wave_id')) {
            $query->where('wave_id', $request->wave_id);
        }

        // Filter pencarian nama / kode
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('registration_code', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        $applicants = $query->paginate(20)->withQueryString();

        // Statistik ringkasan untuk header
        $stats = [
            'total'       => PpdbApplicant::count(),
            'pending'     => PpdbApplicant::where('status', 'pending')->count(),
            'verified'    => PpdbApplicant::where('status', 'verified')->count(),
            'selected'    => PpdbApplicant::where('status', 'selected')->count(),
            'rejected'    => PpdbApplicant::where('status', 'rejected')->count(),
            'reregistered'=> PpdbApplicant::where('status', 're_registered')->count(),
        ];

        $waves    = PpdbWave::orderByDesc('created_at')->get();
        $statuses = PpdbApplicantStatus::options();

        return view('admin.ppdb.index', compact('applicants', 'stats', 'waves', 'statuses'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.3.c — Detail Pendaftar + Verifikasi Dokumen
    // ─────────────────────────────────────────────────────────────────────────

    public function show(PpdbApplicant $applicant)
    {
        $applicant->load([
            'wave',
            'documents.verifiedBy',
            'uniformOrder',
            'selectionScores.inputtedBy',
            'payments',
            'reregistration.student',
        ]);

        $scoreTypes = PpdbSelectionScore::$scoreTypes;

        return view('admin.ppdb.show', compact('applicant', 'scoreTypes'));
    }

    /**
     * Verifikasi satu dokumen (approve / reject) oleh panitia.
     * Route: POST /admin/ppdb/pendaftar/{applicant}/dokumen/{document}/verifikasi
     */
    public function verifyDocument(Request $request, PpdbApplicant $applicant, PpdbDocument $document)
    {
        $validated = $request->validate([
            'action'          => 'required|in:valid,invalid',
            'rejection_notes' => 'required_if:action,invalid|nullable|string|max:500',
        ]);

        $document->update([
            'status'          => $validated['action'],
            'rejection_notes' => $validated['action'] === 'invalid' ? $validated['rejection_notes'] : null,
            'verified_by'     => auth()->id(),
            'verified_at'     => now(),
        ]);

        // Setelah verifikasi, cek apakah semua dokumen sudah diproses
        // Jika ada yang invalid → ubah status pendaftar menjadi need_revision
        // Jika semua valid → ubah status menjadi verified
        $this->recalculateApplicantStatus($applicant);

        return back()->with('success', "Dokumen \"{$document->docTypeLabel()}\" berhasil diperbarui.");
    }

    /**
     * Hitung ulang status pendaftar berdasarkan kondisi dokumennya.
     * Dipanggil setiap kali ada perubahan status dokumen.
     */
    private function recalculateApplicantStatus(PpdbApplicant $applicant): void
    {
        // Hanya proses jika status masih di tahap verifikasi
        if (!in_array($applicant->status->value, ['pending', 'verified', 'need_revision'])) {
            return;
        }

        $documents = $applicant->documents;

        if ($documents->isEmpty()) return;

        if ($documents->where('status', 'invalid')->count() > 0) {
            $applicant->update(['status' => PpdbApplicantStatus::NeedRevision->value]);
        } elseif ($documents->where('status', 'pending')->count() === 0) {
            // Semua dokumen sudah diproses dan tidak ada yang invalid
            $wave = $applicant->wave;
            if ($wave && $wave->hasFee()) {
                $applicant->update(['status' => PpdbApplicantStatus::PaymentPending->value]);
            } else {
                $applicant->update(['status' => PpdbApplicantStatus::Verified->value]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.3.d — Seleksi: Input Nilai + Override Status
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Simpan / update nilai seleksi pendaftar.
     */
    public function storeScore(Request $request, PpdbApplicant $applicant)
    {
        $validated = $request->validate([
            'scores'             => 'required|array',
            'scores.*.score_type'  => 'required|string',
            'scores.*.score_value' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($validated['scores'] as $score) {
            PpdbSelectionScore::updateOrCreate(
                [
                    'applicant_id' => $applicant->id,
                    'score_type'   => $score['score_type'],
                ],
                [
                    'score_value' => $score['score_value'],
                    'inputted_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', 'Nilai seleksi berhasil disimpan.');
    }

    /**
     * Override status pendaftar secara manual oleh panitia.
     * Digunakan untuk menentukan kelulusan seleksi.
     */
    public function updateStatus(Request $request, PpdbApplicant $applicant)
    {
        $validated = $request->validate([
            'status'      => 'required|in:pending,verified,need_revision,payment_pending,selected,rejected,re_registered',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $applicant->update([
            'status'      => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        // Jika status diset ke "selected", buat record daftar ulang otomatis
        if ($validated['status'] === 'selected' && !$applicant->reregistration) {
            PpdbReregistration::create([
                'applicant_id' => $applicant->id,
                'status'       => PpdbReregistrationStatus::Pending->value,
            ]);
        }

        return back()->with('success', 'Status pendaftar berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.3.e — Daftar Ulang: Buat Siswa Resmi di Tabel Students
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Proses daftar ulang: buat akun User + data Student dari data pendaftar.
     * Ini adalah jembatan antara ppdb_applicants → students (siswa resmi).
     */
    public function processReregistration(Request $request, PpdbApplicant $applicant)
    {
        // Validasi: hanya bisa daftar ulang jika status = selected
        if ($applicant->status->value !== 'selected') {
            return back()->withErrors(['Pendaftar belum dinyatakan lolos seleksi.']);
        }

        if ($applicant->reregistration?->status?->value === 're_registered') {
            return back()->withErrors(['Pendaftar sudah menyelesaikan daftar ulang.']);
        }

        $validated = $request->validate([
            'nis'        => 'nullable|string|max:20',
            'notes'      => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($applicant, $validated) {

            // ── 1. Buat akun User untuk siswa baru ───────────────────────────
            $email = $applicant->email
                ?? strtolower(str_replace(' ', '.', $applicant->registration_code)) . '@siswa.sch.id';

            $user = User::create([
                'name'     => $applicant->full_name,
                'email'    => $email,
                'phone'    => $applicant->parent_phone,
                'password' => bcrypt($applicant->birth_date->format('dmY')), // Default: tanggal lahir
                'role'     => 'Siswa',
                'school_id'=> School::first()?->id,
            ]);
            $user->assignRole('Siswa');

            // ── 2. Buat data Student dari data pendaftar ──────────────────────
            $student = Student::create([
                'user_id'      => $user->id,
                'school_id'    => School::first()?->id,
                'nisn'         => $applicant->nisn,
                'nis'          => $validated['nis'] ?? null,
                'name'         => $applicant->full_name,
                'gender'       => $applicant->gender,
                'birth_place'  => $applicant->birth_place,
                'birth_date'   => $applicant->birth_date,
                'address'      => $applicant->address,
                'parent_name'  => $applicant->parent_name,
                'parent_phone' => $applicant->parent_phone,
                'entry_year'   => now()->year,
                'status'       => 'aktif',
            ]);

            // ── 3. Selesaikan daftar ulang, simpan FK ke student ─────────────
            $applicant->reregistration()->updateOrCreate(
                ['applicant_id' => $applicant->id],
                [
                    'status'       => PpdbReregistrationStatus::Completed->value,
                    'student_id'   => $student->id,
                    'completed_at' => now(),
                    'notes'        => $validated['notes'] ?? 'Daftar ulang selesai diproses oleh panitia.',
                ]
            );

            // ── 4. Update status pendaftar menjadi re_registered ─────────────
            $applicant->update([
                'status'      => PpdbApplicantStatus::ReRegistered->value,
                'admin_notes' => 'Daftar ulang selesai. Akun siswa telah dibuat.',
            ]);
        });

        return back()->with('success',
            "Daftar ulang {$applicant->full_name} berhasil! Akun siswa telah dibuat dengan password default (tanggal lahir format ddmmyyyy)."
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6.3.f — Rekap Kebutuhan Seragam
    // ─────────────────────────────────────────────────────────────────────────

    public function uniformRecap(Request $request)
    {
        // Ambil semua data seragam, join ke applicant untuk filter status
        $query = PpdbUniformOrder::with('applicant.wave')
            ->whereHas('applicant', function ($q) {
                // Hanya hitung yang tidak ditolak (exclude rejected)
                $q->whereNotIn('status', ['rejected']);
            });

        if ($request->filled('wave_id')) {
            $query->whereHas('applicant', fn($q) => $q->where('wave_id', $request->wave_id));
        }

        $orders = $query->get();

        // Buat tabel rekap: dikelompokkan per kombinasi gender + ukuran + kerudung + bawahan
        $recap = $orders->groupBy(function ($order) {
            $key = $order->ukuran . '|' . $order->gender;
            if ($order->gender === 'perempuan') {
                $key .= '|' . ($order->pakai_kerudung ? 'kerudung' : 'tanpa-kerudung');
                $key .= '|' . $order->jenis_bawahan;
            } else {
                $key .= '|celana';
            }
            return $key;
        })->map(function ($group) {
            $first = $group->first();
            return [
                'ukuran'         => $first->ukuran,
                'gender'         => $first->gender,
                'pakai_kerudung' => $first->pakai_kerudung,
                'jenis_bawahan'  => $first->jenis_bawahan,
                'jumlah'         => $group->count(),
                'description'    => $first->description(),
            ];
        })->sortBy('ukuran')->values();

        $totalOrders = $orders->count();
        $waves       = PpdbWave::orderByDesc('created_at')->get();

        // Summary per ukuran (untuk ringkasan cepat)
        $perUkuran = $orders->groupBy('ukuran')->map->count()->sortKeys();

        return view('admin.ppdb.uniform-recap', compact('recap', 'totalOrders', 'waves', 'perUkuran'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Manajemen Gelombang (CRUD sederhana)
    // ─────────────────────────────────────────────────────────────────────────

    public function waves()
    {
        $waves = PpdbWave::withCount('applicants')->orderByDesc('created_at')->get();
        return view('admin.ppdb.waves', compact('waves'));
    }

    public function storeWave(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'quota'            => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'registration_fee' => 'nullable|numeric|min:0',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string|max:500',
        ]);

        $validated['registration_fee'] = $validated['registration_fee'] ?? 0;
        $validated['is_active']        = $request->boolean('is_active');

        PpdbWave::create($validated);

        return redirect()->route('admin.ppdb.waves')
            ->with('success', "Gelombang \"{$validated['name']}\" berhasil dibuat.");
    }

    public function updateWave(Request $request, PpdbWave $wave)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'quota'            => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'registration_fee' => 'nullable|numeric|min:0',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string|max:500',
        ]);

        $validated['registration_fee'] = $validated['registration_fee'] ?? 0;
        $validated['is_active']        = $request->boolean('is_active');

        $wave->update($validated);

        return redirect()->route('admin.ppdb.waves')
            ->with('success', "Gelombang \"{$wave->name}\" berhasil diperbarui.");
    }
}
