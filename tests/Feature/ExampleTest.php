<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_is_private(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
