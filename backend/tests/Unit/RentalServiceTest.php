<?php

namespace Tests\Unit;

use App\Models\Car;
use App\Models\Client;
use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RentalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcular_taxas_devolucao_em_dia_nao_cobra_multa(): void
    {
        $rental = Rental::factory()->make([
            'period_start_date' => now(),
            'period_expected_end_date' => now()->addDays(3),
            'period_actual_end_date' => now()->addDays(3),
            'daily_rate' => 200,
        ]);

        $taxas = app(RentalService::class)->calcularTaxas($rental);

        $this->assertSame(0.0, (float) $taxas['late_fee']);
        $this->assertSame(600.0, (float) $taxas['total']);
    }

    public function test_calcular_taxas_cobra_50_porcento_por_dia_de_atraso(): void
    {
        $rental = Rental::factory()->make([
            'period_start_date' => now(),
            'period_expected_end_date' => now()->addDays(2),
            'period_actual_end_date' => now()->addDays(4),
            'daily_rate' => 100,
        ]);

        $taxas = app(RentalService::class)->calcularTaxas($rental);

        $this->assertSame(100.0, (float) $taxas['late_fee']);
        $this->assertSame(300.0, (float) $taxas['total']);
    }

    public function test_create_marca_carro_como_indisponivel(): void
    {
        $car = Car::factory()->create(['available' => true]);
        $client = Client::factory()->create();

        app(RentalService::class)->create([
            'client_id' => $client->id,
            'car_id' => $car->id,
            'period_start_date' => now(),
            'period_expected_end_date' => now()->addDays(2),
            'daily_rate' => 150,
            'initial_km' => 10000,
        ]);

        $this->assertFalse($car->fresh()->available);
    }

    public function test_create_rejeita_carro_indisponivel(): void
    {
        $car = Car::factory()->create(['available' => false]);
        $client = Client::factory()->create();

        $this->expectException(ValidationException::class);

        app(RentalService::class)->create([
            'client_id' => $client->id,
            'car_id' => $car->id,
            'period_start_date' => now(),
            'period_expected_end_date' => now()->addDays(2),
            'daily_rate' => 150,
            'initial_km' => 10000,
        ]);
    }
}
