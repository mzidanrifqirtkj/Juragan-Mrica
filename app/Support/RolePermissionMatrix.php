<?php

namespace App\Support;

class RolePermissionMatrix
{
    public const FEATURES = [
        'dashboard' => [
            'label' => 'Dashboard',
            'actions' => ['view'],
        ],
        'transactions' => [
            'label' => 'Setoran',
            'actions' => ['view', 'create', 'edit', 'delete', 'custom'],
        ],
        'sales' => [
            'label' => 'Pindah ke Gudang',
            'actions' => ['view', 'create', 'edit', 'delete'],
        ],
        'inventory' => [
            'label' => 'Penyimpanan',
            'actions' => ['view'],
        ],
        'reports' => [
            'label' => 'Laporan',
            'actions' => ['view'],
        ],
        'farmers' => [
            'label' => 'Petani',
            'actions' => ['view', 'create', 'edit', 'delete', 'custom'],
        ],
        'users' => [
            'label' => 'Pengguna',
            'actions' => ['view', 'create', 'edit', 'delete', 'custom'],
        ],
        'role_permissions' => [
            'label' => 'Role & Fitur',
            'actions' => ['view', 'edit'],
        ],
    ];

    public const ACTION_LABELS = [
        'view' => 'Lihat menu & halaman',
        'create' => 'Tambah data',
        'edit' => 'Ubah data',
        'delete' => 'Hapus data',
        'custom' => 'Aksi khusus',
    ];

    public static function permissions(): array
    {
        $permissions = [];

        foreach (self::FEATURES as $feature => $config) {
            foreach ($config['actions'] as $action) {
                $permissions[] = self::permission($feature, $action);
            }
        }

        return $permissions;
    }

    public static function permission(string $feature, string $action): string
    {
        return "{$feature}.{$action}";
    }

    public static function groupedOptions(): array
    {
        $groups = [];

        foreach (self::FEATURES as $feature => $config) {
            $groups[$feature] = [
                'label' => $config['label'],
                'options' => [],
            ];

            foreach ($config['actions'] as $action) {
                $groups[$feature]['options'][self::permission($feature, $action)] = self::ACTION_LABELS[$action] ?? ucfirst($action);
            }
        }

        return $groups;
    }

    public static function viewPermissions(): array
    {
        return array_map(
            fn (string $feature) => self::permission($feature, 'view'),
            array_keys(self::FEATURES),
        );
    }

    public static function defaults(): array
    {
        return [
            'owner' => self::permissions(),
            'admin' => array_values(array_filter(
                self::permissions(),
                fn (string $permission) => ! str_starts_with($permission, 'role_permissions.'),
            )),
            'petani' => [
                'dashboard.view',
                'transactions.view',
            ],
        ];
    }
}
