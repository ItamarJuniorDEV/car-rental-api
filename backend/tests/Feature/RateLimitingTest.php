<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->flush();
    }

    public function test_login_bloqueia_apos_5_tentativas_falhas(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'inexistente@example.com',
                'password' => 'errada',
            ])->assertStatus(422);
        }

        $this->postJson('/api/login', [
            'email' => 'inexistente@example.com',
            'password' => 'errada',
        ])->assertStatus(429);
    }

    public function test_limiter_de_login_e_por_email_e_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'um@example.com',
                'password' => 'errada',
            ])->assertStatus(422);
        }

        $this->postJson('/api/login', [
            'email' => 'outro@example.com',
            'password' => 'errada',
        ])->assertStatus(422);
    }

    public function test_api_global_limita_em_60_por_minuto(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($user)->getJson('/api/me')->assertOk();
        }

        $this->actingAs($user)->getJson('/api/me')->assertStatus(429);
    }
}
