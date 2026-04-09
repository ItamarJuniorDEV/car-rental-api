<?php

namespace App\Http\Requests;

use App\Models\Line;
use Illuminate\Foundation\Http\FormRequest;

class StoreLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Line::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'brand_id' => 'required|integer|exists:brands,id',
            'name' => 'required|string|max:30',
            'image' => 'nullable|string|max:100',
            'door_count' => 'nullable|integer|min:1',
            'seats' => 'nullable|integer|min:1',
            'air_bag' => 'nullable|boolean',
            'abs' => 'nullable|boolean',
        ];
    }
}
