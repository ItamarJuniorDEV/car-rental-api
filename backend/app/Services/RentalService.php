<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalService
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Rental
    {
        return DB::transaction(function () use ($data) {
            $car = Car::lockForUpdate()->findOrFail($data['car_id']);

            if (! $car->available) {
                throw ValidationException::withMessages(['car_id' => ['O veículo não está disponível para locação.']]);
            }

            $data['initial_km'] = $car->km;

            $rental = Rental::create($data);

            $car->available = false;
            $car->save();

            return $rental->load(['client', 'car']);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Rental $rental, array $data): Rental
    {
        return DB::transaction(function () use ($rental, $data) {
            $lockedRental = Rental::query()->lockForUpdate()->findOrFail($rental->id);
            $car = Car::query()->lockForUpdate()->findOrFail($lockedRental->car_id);

            if (isset($data['period_actual_end_date']) && $lockedRental->period_actual_end_date !== null) {
                throw ValidationException::withMessages([
                    'period_actual_end_date' => ['A locação já foi finalizada.'],
                ]);
            }

            $lockedRental->fill($data);
            $lockedRental->save();

            if (isset($data['period_actual_end_date'])) {
                $car->update([
                    'available' => true,
                    'km' => $data['final_km'],
                ]);
            }

            return $lockedRental->fresh(['client', 'car']);
        });
    }

    public function delete(Rental $rental): void
    {
        DB::transaction(function () use ($rental) {
            $lockedRental = Rental::query()->lockForUpdate()->findOrFail($rental->id);
            $car = Car::query()->lockForUpdate()->findOrFail($lockedRental->car_id);

            $lockedRental->delete();

            $hasActiveRental = Rental::query()
                ->where('car_id', $car->id)
                ->whereNull('period_actual_end_date')
                ->exists();

            $car->update(['available' => ! $hasActiveRental]);
        });
    }

    /** @return array{late_fee: float|int, total: float|int} */
    public function calculateFees(Rental $rental): array
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
