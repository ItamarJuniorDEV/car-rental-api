<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $brand = $this->route('brand');

        if (! $brand instanceof Brand) {
            $brand = Brand::find($brand);
        }

        return $brand !== null && ($this->user()?->can('update', $brand) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:30', Rule::unique('brands', 'name')->ignore($this->route('brand'))],
            'image' => 'sometimes|string|max:100',
        ];
    }
}
