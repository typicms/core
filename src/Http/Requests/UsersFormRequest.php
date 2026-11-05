<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Override;
use TypiCMS\Modules\Core\Models\Role;
use TypiCMS\Modules\Core\Models\User;

class UsersFormRequest extends AbstractFormRequest
{
    /**
     * Roles can only be given to or taken from a user by someone already having
     * every permission they carry, otherwise updating a user is enough to gain
     * the administrator role. Superusers pass through the gate registered in
     * the module service provider.
     *
     * Unknown roles are left to the exists rule below.
     */
    #[Override]
    public function authorize(): bool
    {
        $target = $this->route('user');

        if ($target instanceof User && $target->isSuperUser()) {
            return (bool) $this->user()?->isSuperUser();
        }

        return $this->mayChangeRoles();
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        if (! $this->user()?->isSuperUser()) {
            $this->merge(['superuser' => false]);
        }
    }

    /** @return array<string, list<Exists|Unique|string>> */
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
            'checked_roles' => ['sometimes', 'array'],
            'checked_roles.*' => ['integer', Rule::exists('roles', 'id')],
        ];
    }

    private function mayChangeRoles(): bool
    {
        $roles = Role::query()->with('permissions')->findMany($this->changedRoles());

        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                if (! $this->user()?->can($permission->name)) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @return list<int> */
    public function checkedRoles(): array
    {
        $roles = $this->validated('checked_roles', []);

        return array_values(array_map(intval(...), array_filter(is_array($roles) ? $roles : [], is_numeric(...))));
    }

    /**
     * Roles this request gives to or takes from the user.
     *
     * @return list<int>
     */
    private function changedRoles(): array
    {
        $target = $this->route('user');
        $current = $target instanceof User
            ? array_map(intval(...), array_filter($target->roles()->pluck('id')->all(), is_numeric(...)))
            : [];
        $submitted = array_map(intval(...), array_filter($this->array('checked_roles'), is_numeric(...)));

        return array_values(array_unique([
            ...array_diff($submitted, $current),
            ...array_diff($current, $submitted),
        ]));
    }
}
