<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Override;

class UsersFormRequest extends AbstractFormRequest
{
    #[Override]
    protected function prepareForValidation(): void
    {
        if (! $this->user()?->isSuperUser()) {
            $this->request->remove('superuser');
        }
    }

    /** @return array<string, list<Unique|string>> */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')),
            ],
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'street' => ['nullable', 'max:255'],
            'number' => ['nullable', 'max:255'],
            'box' => ['nullable', 'max:255'],
            'postal_code' => ['nullable', 'max:255'],
            'city' => ['nullable', 'max:255'],
            'country' => ['nullable', 'max:255'],
            'phone' => ['nullable', 'max:100'],
            'locale' => ['required', 'max:5'],
            'activated' => ['boolean'],
            'superuser' => ['boolean'],
            'privacy_policy_accepted' => ['boolean'],
        ];
    }
}
