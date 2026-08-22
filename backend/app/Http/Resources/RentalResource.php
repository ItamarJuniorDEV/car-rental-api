<?php

namespace App\Http\Resources;

use App\Models\Rental;
use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Rental */
class RentalResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing(['client', 'car']);
        $fees = app(RentalService::class)->calculateFees($this->resource);

        return [
            'id' => $this->id,
            'period_start_date' => $this->period_start_date->format('Y-m-d H:i:s'),
            'period_expected_end_date' => $this->period_expected_end_date->format('Y-m-d H:i:s'),
            'period_actual_end_date' => $this->period_actual_end_date?->format('Y-m-d H:i:s'),
            'daily_rate' => $this->daily_rate,
            'initial_km' => $this->initial_km,
            'final_km' => $this->final_km,
            'late_fee' => $fees['late_fee'],
            'total' => $fees['total'],
            'client' => new ClientResource($this->whenLoaded('client')),
            'car' => new CarResource($this->whenLoaded('car')),
        ];
    }
}
