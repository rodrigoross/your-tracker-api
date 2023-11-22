<?php

namespace App\Http\Requests;

use App\Enums\PackageIcon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdatePackageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'alias' => ['sometimes'],
            'icon' => ['sometimes', 'in:' . implode(',', PackageIcon::toArray())],
        ];
    }

    public function authorize(): bool
    {
        return Gate::allows('update', $this->package);
    }
}
