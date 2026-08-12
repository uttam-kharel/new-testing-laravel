<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Skip the Vite manifest so the test runs on a fresh checkout
        // before `npm run build` has produced public/build.
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
