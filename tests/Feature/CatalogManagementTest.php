<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LiturgicalMoment;
use App\Models\LiturgicalSeason;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_regular_user_cannot_access_catalog_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.settings'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.catalogs.index', 'categories'))->assertForbidden();
    }

    public function test_admin_can_create_a_catalog_item_with_generated_slug(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.catalogs.store', 'categories'), [
            'name' => 'Cantos de entrada',
            'slug' => '',
            'description' => 'Para iniciar la celebración.',
            'sort_order' => 4,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Cantos de entrada', 'slug' => 'cantos-de-entrada', 'sort_order' => 4, 'is_active' => true]);
    }

    public function test_slug_must_be_unique_within_its_catalog(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Category::factory()->create(['slug' => 'entrada']);

        $this->actingAs($admin)->post(route('admin.catalogs.store', 'categories'), [
            'name' => 'Entrada', 'slug' => '', 'sort_order' => 0, 'is_active' => 1,
        ])->assertSessionHasErrors('slug');
    }

    public function test_catalog_items_are_presented_in_configured_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Category::factory()->create(['name' => 'Segundo', 'sort_order' => 20]);
        Category::factory()->create(['name' => 'Primero', 'sort_order' => 10]);

        $this->actingAs($admin)->get(route('admin.catalogs.index', 'categories'))
            ->assertOk()
            ->assertSeeInOrder(['Primero', 'Segundo']);
    }

    public function test_catalog_list_can_be_searched_filtered_and_paginated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Category::factory()->count(30)->create(['is_active' => true]);
        Category::factory()->create(['name' => 'Opción inactiva única', 'is_active' => false]);

        $this->actingAs($admin)->get(route('admin.catalogs.index', ['categories', 'per_page' => 24]))
            ->assertOk()
            ->assertViewHas('items', fn ($items) => $items->perPage() === 24 && $items->count() === 24);

        $this->actingAs($admin)->get(route('admin.catalogs.index', ['categories', 'q' => 'Opción inactiva única', 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('Opción inactiva única')
            ->assertViewHas('items', fn ($items) => $items->total() === 1);

        $this->actingAs($admin)->get(route('admin.catalogs.index', ['categories', 'per_page' => 500]))
            ->assertOk()
            ->assertViewHas('items', fn ($items) => $items->perPage() === 12);
    }
    public function test_admin_can_deactivate_an_unused_item_but_not_an_item_in_use(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $unused = LiturgicalMoment::factory()->create(['is_active' => true]);
        $used = LiturgicalMoment::factory()->create(['is_active' => true]);
        Song::factory()->create(['liturgical_moment_id' => $used->id]);

        $payload = ['name' => $unused->name, 'slug' => $unused->slug, 'description' => null, 'sort_order' => 0, 'is_active' => 0];
        $this->actingAs($admin)->put(route('admin.catalogs.update', ['moments', $unused]), $payload)->assertSessionHasNoErrors();
        $this->assertFalse($unused->fresh()->is_active);

        $payload['name'] = $used->name;
        $payload['slug'] = $used->slug;
        $this->actingAs($admin)->put(route('admin.catalogs.update', ['moments', $used]), $payload)->assertSessionHasErrors('is_active');
        $this->assertTrue($used->fresh()->is_active);
    }

    public function test_used_liturgical_season_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $season = LiturgicalSeason::factory()->create(['is_active' => true]);
        $song = Song::factory()->create();
        $song->liturgicalSeasons()->attach($season);

        $this->actingAs($admin)->put(route('admin.catalogs.update', ['seasons', $season]), [
            'name' => $season->name, 'slug' => $season->slug, 'description' => null, 'sort_order' => 0, 'is_active' => 0,
        ])->assertSessionHasErrors('is_active');
        $this->assertTrue($season->fresh()->is_active);
    }
}
