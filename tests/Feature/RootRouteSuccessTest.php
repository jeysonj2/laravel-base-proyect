<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RootRouteSuccessTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Assert redirect to /api/documentation
        $response->assertStatus(302);
        $response->assertRedirect('/api/documentation');
    }
}
