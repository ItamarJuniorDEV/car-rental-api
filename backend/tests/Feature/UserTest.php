<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operador;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->operador = User::factory()->create();
    }

    public function test_admin_pode_listar_usuarios()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_operador_nao_pode_listar_usuarios()
    {
        $response = $this->actingAs($this->operador, 'sanctum')->getJson('/api/users');

        $response->assertForbidden();
    }

    public function test_sem_autenticacao_retorna_401()
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_admin_pode_promover_usuario_para_admin()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/users/{$this->operador->id}/role", ['role' => 'admin']);

        $response->assertOk()
            ->assertJsonFragment(['role' => 'admin']);

        $this->assertDatabaseHas('users', ['id' => $this->operador->id, 'role' => 'admin']);
    }

    public function test_admin_pode_revogar_role_admin()
    {
        $outroAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/users/{$outroAdmin->id}/role", ['role' => 'user']);

        $response->assertOk()
            ->assertJsonFragment(['role' => 'user']);
    }

    public function test_operador_nao_pode_alterar_role()
    {
        $response = $this->actingAs($this->operador, 'sanctum')
            ->patchJson("/api/users/{$this->admin->id}/role", ['role' => 'user']);

        $response->assertForbidden();
    }

    public function test_role_invalido_retorna_erro()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/users/{$this->operador->id}/role", ['role' => 'superuser']);

        $response->assertUnprocessable();
    }

    public function test_usuario_inexistente_retorna_404()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson('/api/users/99999/role', ['role' => 'admin']);

        $response->assertNotFound();
    }

    public function test_admin_nao_pode_alterar_proprio_role()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/users/{$this->admin->id}/role", ['role' => 'user']);

        $response->assertUnprocessable()
            ->assertJsonFragment(['erro' => 'Não é possível alterar o próprio perfil.']);
    }

    public function test_admin_pode_criar_usuario()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Novo Operador',
                'email' => 'operador@locadora.com',
                'password' => 'senha123',
                'role' => 'user',
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['email' => 'operador@locadora.com', 'role' => 'user']);

        $this->assertDatabaseHas('users', ['email' => 'operador@locadora.com']);
    }

    public function test_operador_nao_pode_criar_usuario()
    {
        $response = $this->actingAs($this->operador, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Teste',
                'email' => 'teste@locadora.com',
                'password' => 'senha123',
                'role' => 'user',
            ]);

        $response->assertForbidden();
    }

    public function test_email_duplicado_retorna_erro()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Cópia',
                'email' => $this->operador->email,
                'password' => 'senha123',
                'role' => 'user',
            ]);

        $response->assertUnprocessable();
    }
}
