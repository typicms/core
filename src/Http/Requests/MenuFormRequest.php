<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class MenuFormRequest extends AbstractFormRequest
{
    /** @return array<string, list<Unique|string>> */
    public function rules(): array
    {
        return [
            'image_id' => ['nullable', 'integer'],
            'name' => ['required', 'max:255', 'alpha_dash', Rule::unique('menus')->ignore($this->route('menu'))],
            'class' => ['nullable', 'max:255'],
            'status.*' => ['boolean'],
        ];
    }
}
