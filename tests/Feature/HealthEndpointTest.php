<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_homepage_responds_with_200_and_brand_title(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('DEEN');
        $response->assertSee('Electronics');
    }

    public function test_health_check_endpoint_returns_ok_json(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
            'app' => 'DEEN Electronics',
        ]);
        $response->assertJsonStructure([
            'status',
            'app',
            'framework',
            'environment',
            'timestamp',
        ]);
    }

    public function test_health_db_endpoint_returns_json_response(): void
    {
        $response = $this->get('/health/db');

        $this->assertContains($response->getStatusCode(), [200, 503]);
        $response->assertJsonStructure([
            'status',
            'database',
        ]);
    }
}
