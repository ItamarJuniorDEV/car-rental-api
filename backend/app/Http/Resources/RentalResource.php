<?php

namespace App\Http\Resources;

use App\Services\RentalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing(['client', 'car']);
        $fees = app(RentalService::class)->calcularTaxas($this->resource);

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
            'client' => $this->client,
            'car' => $this->car,
        ];
    }
}
