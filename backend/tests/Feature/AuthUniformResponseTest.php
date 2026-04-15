<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthUniformResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_email_inexistente_retorna_mesma_mensagem_que_senha_errada(): void
    {
        User::factory()->create([
            'email' => 'existente@example.com',
            'password' => bcrypt('senhaCerta'),
        ]);

        $emailInexistente = $this->postJson('/api/login', [
            'email' => 'inexistente@example.com',
            'password' => 'qualquer',
        ]);

        $senhaErrada = $this->postJson('/api/login', [
            'email' => 'existente@example.com',
            'password' => 'errada',
        ]);

        $emailInexistente->assertStatus(422);
        $senhaErrada->assertStatus(422);

        $this->assertSame(
            $emailInexistente->json('message'),
            $senhaErrada->json('message'),
            'Mensagens precisam ser identicas para evitar enumeracao de usuario.'
        );

        $this->assertSame(
            $emailInexistente->json('errors.email'),
            $senhaErrada->json('errors.email'),
            'Detalhes do erro precisam ser identicos para evitar enumeracao de usuario.'
        );
    }

    public function test_login_tem_tempo_minimo_constante(): void
    {
        User::factory()->create([
            'email' => 'existente@example.com',
            'password' => bcrypt('senhaCerta'),
        ]);

        $startInexistente = microtime(true);
        $this->postJson('/api/login', [
            'email' => 'inexistente@example.com',
            'password' => 'qualquer',
        ]);
        $duracaoInexistente = microtime(true) - $startInexistente;

        $startSenhaErrada = microtime(true);
        $this->postJson('/api/login', [
            'email' => 'existente@example.com',
            'password' => 'errada',
        ]);
        $duracaoSenhaErrada = microtime(true) - $startSenhaErrada;

        $this->assertGreaterThan(0.4, $duracaoInexistente, 'Timebox deve garantir minimo de 500ms');
        $this->assertGreaterThan(0.4, $duracaoSenhaErrada, 'Timebox deve garantir minimo de 500ms');
    }
}
