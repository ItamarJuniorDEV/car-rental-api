<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Line;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PoliciesAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operador_nao_pode_criar_marca(): void
    {
        Sanctum::actingAs(User::factory()->operador()->create());

        $this->postJson('/api/brands', ['name' => 'Toyota'])
            ->assertStatus(403);
    }

    public function test_admin_pode_criar_marca(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/brands', ['name' => 'Toyota'])
            ->assertStatus(201);
    }

    public function test_operador_nao_pode_atualizar_linha(): void
    {
        $line = Line::factory()->create();
        Sanctum::actingAs(User::factory()->operador()->create());

        $this->putJson("/api/lines/{$line->id}", ['name' => 'Civic atualizado'])
            ->assertStatus(403);
    }

    public function test_operador_nao_pode_deletar_veiculo(): void
    {
        $car = Car::factory()->create();
        Sanctum::actingAs(User::factory()->operador()->create());

        $this->deleteJson("/api/cars/{$car->id}")
            ->assertStatus(403);
    }

    public function test_operador_nao_pode_listar_usuarios(): void
    {
        Sanctum::actingAs(User::factory()->operador()->create());

        $this->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_admin_pode_listar_usuarios(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/users')
            ->assertStatus(200);
    }

    public function test_admin_nao_pode_alterar_proprio_papel(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/users/{$admin->id}/role", ['role' => 'operador'])
            ->assertStatus(422);
    }

    public function test_operador_pode_criar_cliente(): void
    {
        Sanctum::actingAs(User::factory()->operador()->create());

        $this->postJson('/api/clients', [
            'name' => 'Maria Oliveira',
            'cpf' => '123.456.789-00',
            'email' => 'maria@example.com',
            'phone' => '(51) 99999-1234',
        ])->assertStatus(201);
    }
}
