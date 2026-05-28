# S-FIPT Development Tasks

Dokumen ini dibuat dari hasil pengecekan `PRD-SFIPT.md` terhadap kondisi repo saat ini.

Tanggal pengecekan: 2026-05-27

## Ringkasan Status

| Area | Status | Catatan |
|---|---|---|
| Authentication | Partial | Login, logout, dan current user sudah ada, tetapi endpoint belum sama dengan PRD. |
| RBAC / Permission | Partial | Sudah memakai permission middleware dan seeder permission, tetapi role matrix PRD belum lengkap untuk semua modul. |
| Master Unit | Done / perlu test | CRUD, request, resource, model, migration, route sudah ada. |
| Master Category | Done / perlu test | CRUD, request, resource, model, migration, route sudah ada. |
| Master Warehouse | Done / perlu test | CRUD, request, resource, model, migration, route sudah ada. |
| Master Product | Implemented / perlu test penuh | Migration, model, controller, request, resource, route, permission, seeder, dokumentasi, dan feature test dasar sudah dibuat. |
| Inventory Stock | Implemented / perlu test penuh | Table `stocks`, read API, low stock filter, dan scan API sudah dibuat. |
| Stock Mutation | Implemented / perlu test penuh | Table `stock_mutations`, service, controller, request, route, dan feature test dasar sudah dibuat. |
| BOM | Not started | Belum ada table `boms` dan `bom_items`. |
| Production Order | Not started | Belum ada table, service, controller, request, route. |
| Dashboard | Not started | Belum ada dashboard controller dan endpoint aggregation. |
| API Documentation | Partial | Dokumentasi lama belum lengkap untuk Unit/Category dan belum sinkron dengan route aktual. |
| Automated Tests | Not started | Belum ada feature test untuk endpoint utama. |

## Fitur yang Sudah Ada

### 1. Authentication

File terkait:

- `app/Http/Controllers/Api/V1/AuthController.php`
- `routes/api.php`

Yang sudah ada:

- `POST /api/v1/login`
- `GET /api/v1/me`
- `POST /api/v1/logout`
- Token Sanctum dibuat saat login.
- Current user memuat roles dan permissions.

Gap terhadap PRD:

- PRD meminta `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, dan `GET /api/v1/user/profile`.
- Validasi login masih inline di controller, belum memakai Form Request.
- Response message masih bahasa Inggris, sementara PRD contoh memakai bahasa Indonesia.

Task:

- [ ] Putuskan apakah route mengikuti PRD atau route aktual.
- [ ] Jika mengikuti PRD, tambahkan alias route `/auth/login`, `/auth/logout`, dan `/user/profile`.
- [ ] Buat `LoginRequest`.
- [ ] Standarkan response memakai `ApiResponse`.
- [ ] Tambahkan rate limit login.
- [ ] Tambahkan feature test login, me/profile, dan logout.

### 2. RBAC / Role Permission

File terkait:

- `database/seeders/RbacSeeder.php`
- `routes/api.php`
- `config/permission.php`

Yang sudah ada:

- Role `admin`, `warehouse_operator`, dan `production_manager`.
- Permission untuk users, roles, permissions, warehouses, categories, units.
- Middleware permission sudah dipasang pada route.

Gap terhadap PRD:

- Role `viewer` / `manager` belum ada.
- Permission untuk products, stocks, mutations, BOM, production order, dashboard belum ada.
- Mapping akses role belum mengikuti modul PRD sepenuhnya.

Task:

- [ ] Tambahkan role `viewer` atau `manager` sesuai keputusan produk.
- [ ] Tambahkan permission `products.*`.
- [ ] Tambahkan permission `stocks.view`.
- [ ] Tambahkan permission `mutations.view`, `mutations.create`.
- [ ] Tambahkan permission `boms.*`.
- [ ] Tambahkan permission `production-orders.*`.
- [ ] Tambahkan permission `dashboard.view`.
- [ ] Update assignment permission per role.
- [ ] Tambahkan test akses berdasarkan role minimal untuk modul utama.

### 3. Master Unit

File terkait:

- `database/migrations/2026_05_19_000001_create_units_table.php`
- `app/Models/Unit.php`
- `app/Http/Controllers/Api/V1/Admin/UnitController.php`
- `app/Http/Requests/Api/V1/Admin/MasterData/StoreUnitRequest.php`
- `app/Http/Requests/Api/V1/Admin/MasterData/UpdateUnitRequest.php`
- `app/Http/Resources/Api/V1/Admin/UnitResource.php`
- `database/seeders/UnitSeeder.php`

Yang sudah ada:

- Table `units`.
- CRUD API.
- Search `search` dan `q`.
- Pagination.
- Sort dan direction.
- Validasi kode unik.
- Response resource.

Task lanjutan:

- [ ] Tambahkan feature test CRUD Unit.
- [ ] Pastikan delete gagal dengan status yang tepat jika unit sudah dipakai produk.
- [ ] Update API documentation untuk endpoint Unit aktual.

### 4. Master Category

File terkait:

- `database/migrations/2026_05_19_000000_create_categories_table.php`
- `app/Models/Category.php`
- `app/Http/Controllers/Api/V1/Admin/CategoryController.php`
- `app/Http/Requests/Api/V1/Admin/MasterData/StoreCategoryRequest.php`
- `app/Http/Requests/Api/V1/Admin/MasterData/UpdateCategoryRequest.php`
- `app/Http/Resources/Api/V1/Admin/CategoryResource.php`
- `database/seeders/CategorySeeder.php`

Yang sudah ada:

- Table `categories`.
- CRUD API.
- Search `search` dan `q`.
- Pagination.
- Sort dan direction.
- Validasi kode unik.
- Response resource.

Task lanjutan:

- [ ] Tambahkan feature test CRUD Category.
- [ ] Pastikan delete gagal dengan status yang tepat jika category sudah dipakai produk.
- [ ] Update API documentation untuk endpoint Category aktual.

### 5. Master Warehouse

File terkait:

- `database/migrations/2026_04_23_102344_create_warehouses_table.php`
- `database/migrations/2026_05_25_000000_update_warehouses_for_master_api_foundation.php`
- `app/Models/Warehouse.php`
- `app/Http/Controllers/Api/V1/Admin/WarehouseController.php`
- `app/Http/Requests/Api/V1/Admin/MasterData/StoreWarehouseRequest.php`
- `app/Http/Requests/Api/V1/Admin/MasterData/UpdateWarehouseRequest.php`
- `app/Http/Resources/Api/V1/Admin/WarehouseResource.php`

Yang sudah ada:

- Table `warehouses`.
- Field `code`, `name`, `location`, `type`, `is_active`.
- Field audit `created_by` dan `updated_by`.
- CRUD API.
- Search `search` dan `q`.
- Pagination.
- Sort dan direction.
- Validasi kode unik.
- Type mendukung `raw`, `wip`, `finished`, `general`.

Task lanjutan:

- [ ] Tambahkan feature test CRUD Warehouse.
- [ ] Pastikan delete gagal dengan status yang tepat jika warehouse sudah dipakai stock atau mutation.
- [ ] Update API documentation karena dokumentasi lama belum menyebut `code` dan masih belum sinkron dengan type `general`.

## Fitur yang Belum Dibuat

### 6. Master Product

Status: Implemented / perlu test penuh

Priority: High

Dependensi:

- Unit
- Category

Task backend:

- [x] Buat migration `products`.
- [x] Field: `sku`, `barcode`, `name`, `category_id`, `unit_id`, `type`, `min_stock`, `description`, `is_active`.
- [x] Tambahkan unique index untuk `sku`.
- [x] Tambahkan unique nullable index untuk `barcode`.
- [x] Tambahkan foreign key ke `categories` dan `units`.
- [x] Buat model `Product`.
- [x] Tambahkan relationship `category()` dan `unit()`.
- [x] Tambahkan relationship balik dari `Category` dan `Unit` ke products.
- [x] Buat `StoreProductRequest`.
- [x] Buat `UpdateProductRequest`.
- [x] Buat `ProductResource`.
- [x] Buat `ProductController`.
- [x] Endpoint `GET /api/v1/inventory/products`.
- [x] Endpoint `POST /api/v1/inventory/products`.
- [x] Endpoint `GET /api/v1/inventory/products/{product}`.
- [x] Endpoint `PUT /api/v1/inventory/products/{product}`.
- [x] Endpoint `DELETE /api/v1/inventory/products/{product}`.
- [x] Endpoint `GET /api/v1/inventory/products/barcode/{barcode}`.
- [x] Tambahkan filter `search`, `type`, `category_id`, `is_active`.
- [x] Tambahkan eager loading category dan unit.
- [x] Tambahkan permission `products.view/create/update/delete`.
- [x] Tambahkan seeder contoh product.
- [x] Jalankan migration aktual di database lokal.
- [x] Tambahkan feature test Product.

Acceptance criteria:

- [x] SKU wajib unik.
- [x] Barcode opsional tetapi unik jika diisi.
- [x] Product wajib terhubung ke category dan unit.
- [x] Product tidak bisa dihapus jika sudah memiliki stock mutation. Catatan: menunggu modul `stock_mutations`.
- [x] Search bisa berdasarkan name, SKU, atau barcode.

### 7. Inventory Stock

Priority: High

Dependensi:

- Product
- Warehouse
- Stock Mutation

Task backend:

- [x] Buat migration `stocks`.
- [x] Field: `product_id`, `warehouse_id`, `qty`.
- [x] Tambahkan unique index gabungan `product_id` dan `warehouse_id`.
- [x] Buat model `Stock`.
- [x] Tambahkan relationship ke `Product` dan `Warehouse`.
- [x] Buat `StockResource`.
- [x] Buat `StockController`.
- [x] Endpoint `GET /api/v1/inventory/stocks`.
- [x] Endpoint `GET /api/v1/inventory/stocks/{stock}`.
- [x] Endpoint `GET /api/v1/inventory/scan/{barcode}`.
- [x] Tambahkan filter `product_id`, `warehouse_id`, `low_stock`, `search`.
- [x] Implementasikan low stock query berdasarkan `stocks.qty < products.min_stock`.
- [x] Tambahkan permission `stocks.view`.

Acceptance criteria:

- [x] Stock tidak diinput langsung oleh user.
- [x] Stock otomatis terbentuk saat mutation pertama.
- [x] Kombinasi product dan warehouse unik.
- [x] Low stock dapat difilter.

### 8. Stock Mutation

Priority: High

Dependensi:

- Product
- Warehouse
- Stock

Task backend:

- [x] Buat migration `stock_mutations`.
- [x] Field sesuai PRD: `mutation_number`, `product_id`, `type`, `from_warehouse_id`, `to_warehouse_id`, `qty`, `reference_type`, `reference_id`, `reference_no`, `notes`, `created_by`.
- [x] Tambahkan unique index `mutation_number`.
- [x] Tambahkan index untuk `product_id`, warehouse, reference, dan created date.
- [x] Buat model `StockMutation`.
- [x] Buat `StockMutationResource`.
- [x] Buat `StoreStockMutationRequest`.
- [x] Buat `StockMutationController`.
- [x] Endpoint `GET /api/v1/inventory/mutations`.
- [x] Endpoint `POST /api/v1/inventory/mutations`.
- [x] Endpoint `GET /api/v1/inventory/mutations/{mutation}`.
- [x] Buat `StockService`.
- [x] Implementasikan `stockIn`.
- [x] Implementasikan `stockOut`.
- [x] Implementasikan `transfer`.
- [x] Implementasikan `adjustment`.
- [x] Semua perubahan stock wajib memakai `DB::transaction()`.
- [x] Gunakan row lock saat update stock untuk mengurangi risiko stock tidak sinkron.
- [x] Generate `mutation_number` otomatis.
- [x] Tambahkan permission `mutations.view` dan `mutations.create`.

Acceptance criteria:

- [x] Mutation `in` menambah stock.
- [x] Mutation `out` mengurangi stock.
- [x] Mutation `transfer` mengurangi gudang asal dan menambah gudang tujuan.
- [x] Mutation gagal jika stock tidak cukup.
- [x] Mutation tersimpan sebagai audit trail.
- [x] Mutation tidak disediakan endpoint delete.

### 9. Bill of Materials

Priority: Medium

Dependensi:

- Product
- Unit

Task backend:

- [ ] Buat migration `boms`.
- [ ] Buat migration `bom_items`.
- [ ] Buat model `Bom`.
- [ ] Buat model `BomItem`.
- [ ] Tambahkan relationship `Bom` ke product dan items.
- [ ] Tambahkan relationship `BomItem` ke material product dan unit.
- [ ] Buat `StoreBomRequest`.
- [ ] Buat `UpdateBomRequest`.
- [ ] Buat `BomResource`.
- [ ] Buat `BomController`.
- [ ] Endpoint `GET /api/v1/boms`.
- [ ] Endpoint `POST /api/v1/boms`.
- [ ] Endpoint `GET /api/v1/boms/{bom}`.
- [ ] Endpoint `PUT /api/v1/boms/{bom}`.
- [ ] Endpoint `DELETE /api/v1/boms/{bom}`.
- [ ] Endpoint `GET /api/v1/inventory/products/{product}/boms`.
- [ ] Implementasikan create/update BOM bersama items dalam transaction.
- [ ] Validasi output product hanya `finished_good` atau `semi_finished`.
- [ ] Validasi material product hanya `raw_material`, `semi_finished`, atau `packaging`.
- [ ] Tambahkan permission `boms.view/create/update/delete`.

Acceptance criteria:

- [ ] BOM minimal memiliki satu item.
- [ ] `qty_needed` wajib lebih dari 0.
- [ ] Satu product bisa punya lebih dari satu BOM.
- [ ] Jika `is_default` true, default BOM lain untuk product yang sama harus disesuaikan.

### 10. Production Order

Priority: Medium

Dependensi:

- Product
- Warehouse
- BOM
- Stock
- Stock Mutation

Task backend:

- [ ] Buat migration `production_orders`.
- [ ] Buat migration `production_order_materials`.
- [ ] Buat model `ProductionOrder`.
- [ ] Buat model `ProductionOrderMaterial`.
- [ ] Buat `ProductionOrderResource`.
- [ ] Buat `StoreProductionOrderRequest`.
- [ ] Buat `UpdateProductionOrderRequest`.
- [ ] Buat `UpdateProductionOrderStatusRequest`.
- [ ] Buat `ExecuteProductionOrderRequest`.
- [ ] Buat `ProductionOrderController`.
- [ ] Buat `ProductionService`.
- [ ] Endpoint `GET /api/v1/production-orders`.
- [ ] Endpoint `POST /api/v1/production-orders`.
- [ ] Endpoint `GET /api/v1/production-orders/{productionOrder}`.
- [ ] Endpoint `PUT /api/v1/production-orders/{productionOrder}`.
- [ ] Endpoint `PUT /api/v1/production-orders/{productionOrder}/status`.
- [ ] Endpoint `POST /api/v1/production-orders/{productionOrder}/execute`.
- [ ] Endpoint `POST /api/v1/production-orders/{productionOrder}/cancel`.
- [ ] Generate `order_number` otomatis.
- [ ] Hitung `required_qty = bom_item.qty_needed * target_qty / bom.output_qty`.
- [ ] Saat execute, validasi stock material cukup.
- [ ] Saat execute, buat mutation `production_out` untuk material.
- [ ] Saat execute, buat mutation `production_in` untuk barang jadi.
- [ ] Saat execute, update status ke `completed`.
- [ ] Tambahkan permission `production-orders.view/create/update/execute/cancel`.

Acceptance criteria:

- [ ] Production order bisa dibuat dari BOM.
- [ ] Material requirement tersimpan saat order dibuat.
- [ ] Execute gagal jika material tidak cukup.
- [ ] Execute berhasil mengurangi material dan menambah finished good.
- [ ] Semua proses execute memakai transaction.

### 11. Dashboard

Priority: Medium

Dependensi:

- Product
- Warehouse
- Stock
- Stock Mutation
- Production Order

Task backend:

- [ ] Buat `DashboardController`.
- [ ] Endpoint `GET /api/v1/dashboard/summary`.
- [ ] Endpoint `GET /api/v1/dashboard/low-stock`.
- [ ] Endpoint `GET /api/v1/dashboard/production-chart`.
- [ ] Endpoint `GET /api/v1/dashboard/recent-mutations`.
- [ ] Buat query aggregation untuk total products.
- [ ] Buat query aggregation untuk total warehouses.
- [ ] Buat query low stock count.
- [ ] Buat query active production orders.
- [ ] Buat query completed production orders.
- [ ] Buat query chart produksi mingguan atau bulanan.
- [ ] Tambahkan permission `dashboard.view`.

Acceptance criteria:

- [ ] Dashboard tidak memakai table khusus.
- [ ] Dashboard memakai eager loading atau select yang efisien.
- [ ] Semua endpoint dashboard hanya read-only.

## Task Penyelarasan Struktur API

Route aktual saat ini:

- `POST /api/v1/login`
- `GET /api/v1/me`
- `POST /api/v1/logout`
- `GET /api/v1/inventory/units`
- `GET /api/v1/inventory/categories`
- `GET /api/v1/inventory/warehouses`
- Admin user/role/permission berada di `/api/v1/admin/...`

Route di PRD:

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/user/profile`
- Inventory master berada di `/api/v1/inventory/...`

Task:

- [ ] Putuskan route final untuk auth.
- [ ] Jika route lama dipertahankan, update PRD dan API documentation.
- [ ] Jika route PRD dipakai, tambahkan alias route agar backward compatible.
- [ ] Pastikan API documentation menulis prefix lengkap `/api/v1`.
- [ ] Samakan bahasa response message.
- [ ] Samakan status code untuk delete gagal karena data dipakai, disarankan `422` atau `409`, bukan `500`.

## Task Dokumentasi

- [ ] Update `API_DOCUMENTATION.md` untuk auth route final.
- [ ] Tambahkan dokumentasi Unit.
- [ ] Tambahkan dokumentasi Category.
- [ ] Update dokumentasi Warehouse.
- [ ] Tambahkan dokumentasi Product setelah dibuat.
- [x] Tambahkan dokumentasi Stock setelah dibuat.
- [x] Tambahkan dokumentasi Stock Mutation setelah dibuat.
- [ ] Tambahkan dokumentasi BOM setelah dibuat.
- [ ] Tambahkan dokumentasi Production Order setelah dibuat.
- [ ] Tambahkan dokumentasi Dashboard setelah dibuat.
- [ ] Buat contoh payload dan response untuk setiap endpoint utama.

## Task Testing

Prioritas minimal:

- [ ] Feature test auth login berhasil.
- [ ] Feature test auth login gagal.
- [ ] Feature test logout.
- [ ] Feature test current user.
- [ ] Feature test CRUD Unit.
- [ ] Feature test CRUD Category.
- [ ] Feature test CRUD Warehouse.
- [ ] Feature test CRUD Product.
- [x] Feature test stock mutation in.
- [x] Feature test stock mutation out.
- [x] Feature test stock mutation transfer.
- [x] Feature test negative stock ditolak.
- [ ] Feature test create BOM.
- [ ] Feature test create production order.
- [ ] Feature test execute production gagal jika stock kurang.
- [ ] Feature test execute production berhasil jika stock cukup.
- [ ] Feature test dashboard summary.

## Urutan Implementasi yang Disarankan

### Phase 0 - Rapikan Fondasi

- [ ] Sinkronkan route auth dengan PRD.
- [ ] Rapikan response error delete data master yang sudah dipakai.
- [ ] Update permission seeder untuk modul berikutnya.
- [ ] Tambahkan test untuk Unit, Category, Warehouse.

### Phase 1 - Master Data

- [x] Implementasi Master Product.
- [x] Tambahkan seeder Product.
- [x] Update API documentation Product.
- [x] Tambahkan feature test Product.

### Phase 2 - Inventory Core

- [x] Implementasi Stock.
- [x] Implementasi Stock Mutation.
- [x] Implementasi StockService.
- [x] Implementasi barcode / scan endpoint.
- [x] Tambahkan feature test stock dan mutation.

### Phase 3 - Production Core

- [ ] Implementasi BOM.
- [ ] Implementasi Production Order.
- [ ] Implementasi ProductionService.
- [ ] Tambahkan feature test production flow.

### Phase 4 - Dashboard

- [ ] Implementasi dashboard summary.
- [ ] Implementasi low stock endpoint.
- [ ] Implementasi production chart endpoint.
- [ ] Implementasi recent mutation endpoint.
- [ ] Tambahkan feature test dashboard.

### Phase 5 - Finalisasi Backend API

- [ ] Audit semua endpoint agar memakai Form Request.
- [ ] Audit semua response agar konsisten.
- [ ] Audit N+1 query dan tambahkan eager loading.
- [ ] Tambahkan index database yang dibutuhkan.
- [ ] Update `API_DOCUMENTATION.md`.
- [ ] Jalankan full test suite.

## Open Questions yang Perlu Diputuskan

- [ ] Apakah route auth final mengikuti PRD atau route saat ini?
- [ ] Apakah delete data master memakai hard delete, soft delete, atau blocked delete?
- [ ] Apakah negative stock benar-benar tidak diizinkan?
- [ ] Apakah stock adjustment boleh menaikkan dan menurunkan stok?
- [ ] Apakah product boleh punya lebih dari satu barcode?
- [ ] Apakah material produksi bisa diambil dari beberapa gudang untuk material yang sama?
- [ ] Apakah production order butuh status `in_progress` sebelum execute?
- [ ] Apakah role `viewer` perlu dibuat sekarang?
- [ ] Apakah response message harus bahasa Indonesia atau bahasa Inggris?
