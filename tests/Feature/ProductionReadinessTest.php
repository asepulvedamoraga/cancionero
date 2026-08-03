<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_check_fails_safely_for_a_testing_environment(): void
    {
        config([
            'app.env' => 'testing',
            'app.debug' => true,
            'app.url' => 'http://localhost',
            'session.secure' => false,
            'mail.default' => 'array',
            'mail.from.address' => 'noreply@example.com',
            'database.connections.mysql.password' => 'super-secret-sentinel',
        ]);

        $this->artisan('cancionero:production-check')
            ->expectsOutputToContain('no está lista para producción')
            ->assertFailed();

        $output = Artisan::output();
        $this->assertStringNotContainsString('super-secret-sentinel', $output);
    }

    public function test_export_cleanup_is_registered_in_the_scheduler(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('repertoire:cleanup-exports')
            ->assertSuccessful();
    }
}
