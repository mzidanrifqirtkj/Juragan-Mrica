<?php

namespace App\Support;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class Access
{
    public static function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public static function can(string $permission): bool
    {
        $user = self::user();

        return $user?->can($permission) ?? false;
    }

    public static function any(array $permissions): bool
    {
        $user = self::user();

        return $user?->hasAnyPermission($permissions) ?? false;
    }

    public static function role(string $role): bool
    {
        $user = self::user();

        return $user?->hasRole($role) ?? false;
    }

    public static function owner(): bool
    {
        return self::role('owner');
    }

    public static function admin(): bool
    {
        return self::role('admin');
    }

    public static function petani(): bool
    {
        return self::user()?->isPetani() ?? false;
    }

    public static function farmerId(): ?int
    {
        if (! self::hasFarmerLinking()) {
            return null;
        }

        return self::user()?->farmer_id;
    }

    public static function hasFarmerLinking(): bool
    {
        return Schema::hasColumn('users', 'farmer_id');
    }

    public static function petaniConfigured(): bool
    {
        if (! self::petani()) {
            return true;
        }

        if (! self::hasFarmerLinking()) {
            return false;
        }

        return filled(self::farmerId());
    }

    public static function restrictPetaniTransactionQuery(Builder $query): Builder
    {
        if (! self::petani()) {
            return $query;
        }

        $farmerId = self::farmerId();

        if (! $farmerId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('farmer_id', $farmerId);
    }

    public static function ownsTransaction(Transaction $transaction): bool
    {
        if (! self::petani()) {
            return true;
        }

        return self::petaniConfigured() && $transaction->farmer_id === self::farmerId();
    }
}
