<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        if (getenv('DB_CONNECTION') === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is not available.');
        }

        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['products.view', 'products.create', 'products.update', 'products.delete'] as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'sanctum',
            ]);
        }

        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
        ]);
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_user_can_create_product(): void
    {
        $category = Category::create([
            'code' => 'RAW',
            'name' => 'Raw Material',
        ]);
        $unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        $response = $this->postJson('/api/v1/inventory/products', [
            'sku' => 'rm-001',
            'barcode' => '899000000101',
            'name' => 'Raw Material 001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'type' => 'raw_material',
            'min_stock' => 5,
            'description' => 'Testing product',
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sku', 'RM-001')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.unit.id', $unit->id);

        $this->assertDatabaseHas('products', [
            'sku' => 'RM-001',
            'barcode' => '899000000101',
        ]);
    }

    public function test_user_can_list_filter_and_find_product_by_barcode(): void
    {
        $category = Category::create([
            'code' => 'FG',
            'name' => 'Finished Good',
        ]);
        $unit = Unit::create([
            'code' => 'PCS',
            'name' => 'Pieces',
        ]);

        Product::create([
            'sku' => 'FG-001',
            'barcode' => '899000000201',
            'name' => 'Finished Good 001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'type' => 'finished_good',
            'min_stock' => 2,
        ]);

        $this->getJson('/api/v1/inventory/products?search=Finished&type=finished_good')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.sku', 'FG-001')
            ->assertJsonPath('meta.total', 1);

        $this->getJson('/api/v1/inventory/products/barcode/899000000201')
            ->assertOk()
            ->assertJsonPath('data.sku', 'FG-001');
    }

    public function test_user_can_update_and_delete_product(): void
    {
        $category = Category::create([
            'code' => 'PACK',
            'name' => 'Packaging',
        ]);
        $unit = Unit::create([
            'code' => 'BOX',
            'name' => 'Box',
        ]);
        $product = Product::create([
            'sku' => 'PACK-001',
            'barcode' => '899000000301',
            'name' => 'Packaging 001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'type' => 'packaging',
            'min_stock' => 3,
        ]);

        $this->putJson("/api/v1/inventory/products/{$product->id}", [
            'name' => 'Packaging Updated',
            'min_stock' => 8,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Packaging Updated')
            ->assertJsonPath('data.min_stock', '8.0000');

        $this->deleteJson("/api/v1/inventory/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
