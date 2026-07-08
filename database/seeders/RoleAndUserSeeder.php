<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();
        
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles
        $roles = [
            'Super Admin',
            'Admin',
            'Kepala Sekolah',
            'Guru',
            'Siswa',
            'Ortu'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // create default school
        $school = School::firstOrCreate(
            ['npsn' => '12345678'],
            [
                'name' => 'Sekolah Nusantara',
                'address' => 'Jl. Pendidikan No. 1',
                'phone' => '021-123456',
                'email' => 'info@sekolahnusantara.sch.id',
                'level' => 'SMA'
            ]
        );

        // create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@paskola.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'school_id' => $school->id,
                'role' => 'Super Admin',
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        // create admin sekolah
        $admin = User::firstOrCreate(
            ['email' => 'admin@paskola.com'],
            [
                'name' => 'Admin TU',
                'password' => Hash::make('password123'),
                'school_id' => $school->id,
                'role' => 'Admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('Admin');

        // create guru
        $guru = User::firstOrCreate(
            ['email' => 'guru@paskola.com'],
            [
                'name' => 'Budi Guru',
                'password' => Hash::make('password123'),
                'school_id' => $school->id,
                'role' => 'Guru',
                'email_verified_at' => now(),
            ]
        );
        $guru->assignRole('Guru');

        // create siswa
        $siswa = User::firstOrCreate(
            ['email' => 'siswa@paskola.com'],
            [
                'name' => 'Andi Siswa',
                'password' => Hash::make('password123'),
                'school_id' => $school->id,
                'role' => 'Siswa',
                'email_verified_at' => now(),
            ]
        );
        $siswa->assignRole('Siswa');
    }
}
