# AGENTS.md — Warung Setor (Juragan-Mrica)

Single Laravel 12 + Filament 4 admin panel. Not a monorepo. npm (not pnpm/yarn). Indonesian locale (`id`).

## Commands

| Action | Command |
|--------|---------|
| Dev server (4 concurrent: server+queue+logs+vite HMR) | `composer dev` |
| Run full test suite (SQLite :memory:, no DB needed) | `composer test` (runs `config:clear` first) |
| Run single test | `php artisan test tests/Feature/XTest.php --filter=method` |
| Full setup (fresh clone) | `composer setup` |
| PHP linter | `./vendor/bin/pint` (Laravel Pint, default config) |
| Frontend build | `npm run build` |
| Frontend dev (Vite HMR only) | `npm run dev` |

## Architecture

- **Panel**: Filament v4 at `/admin` with custom login (single field accepts username OR email)
- **Auth**: Spatie Laravel Permission, 3 roles — `owner` (full), `admin` (no role/permission editing), `petani` (own data only)
- **Permissions**: Defined in `app/Support/RolePermissionMatrix.php`, enforced via `app/Support/Access.php`
- **Models**: `Farmer` → hasMany `Transaction` (setoran/deposits). `Sale` (penjualan/pindah stok) has polymorphic pivot to Transaction. Users link to Farmer via `farmer_id`.
- **Inventory**: Running balance in `inventory_logs` table. `TransactionObserver` and `SaleObserver` auto-create/update/delete logs. `InventoryService` (addStock/reduceStock/getCurrentStock). Observers recalculate balance when weight changes.
- **Code generation**: `app/Services/CodeGeneratorService.php` — `PET001`, `TRX-YYYYMMDD-001`, `SALE-YYYYMMDD-001`
- **Settings**: Key-value via `App\Models\Setting` with in-memory cache
- **Queue**: `file` driver (sync). Broadcasting: `log` (no websockets). Filament notifications poll every 30s.
- **Storage**: AWS S3-compatible (`is3.cloudhost.id`, bucket `pplq`, path-style endpoint)
- **Navigation groups**: Transaksi, Penyimpanan, Laporan, Master Data, Pengaturan (defined in `NavigationGroup` enum)

## Testing

- PHPUnit 11 via `composer test`. Tests use SQLite `:memory:` — no external DB service needed.
- Observers fire during tests. Be mindful when creating/updating Transaction/Sale models — they auto-create inventory logs.

## Frontend

- Vite + Tailwind CSS v4 (`@tailwindcss/vite` plugin). Inputs: `resources/css/app.css`, `resources/js/app.js`.
- No JS frameworks beyond Filament's Livewire. No React/Vue.
- Vite watch ignores `storage/framework/views/**`.

## Docs

- `README.md` — full setup, seeder accounts, workflows
- `PANDUAN-OPERASIONAL.md` — operator guide (Indonesian)
- `PANDUAN-PETANI.md` — farmer guide (Indonesian)
- `SOP-ADMIN-OPERATOR.md` — daily SOP (Indonesian)
