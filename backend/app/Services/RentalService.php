<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalService
{
    public function create(array $data): Rental
    {
        return DB::transaction(function () use ($data) {
            $car = Car::lockForUpdate()->find($data['car_id']);

            if (! $car->available) {
                throw ValidationException::withMessages(['car_id' => ['O veículo não está disponível para locação.']]);
            }

            $rental = Rental::create($data);

            $car->available = false;
            $car->save();

            return $rental->load(['client', 'car']);
        });
    }

    public function update(Rental $rental, array $data): Rental
    {
        $rental->fill($data);
        $rental->save();

        if (isset($data['period_actual_end_date'])) {
            $rental->car()->update(['available' => true, 'km' => $data['final_km']]);
        }

        return $rental->fresh(['client', 'car']);
    }

    public function delete(Rental $rental): void
    {
        $rental->car()->update(['available' => true]);
        $rental->delete();
    }

    public function calcularTaxas(Rental $rental): array
    {
        $days = max(1, ceil($rental->period_start_date->diffInHours($rental->period_expected_end_date) / 24));
        $total = $days * $rental->daily_rate;
        $lateFee = 0;

        if ($rental->period_actual_end_date && $rental->period_actual_end_date->gt($rental->period_expected_end_date)) {
            $lateDays = ceil($rental->period_expected_end_date->diffInHours($rental->period_actual_end_date) / 24);
            $lateFee = $lateDays * ($rental->daily_rate * 0.5);
            $total += $lateFee;
        }

        return ['late_fee' => $lateFee, 'total' => $total];
    }
}
