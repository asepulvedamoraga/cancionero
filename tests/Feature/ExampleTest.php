<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_public_landing_is_available(): void
    {
        $this->withoutVite();
        $this->get('/')
            ->assertOk()
            ->assertSee('Comunidad abierta');
    }
}
