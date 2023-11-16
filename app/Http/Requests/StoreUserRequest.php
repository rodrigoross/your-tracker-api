<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ["required", "email", "unique:users,email"],
            'name' => ["required", "string", "max:254"],
            'password' => ["required", "confirmed", new Password(8)],
            'device_name' => "required",
        ];
    }
}
