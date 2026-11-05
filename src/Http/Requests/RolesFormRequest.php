<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Unique;
use Override;
use TypiCMS\Modules\Core\Models\Role;
use TypiCMS\Modules\Core\Support\Permissions;

class RolesFormRequest extends AbstractFormRequest
{
    /**
     * Permissions can only be given to or taken from a role by a user already
     * having them, otherwise updating a role is enough to gain every permission
     * of the CMS. Superusers pass through the gate registered in the module
     * service provider.
     *
     * Unknown permissions are left to the in rule below, which tells the user
     * what is wrong instead of returning a bare 403.
     */
    #[Override]
    public function authorize(): bool
    {
        foreach ($this->changedPermissions() as $permission) {
            if (! Permissions::has($permission)) {
                continue;
            }

            if (! $this->user()?->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<string, list<In|Unique|string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:4', 'max:255', Rule::unique('roles')->ignore($this->route('role'))],
            'checked_permissions' => ['sometimes', 'array'],
            'checked_permissions.*' => ['string', Rule::in(Permissions::names())],
        ];
    }

    /** @return list<string> */
    public function checkedPermissions(): array
    {
        $permissions = $this->validated('checked_permissions', []);

        return array_values(array_filter(is_array($permissions) ? $permissions : [], is_string(...)));
    }

    /**
     * Permissions this request adds to or removes from the role.
     *
     * @return list<string>
     */
    private function changedPermissions(): array
    {
        $role = $this->route('role');
        $current = $role instanceof Role
            ? array_filter($role->permissions()->pluck('name')->all(), is_string(...))
            : [];
        $submitted = array_filter($this->array('checked_permissions'), is_string(...));

        return array_values(array_unique([
            ...array_diff($submitted, $current),
            ...array_diff($current, $submitted),
        ]));
    }
}
