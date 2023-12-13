<?php

namespace App\Http\Requests;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePackageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'unique:packages,code'],
        ];
    }

    public function authorize(): bool
    {
        return Gate::allows('create', Package::class);
    }
}
