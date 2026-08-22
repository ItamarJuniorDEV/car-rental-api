<?php

namespace App\Http\Requests;

use App\Models\Line;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $line = $this->route('line');

        if (! $line instanceof Line) {
            $line = Line::find($line);
        }

        return $line !== null && ($this->user()?->can('update', $line) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'brand_id' => 'sometimes|integer|exists:brands,id',
            'name' => 'sometimes|string|max:30',
            'image' => 'sometimes|string|max:100',
            'door_count' => 'sometimes|integer|min:1',
            'seats' => 'sometimes|integer|min:1',
            'air_bag' => 'sometimes|boolean',
            'abs' => 'sometimes|boolean',
        ];
    }
}
