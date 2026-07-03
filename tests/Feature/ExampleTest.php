<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_spa_route_returns_ok(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
