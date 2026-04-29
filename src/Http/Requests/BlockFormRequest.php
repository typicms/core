<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class BlockFormRequest extends AbstractFormRequest
{
    /** @return array<string, list<Unique|string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', 'alpha_dash', Rule::unique('blocks')->ignore($this->route('block'))],
            'status.*' => ['boolean'],
            'body.*' => ['nullable', 'max:20000'],
        ];
    }
}
