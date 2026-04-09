<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'brand_id' => $this->brand_id,
            'name' => $this->name,
            'image' => $this->image,
            'door_count' => $this->door_count,
            'seats' => $this->seats,
            'air_bag' => $this->air_bag,
            'abs' => $this->abs,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
