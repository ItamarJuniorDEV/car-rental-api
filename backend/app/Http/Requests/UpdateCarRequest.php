<?php

namespace App\Http\Requests;

use App\Models\Car;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $car = $this->route('car');

        if (! $car instanceof Car) {
            $car = Car::find($car);
        }

        return $car !== null && ($this->user()?->can('update', $car) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'line_id' => 'sometimes|integer|exists:lines,id',
            'plate' => ['sometimes', 'string', 'max:10', Rule::unique('cars', 'plate')->ignore($this->route('car'))],
            'available' => 'sometimes|boolean',
            'km' => 'sometimes|integer|min:0',
        ];
    }
}
