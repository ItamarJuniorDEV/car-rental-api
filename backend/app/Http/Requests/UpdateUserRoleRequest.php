<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $caller = $this->user();

        if ($caller === null) {
            return false;
        }

        $target = $this->resolveTarget();

        if ($target === null) {
            return $caller->isAdmin();
        }

        return $caller->can('updateRole', $target);
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'in:admin,user'],
        ];
    }

    public function resolveTarget(): ?User
    {
        $raw = $this->route('id');

        return $raw === null ? null : User::find($raw);
    }
}
