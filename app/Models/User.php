<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'farmer_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'farmer_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if (! Schema::hasColumn('users', 'farmer_id')) {
                return;
            }

            if ($user->role !== 'petani') {
                $user->farmer_id = null;
            }
        });

        static::saved(function (self $user): void {
            if (! Schema::hasTable('roles')) {
                return;
            }

            $role = $user->normalizeRole($user->role);

            if ($role) {
                $user->syncRoles([$role]);
            }
        });
    }

    /**
     * Check if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user is owner
     */
    public function isOwner(): bool
    {
        return $this->hasRole('owner') || $this->role === 'owner';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin';
    }

    /**
     * Check if user is petani
     */
    public function isPetani(): bool
    {
        return $this->hasRole('petani') || in_array($this->role, ['petani', 'kasir'], true);
    }

    /**
     * Legacy alias during role transition
     */
    public function isKasir(): bool
    {
        return $this->isPetani();
    }

    /**
     * Get transactions created by this user
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    /**
     * Get the linked farmer profile for this user.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get sales created by this user
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function hasFarmerProfile(): bool
    {
        if (! Schema::hasColumn('users', 'farmer_id')) {
            return false;
        }

        return filled($this->farmer_id);
    }

    public static function generateUniqueUsername(string $name, ?string $fallback = null): string
    {
        $base = Str::of($name)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();

        if ($base === '' && $fallback) {
            $base = Str::of($fallback)
                ->before('@')
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '')
                ->value();
        }

        $base = $base !== '' ? $base : 'user';
        $username = $base;
        $suffix = 1;

        while (Schema::hasColumn('users', 'username') && static::query()->where('username', $username)->exists()) {
            $suffix++;
            $username = $base . $suffix;
        }

        return $username;
    }

    private function normalizeRole(?string $role): ?string
    {
        return match ($role) {
            'kasir' => 'petani',
            'owner', 'admin', 'petani' => $role,
            default => null,
        };
    }
}
