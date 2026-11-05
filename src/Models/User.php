<?php

declare(strict_types=1);

namespace TypiCMS\Modules\Core\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Override;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use TypiCMS\Modules\Core\Traits\HasAdminUrls;
use TypiCMS\Modules\Core\Traits\Historable;

/**
 * @property int $id
 * @property string $email
 * @property bool $activated
 * @property bool $superuser
 * @property bool $privacy_policy_accepted
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $locale
 * @property string|null $street
 * @property string|null $number
 * @property string|null $box
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property array<array-key, mixed>|null $preferences
 * @property string $api_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read mixed $all_permissions
 * @property-read Collection<int, History> $history
 * @property-read int|null $history_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 */
#[Unguarded]
#[Hidden([
    'api_token',
])]
class User extends Model implements AuthenticatableContract, AuthorizableContract, HasLocalePreference, HasPasskeys
{
    use Authenticatable;
    use Authorizable;
    use HasAdminUrls;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasOneTimePasswords;
    use HasRoles;
    use Historable;
    use InteractsWithPasskeys;
    use Notifiable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    public function presentTitle(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'preferences' => 'array',
            'superuser' => 'boolean',
            'activated' => 'boolean',
            'privacy_policy_accepted' => 'boolean',
        ];
    }

    protected function getDefaultGuardName(): string
    {
        // https://github.com/spatie/laravel-permission/issues/1156#issue-466659658
        return 'web';
    }

    public function getPasskeyDisplayName(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function preferredLocale()
    {
        return $this->locale;
    }

    #[Override]
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $user): void {
            $user->api_token = Str::uuid()->toString();
        });
    }

    public function isSuperUser(): bool
    {
        return (bool) $this->superuser;
    }

    /** @return Attribute<string[], null> */
    protected function allPermissions(): Attribute
    {
        $permissions = [];
        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            if ($user->isSuperUser()) {
                $permissions = ['all'];
            }

            foreach (Permission::all() as $permission) {
                if ($user->can($permission->name)) {
                    $permissions[] = $permission->name;
                }
            }
        }

        return Attribute::make(get: fn (): array => $permissions);
    }

    public function isImpersonating(): bool
    {
        return Session::has('impersonation');
    }
}
