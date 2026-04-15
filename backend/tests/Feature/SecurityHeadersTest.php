<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_resposta_da_api_carrega_headers_de_seguranca(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/me');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
    }

    public function test_endpoint_publico_de_login_tambem_carrega_headers(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'naoexiste@example.com',
            'password' => 'senha1234',
        ]);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_csp_e_restritivo_para_api_json(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/me');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_hsts_so_aparece_em_producao_com_https(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/me');

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }
}
