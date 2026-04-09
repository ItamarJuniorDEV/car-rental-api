<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Brand::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:30|unique:brands,name',
            'image' => 'nullable|string|max:100',
        ];
    }
}
