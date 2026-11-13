<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\PushSubscription;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use DatabaseTransactions;

    private School $school;
    private User $admin;
    private User $guru;
    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->school = School::first() ?? School::create([
            'name'    => 'SMA Test',
            'npsn'    => '99887766',
            'address' => 'Jl. Pendidikan No. 9',
        ]);

        // Pastikan roles ada
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Guru']);
        Role::firstOrCreate(['name' => 'Siswa']);

        // Admin
        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test@paskola.com'],
            [
                'name'      => 'Admin Test',
                'password'  => bcrypt('password'),
                'school_id' => $this->school->id,
                'role'      => 'Admin',
            ]
        );
        $this->admin->assignRole('Admin');

        // Guru
        $this->guru = User::firstOrCreate(
            ['email' => 'guru_test@paskola.com'],
            [
                'name'      => 'Guru Test',
                'password'  => bcrypt('password'),
                'school_id' => $this->school->id,
                'role'      => 'Guru',
            ]
        );
        $this->guru->assignRole('Guru');

        // Siswa
        $this->siswa = User::firstOrCreate(
            ['email' => 'siswa_test@paskola.com'],
            [
                'name'      => 'Siswa Test',
                'password'  => bcrypt('password'),
                'school_id' => $this->school->id,
                'role'      => 'Siswa',
            ]
        );
        $this->siswa->assignRole('Siswa');
    }

    public function test_user_can_view_announcements_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('announcements.index'));
        $response->assertStatus(200);
        $response->assertViewIs('announcements.index');
    }

    public function test_admin_can_create_and_publish_announcement(): void
    {
        $payload = [
            'title'        => 'Pengumuman Uji Coba Admin',
            'content'      => 'Ini adalah isi pengumuman uji coba.',
            'target_roles' => ['Guru', 'Siswa'],
            'priority'     => 'urgent',
            'publish_now'  => 1,
            'is_pinned'    => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('announcements.store'), $payload);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'school_id' => $this->school->id,
            'title'     => 'Pengumuman Uji Coba Admin',
            'priority'  => 'urgent',
            'is_pinned' => true,
        ]);
    }

    public function test_guru_can_create_announcement(): void
    {
        $payload = [
            'title'        => 'Pengumuman Dari Guru',
            'content'      => 'Harap persiapkan materi bab 3.',
            'target_roles' => ['Siswa'],
            'priority'     => 'normal',
            'publish_now'  => 1,
        ];

        $response = $this->actingAs($this->guru)->post(route('announcements.store'), $payload);

        $response->assertRedirect(route('announcements.index'));
        $this->assertDatabaseHas('announcements', [
            'author_id' => $this->guru->id,
            'title'     => 'Pengumuman Dari Guru',
        ]);
    }

    public function test_siswa_cannot_create_announcement(): void
    {
        $payload = [
            'title'       => 'Upaya Siswa Buat Pengumuman',
            'content'     => 'Tidak boleh lolos.',
            'publish_now' => 1,
        ];

        $response = $this->actingAs($this->siswa)->post(route('announcements.store'), $payload);

        $response->assertStatus(403);
    }

    public function test_siswa_cannot_view_draft_announcement(): void
    {
        $draft = Announcement::create([
            'school_id'    => $this->school->id,
            'author_id'    => $this->admin->id,
            'title'        => 'Draf Rahasia Internal',
            'content'      => 'Belum boleh dilihat siswa.',
            'published_at' => null, // Draft
        ]);

        $response = $this->actingAs($this->siswa)->get(route('announcements.show', $draft->id));

        $response->assertStatus(403);
    }

    public function test_announcement_auto_marks_as_read_when_viewed(): void
    {
        $announcement = Announcement::create([
            'school_id'    => $this->school->id,
            'author_id'    => $this->admin->id,
            'title'        => 'Pengumuman Terbuka Untuk Siswa',
            'content'      => 'Harap dibaca semua siswa.',
            'target_roles' => ['Siswa'],
            'published_at' => Carbon::now()->subHour(),
        ]);

        $this->assertDatabaseMissing('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id'         => $this->siswa->id,
        ]);

        $response = $this->actingAs($this->siswa)->get(route('announcements.show', $announcement->id));
        $response->assertStatus(200);

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id'         => $this->siswa->id,
        ]);
    }

    public function test_unread_count_api_works(): void
    {
        Announcement::create([
            'school_id'    => $this->school->id,
            'author_id'    => $this->admin->id,
            'title'        => 'Pengumuman Baru Belum Dibaca',
            'content'      => 'Cek notifikasi.',
            'target_roles' => ['Siswa'],
            'published_at' => Carbon::now()->subMinute(),
        ]);

        $response = $this->actingAs($this->siswa)->getJson(route('announcements.unread-count'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['count']);
        $this->assertGreaterThan(0, $response->json('count'));
    }

    public function test_push_subscription_store_and_destroy(): void
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-endpoint-' . uniqid();

        // 1. Subscribe
        $response = $this->actingAs($this->siswa)->postJson(route('push.subscribe'), [
            'endpoint'   => $endpoint,
            'public_key' => 'BLC9test_key_sample',
            'auth_token' => 'auth_token_sample',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id'    => $this->siswa->id,
            'public_key' => 'BLC9test_key_sample',
        ]);

        // 2. Status
        $statusResponse = $this->actingAs($this->siswa)->getJson(route('push.status'));
        $statusResponse->assertStatus(200)->assertJson(['subscribed' => true]);

        // 3. Unsubscribe
        $delResponse = $this->actingAs($this->siswa)->postJson(route('push.unsubscribe'), [
            'endpoint' => $endpoint,
        ]);

        $delResponse->assertStatus(200);
        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id'  => $this->siswa->id,
            'endpoint' => $endpoint,
        ]);
    }
}
