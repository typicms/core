<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;


class OneTimePasswordLoginRequest extends AbstractFormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'exists:users,email',
            ],
        ];
    }
}
