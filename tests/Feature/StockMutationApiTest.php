<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StockMutationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    private Warehouse $rawWarehouse;

    private Warehouse $finishedWarehouse;

    protected function setUp(): void
    {
        if (getenv('DB_CONNECTION') === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available.');
        }

        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['stocks.view', 'mutations.view', 'mutations.create'] as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'sanctum',
            ]);
        }

        $this->user = User::factory()->create();
        $this->user->givePermissionTo(['stocks.view', 'mutations.view', 'mutations.create']);
        $this->actingAs($this->user, 'sanctum');

        $category = Category::create([
            'code' => 'RAW',
            'name' => 'Raw Material',
        ]);
        $unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        $this->product = Product::create([
            'sku' => 'RM-INV-001',
            'barcode' => '899000009001',
            'name' => 'Inventory Material',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'type' => 'raw_material',
            'min_stock' => 10,
        ]);

        $this->rawWarehouse = Warehouse::create([
            'code' => 'RAW-01',
            'name' => 'Raw Warehouse',
            'type' => 'raw',
        ]);
        $this->finishedWarehouse = Warehouse::create([
            'code' => 'FG-01',
            'name' => 'Finished Warehouse',
            'type' => 'finished',
        ]);
    }

    public function test_user_can_create_stock_in_mutation_and_view_stock(): void
    {
        $response = $this->postJson('/api/v1/inventory/mutations', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'to_warehouse_id' => $this->rawWarehouse->id,
            'qty' => 25,
            'reference_no' => 'GRN-001',
            'notes' => 'Initial stock',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'in')
            ->assertJsonPath('data.qty', '25.0000')
            ->assertJsonPath('data.to_warehouse.id', $this->rawWarehouse->id);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->rawWarehouse->id,
            'qty' => '25.0000',
        ]);

        $this->getJson('/api/v1/inventory/stocks?search=Inventory')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.qty', '25.0000')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_user_can_create_stock_out_and_transfer_mutations(): void
    {
        $this->postJson('/api/v1/inventory/mutations', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'to_warehouse_id' => $this->rawWarehouse->id,
            'qty' => 30,
        ])->assertCreated();

        $this->postJson('/api/v1/inventory/mutations', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'from_warehouse_id' => $this->rawWarehouse->id,
            'qty' => 5,
        ])->assertCreated();

        $this->postJson('/api/v1/inventory/mutations', [
            'product_id' => $this->product->id,
            'type' => 'transfer',
            'from_warehouse_id' => $this->rawWarehouse->id,
            'to_warehouse_id' => $this->finishedWarehouse->id,
            'qty' => 10,
        ])->assertCreated();

        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->rawWarehouse->id,
            'qty' => '15.0000',
        ]);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->finishedWarehouse->id,
            'qty' => '10.0000',
        ]);
    }

    public function test_stock_out_fails_when_stock_is_not_enough(): void
    {
        $this->postJson('/api/v1/inventory/mutations', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'from_warehouse_id' => $this->rawWarehouse->id,
            'qty' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('stock_mutations', [
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_scan_product_barcode_with_current_stocks(): void
    {
        $this->postJson('/api/v1/inventory/mutations', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'to_warehouse_id' => $this->rawWarehouse->id,
            'qty' => 7,
        ])->assertCreated();

        $this->getJson('/api/v1/inventory/scan/899000009001')
            ->assertOk()
            ->assertJsonPath('data.sku', 'RM-INV-001')
            ->assertJsonPath('data.stocks.0.qty', '7.0000')
            ->assertJsonPath('data.stocks.0.warehouse.id', $this->rawWarehouse->id);
    }
}
