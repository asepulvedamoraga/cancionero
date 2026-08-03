<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_catalogs_and_admin_from_configuration(): void
    {
        config()->set('cancionero.admin', ['name' => 'Admin Test', 'email' => 'admin@test.local', 'password' => 'StrongPass123!']);
        $this->seed(DatabaseSeeder::class);
        $admin = User::whereEmail('admin@test.local')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('StrongPass123!', $admin->password));
        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseCount('liturgical_moments', 13);
        $this->assertDatabaseCount('liturgical_seasons', 9);
    }
}
