<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class RolesFormRequest extends AbstractFormRequest
{
    /** @return array<string, list<Unique|string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:4', 'max:255', Rule::unique('roles')->ignore($this->route('role'))],
        ];
    }
}
