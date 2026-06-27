<?php

namespace Tests\Feature;

use App\Models\Bom;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BomApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;
    private Product $finishedGood;
    private Product $rawMaterial1;
    private Product $rawMaterial2;
    private Unit $unitPcs;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create required permissions
        foreach (['boms.view', 'boms.create', 'boms.update', 'boms.delete'] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'sanctum',
            ]);
        }

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(['boms.view', 'boms.create', 'boms.update', 'boms.delete']);

        $this->operator = User::factory()->create();
        $this->operator->givePermissionTo(['boms.view']); // read only

        // Seed basic category, units, products
        $category = Category::create(['code' => 'WOOD', 'name' => 'Wood Products']);
        $this->unitPcs = Unit::create(['code' => 'PCS', 'name' => 'Pieces']);

        $this->finishedGood = Product::create([
            'sku' => 'FG-TABLE-01',
            'name' => 'Finished Table',
            'category_id' => $category->id,
            'unit_id' => $this->unitPcs->id,
            'type' => 'finished_good',
            'min_stock' => 5,
        ]);

        $this->rawMaterial1 = Product::create([
            'sku' => 'RM-PLANK-01',
            'name' => 'Wood Plank',
            'category_id' => $category->id,
            'unit_id' => $this->unitPcs->id,
            'type' => 'raw_material',
            'min_stock' => 10,
        ]);

        $this->rawMaterial2 = Product::create([
            'sku' => 'RM-SCREW-01',
            'name' => 'Screws Pack',
            'category_id' => $category->id,
            'unit_id' => $this->unitPcs->id,
            'type' => 'packaging',
            'min_stock' => 100,
        ]);
    }

    public function test_user_can_create_bom_with_items(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $response = $this->postJson('/api/v1/production/boms', [
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-TABLE-01',
            'name' => 'Table Recipe',
            'description' => 'Planks and screws needed',
            'output_qty' => 1.0,
            'is_default' => true,
            'is_active' => true,
            'items' => [
                [
                    'material_id' => $this->rawMaterial1->id,
                    'qty_needed' => 4.0,
                    'unit_id' => $this->unitPcs->id,
                    'notes' => 'Top and legs',
                ],
                [
                    'material_id' => $this->rawMaterial2->id,
                    'qty_needed' => 12.0,
                    'unit_id' => $this->unitPcs->id,
                    'notes' => 'Assembly fasteners',
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'BOM-TABLE-01')
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('boms', [
            'code' => 'BOM-TABLE-01',
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('bom_items', [
            'material_id' => $this->rawMaterial1->id,
            'qty_needed' => 4.0000,
        ]);
    }

    public function test_create_bom_unsetting_other_default(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        // Create first default BOM
        $bom1 = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-OLD-01',
            'name' => 'Old Recipe',
            'output_qty' => 1.0,
            'is_default' => true,
            'is_active' => true,
        ]);

        // Create second default BOM via API
        $response = $this->postJson('/api/v1/production/boms', [
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-NEW-01',
            'name' => 'New Recipe',
            'output_qty' => 1.0,
            'is_default' => true,
            'is_active' => true,
            'items' => [
                [
                    'material_id' => $this->rawMaterial1->id,
                    'qty_needed' => 4.0,
                ]
            ]
        ]);

        $response->assertStatus(201);

        // Verify bom1 has is_default = false
        $this->assertDatabaseHas('boms', [
            'id' => $bom1->id,
            'is_default' => false,
        ]);

        // Verify new BOM has is_default = true
        $this->assertDatabaseHas('boms', [
            'code' => 'BOM-NEW-01',
            'is_default' => true,
        ]);
    }

    public function test_create_bom_validation_failures(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        // Invalid output product type (cannot build BOM for raw material)
        $response = $this->postJson('/api/v1/production/boms', [
            'product_id' => $this->rawMaterial1->id,
            'code' => 'BOM-FAIL-01',
            'name' => 'Failed Recipe',
            'output_qty' => 1.0,
            'is_default' => true,
            'is_active' => true,
            'items' => [
                [
                    'material_id' => $this->rawMaterial2->id,
                    'qty_needed' => 1.0,
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_list_and_filter_boms(): void
    {
        // Setup two BOMs
        $bom1 = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-A',
            'name' => 'Recipe A',
            'output_qty' => 1.0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $bom2 = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-B',
            'name' => 'Recipe B',
            'output_qty' => 1.0,
            'is_default' => false,
            'is_active' => false,
        ]);

        $this->actingAs($this->operator, 'sanctum'); // read only permission

        // Test normal list
        $response = $this->getJson('/api/v1/production/boms');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');

        // Test filter by active
        $responseActive = $this->getJson('/api/v1/production/boms?is_active=1');
        $responseActive->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'BOM-A');

        // Test search
        $responseSearch = $this->getJson('/api/v1/production/boms?search=Recipe B');
        $responseSearch->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'BOM-B');
    }

    public function test_user_can_update_bom_and_sync_items(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $bom = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-UPDATE-ME',
            'name' => 'Original Name',
            'output_qty' => 1.0,
            'is_default' => false,
            'is_active' => true,
        ]);

        $item = $bom->items()->create([
            'material_id' => $this->rawMaterial1->id,
            'qty_needed' => 2.0,
            'unit_id' => $this->unitPcs->id,
        ]);

        // Update name and sync items (replace plank with screws)
        $response = $this->putJson("/api/v1/production/boms/{$bom->id}", [
            'name' => 'Updated Name',
            'items' => [
                [
                    'material_id' => $this->rawMaterial2->id,
                    'qty_needed' => 20.0,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.material_id', $this->rawMaterial2->id);

        $this->assertDatabaseMissing('bom_items', [
            'id' => $item->id,
        ]);

        $this->assertDatabaseHas('bom_items', [
            'bom_id' => $bom->id,
            'material_id' => $this->rawMaterial2->id,
            'qty_needed' => 20.0000,
        ]);
    }

    public function test_user_can_delete_bom(): void
    {
        $this->actingAs($this->admin, 'sanctum');

        $bom = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-DELETE-ME',
            'name' => 'To Delete',
            'output_qty' => 1.0,
            'is_default' => false,
            'is_active' => true,
        ]);

        $bom->items()->create([
            'material_id' => $this->rawMaterial1->id,
            'qty_needed' => 2.0,
        ]);

        $response = $this->deleteJson("/api/v1/production/boms/{$bom->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('boms', [
            'id' => $bom->id,
        ]);

        $this->assertDatabaseMissing('bom_items', [
            'bom_id' => $bom->id,
        ]);
    }

    public function test_user_can_get_boms_by_product(): void
    {
        $this->actingAs($this->operator, 'sanctum');

        $bom = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-PRODUCT-FG',
            'name' => 'For Product',
            'output_qty' => 1.0,
            'is_default' => true,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/inventory/products/{$this->finishedGood->id}/boms");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'BOM-PRODUCT-FG');
    }

    public function test_unauthorized_user_cannot_create_or_delete_bom(): void
    {
        $this->actingAs($this->operator, 'sanctum'); // read only boms.view

        $responseCreate = $this->postJson('/api/v1/production/boms', [
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-NO-AUTH',
            'name' => 'No Auth Recipe',
            'output_qty' => 1.0,
            'is_default' => false,
            'is_active' => true,
            'items' => [
                [
                    'material_id' => $this->rawMaterial1->id,
                    'qty_needed' => 1.0,
                ]
            ]
        ]);

        $responseCreate->assertStatus(403);

        $bom = Bom::create([
            'product_id' => $this->finishedGood->id,
            'code' => 'BOM-NO-AUTH-2',
            'name' => 'No Auth Recipe 2',
            'output_qty' => 1.0,
            'is_default' => false,
            'is_active' => true,
        ]);

        $responseDelete = $this->deleteJson("/api/v1/production/boms/{$bom->id}");
        $responseDelete->assertStatus(403);
    }
}
