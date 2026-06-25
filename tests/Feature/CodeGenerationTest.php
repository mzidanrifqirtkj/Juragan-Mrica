<?php

namespace Tests\Feature;

use App\Models\Farmer;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CodeGeneratorService;
use App\Support\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Farmer $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupPermissionsAndRoles();

        $this->farmer = Farmer::factory()->create([
            'name' => 'Test Farmer',
            'phone' => '081234567890',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'role' => 'admin',
        ]);
        $this->admin->assignRole('admin');
    }

    private function setupPermissionsAndRoles(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RolePermissionMatrix::permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (RolePermissionMatrix::defaults() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }
    }

    public function test_farmer_code_format(): void
    {
        $code = CodeGeneratorService::generateFarmerCode();
        $this->assertMatchesRegularExpression('/^PET\d{3}$/', $code);
    }

    public function test_transaction_code_format(): void
    {
        $code = CodeGeneratorService::generateTransactionCode(now());
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{3}$/', $code);
    }

    public function test_sale_code_format(): void
    {
        $code = CodeGeneratorService::generateSaleCode(now());
        $this->assertMatchesRegularExpression('/^SALE-\d{8}-\d{3}$/', $code);
    }

    public function test_farmer_code_increments(): void
    {
        $this->actingAs($this->admin);

        // setUp already creates a farmer (PET001), so these are PET002 and PET003
        $f1 = Farmer::create(['name' => 'F1', 'phone' => '0811111111']);
        $f2 = Farmer::create(['name' => 'F2', 'phone' => '0811111112']);

        $code1 = (int) substr($f1->farmer_code, 3);
        $code2 = (int) substr($f2->farmer_code, 3);

        $this->assertEquals($code1 + 1, $code2);
        $this->assertEquals('PET002', $f1->farmer_code);
        $this->assertEquals('PET003', $f2->farmer_code);
    }

    public function test_transaction_code_increments_per_day(): void
    {
        $this->actingAs($this->admin);

        $t1 = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 10.0,
            'price_per_kg' => 100000,
            'total_amount' => 1000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $t2 = Transaction::create([
            'farmer_id' => $this->farmer->id,
            'weight_kg' => 20.0,
            'price_per_kg' => 100000,
            'total_amount' => 2000000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'transaction_date' => now(),
            'created_by' => $this->admin->id,
        ]);

        $todayPrefix = 'TRX-'.now()->format('Ymd').'-';
        $this->assertStringStartsWith($todayPrefix, $t1->transaction_code);
        $this->assertStringStartsWith($todayPrefix, $t2->transaction_code);

        $seq1 = (int) substr($t1->transaction_code, -3);
        $seq2 = (int) substr($t2->transaction_code, -3);
        $this->assertEquals($seq1 + 1, $seq2);
    }
}
