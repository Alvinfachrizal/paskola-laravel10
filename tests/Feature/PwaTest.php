<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $school = School::first() ?? School::create([
            'name'    => 'SMA PWA Test',
            'npsn'    => '11223344',
            'address' => 'Jl. PWA No. 1',
        ]);

        Role::firstOrCreate(['name' => 'Admin']);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_pwa_test@paskola.com'],
            [
                'name'      => 'Admin PWA',
                'password'  => bcrypt('password'),
                'school_id' => $school->id,
                'role'      => 'Admin',
            ]
        );
        $this->admin->assignRole('Admin');
    }

    public function test_manifest_file_is_accessible_and_valid(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $json = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($json);
        $this->assertEquals('paskola-sims-app', $json['id']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('/', $json['start_url']);
        $this->assertNotEmpty($json['icons']);
    }

    public function test_service_worker_file_exists_and_contains_push_logic(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString("self.addEventListener('push'", $content);
        $this->assertStringContainsString("self.addEventListener('notificationclick'", $content);
        $this->assertStringContainsString("caches.open", $content);
    }

    public function test_offline_page_exists(): void
    {
        $offlinePath = public_path('offline.html');
        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('Offline', $content);
    }

    public function test_login_page_renders_pwa_meta_tags(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('href="/manifest.json"', false);
        $response->assertSee('apple-mobile-web-app-capable', false);
        $response->assertSee('/sw.js', false);
    }

    public function test_authenticated_layout_renders_pwa_meta_and_prompts(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('pwa-install-banner', false);
        $response->assertSee('pwa-ios-banner', false);
        $response->assertSee('pwa-push-permission-card', false);
    }
}

