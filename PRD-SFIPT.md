# PRD.md — Smart Factory Inventory & Production Tracking (S-FIPT)

## 1. Ringkasan Produk

**Smart Factory Inventory & Production Tracking (S-FIPT)** adalah sistem inventory dan produksi untuk membantu pabrik mencatat master data, memantau stok, melakukan mutasi barang, mengelola Bill of Materials (BOM), menjalankan production order, serta menyediakan dashboard operasional.

Sistem menggunakan:

- **Backend API:** Laravel REST API
- **Frontend Web:** Vue
- **Authentication:** Laravel Sanctum
- **Database:** MySQL / PostgreSQL
- **Target Pengguna:** Admin, Operator Gudang, Production Manager

Saat ini development sudah sampai pada modul **Master Unit/Satuan**. Dokumen ini melanjutkan perencanaan fitur dan API setelah modul tersebut.

---

## 2. Tujuan Produk

Tujuan utama S-FIPT:

1. Membuat pencatatan master data pabrik lebih rapi dan terstruktur.
2. Menyediakan API Laravel yang konsisten untuk digunakan oleh Vue.
3. Memastikan stok barang selalu tercatat secara real-time.
4. Mendukung mutasi barang masuk, keluar, dan transfer antar gudang.
5. Mengelola proses produksi dari BOM sampai production order.
6. Menyediakan dashboard untuk monitoring stok, low stock, dan aktivitas produksi.

---

## 3. Ruang Lingkup

### 3.1 In Scope

Fitur yang masuk scope:

- Authentication dan role-based access control.
- Master data:
  - Unit / Satuan
  - Category
  - Warehouse
  - Product
- Inventory:
  - Current stock
  - Stock mutation
  - Stock history
  - Scan barcode / SKU
- Production:
  - Bill of Materials
  - BOM Items
  - Production Orders
  - Execute production
- Dashboard:
  - Summary inventory
  - Low stock alert
  - Production chart
- API untuk Vue frontend.
- Validasi request menggunakan Laravel Form Request.
- Response konsisten menggunakan API Resource.

### 3.2 Out of Scope

Fitur yang belum masuk tahap awal:

- Integrasi mesin produksi secara real-time / IoT.
- Accounting / finance.
- Purchase order dari supplier.
- Sales order ke customer.
- Multi company / multi tenant.
- Mobile Android production-ready.
- Export Excel/PDF tahap awal.
- Approval workflow kompleks.

---

## 4. User Roles

| Role | Deskripsi | Akses Utama |
|---|---|---|
| Admin | Mengelola seluruh data dan konfigurasi sistem | Semua modul |
| Warehouse Operator | Mengelola stok dan mutasi barang | Inventory, scan, stock mutation |
| Production Manager | Mengelola BOM dan production order | Production, BOM, dashboard |
| Viewer / Manager | Melihat laporan dan dashboard | Dashboard, report read-only |

---

## 5. Tech Stack

### Backend

- Laravel
- Laravel Sanctum
- Laravel Form Request
- Laravel API Resource
- Eloquent ORM
- Database Transaction
- RESTful API
- Route prefix: `/api/v1`

### Frontend

- Vue 3
- Vue Router
- Pinia
- Axios
- Component-based structure
- Dashboard chart library, misalnya Chart.js / ApexCharts

---

## 6. API Response Standard

Semua response API harus konsisten.

### 6.1 Success Response

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {}
}
```

### 6.2 Paginated Response

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "last_page": 10
  }
}
```

### 6.3 Error Response

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "name": ["Field name wajib diisi"]
  }
}
```

---

## 7. Current Progress

Development saat ini sudah sampai pada:

- Master Unit / Satuan

Modul berikutnya yang perlu dibuat:

1. Master Category
2. Master Warehouse
3. Master Product
4. Inventory Stock
5. Stock Mutation
6. BOM
7. Production Order
8. Dashboard

---

# 8. Feature Requirement

---

## 8.1 Authentication & Authorization

### Objective

User dapat login, logout, melihat profil, dan mengakses fitur berdasarkan role.

### Functional Requirements

- User login menggunakan email dan password.
- Setelah login, sistem mengembalikan token Sanctum.
- Token digunakan untuk mengakses API private.
- User dapat logout.
- User dapat melihat profil sendiri.
- Role user digunakan untuk membatasi akses API tertentu.

### API Endpoints

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/v1/auth/login` | Login user | No |
| POST | `/api/v1/auth/logout` | Logout user | Yes |
| GET | `/api/v1/user/profile` | Get current user profile | Yes |

### Login Payload

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

### Login Response

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "plain_text_token",
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@example.com",
      "role": "admin"
    }
  }
}
```

---

## 8.2 Master Unit / Satuan

### Status

Sudah dibuat / sedang dibuat.

### Objective

Mengelola satuan barang seperti pcs, kg, liter, meter, box, dan lain-lain.

### Functional Requirements

- Admin dapat melihat list unit.
- Admin dapat menambahkan unit.
- Admin dapat mengubah unit.
- Admin dapat menghapus unit jika belum digunakan produk.
- Unit memiliki kode unik.

### Suggested Table: `units`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| name | varchar | Nama satuan, contoh: Kilogram |
| code | varchar | Kode satuan, contoh: KG |
| description | text nullable | Deskripsi |
| is_active | boolean | Status aktif |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/units` | List unit |
| POST | `/api/v1/units` | Create unit |
| GET | `/api/v1/units/{id}` | Detail unit |
| PUT | `/api/v1/units/{id}` | Update unit |
| DELETE | `/api/v1/units/{id}` | Delete unit |

---

## 8.3 Master Category

### Objective

Mengelola kategori produk, misalnya bahan baku, bahan jadi, sparepart, packaging, atau kategori lain sesuai kebutuhan pabrik.

### Functional Requirements

- Admin dapat melihat semua category.
- Admin dapat menambah category.
- Admin dapat mengubah category.
- Admin dapat menghapus category jika belum digunakan produk.
- Category dapat memiliki status aktif/nonaktif.

### Suggested Table: `categories`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| name | varchar | Nama kategori |
| code | varchar unique | Kode kategori |
| description | text nullable | Deskripsi |
| is_active | boolean | Status aktif |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/categories` | List category |
| POST | `/api/v1/categories` | Create category |
| GET | `/api/v1/categories/{id}` | Detail category |
| PUT | `/api/v1/categories/{id}` | Update category |
| DELETE | `/api/v1/categories/{id}` | Delete category |

### Query Parameters

| Parameter | Example | Description |
|---|---|---|
| search | `?search=kayu` | Search by name/code |
| is_active | `?is_active=1` | Filter active/inactive |
| per_page | `?per_page=10` | Pagination |

---

## 8.4 Master Warehouse

### Objective

Mengelola daftar gudang yang digunakan untuk menyimpan bahan baku, barang WIP, dan barang jadi.

### Functional Requirements

- Admin dapat membuat data gudang.
- Admin dapat mengubah data gudang.
- Admin dapat menonaktifkan gudang.
- Warehouse digunakan sebagai lokasi stok.
- Warehouse tidak boleh dihapus jika sudah memiliki transaksi stok.

### Suggested Table: `warehouses`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| name | varchar | Nama gudang |
| code | varchar unique | Kode gudang |
| location | varchar nullable | Lokasi gudang |
| type | enum | `raw`, `wip`, `finished`, `general` |
| is_active | boolean | Status aktif |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/warehouses` | List warehouse |
| POST | `/api/v1/warehouses` | Create warehouse |
| GET | `/api/v1/warehouses/{id}` | Detail warehouse |
| PUT | `/api/v1/warehouses/{id}` | Update warehouse |
| DELETE | `/api/v1/warehouses/{id}` | Delete warehouse |

---

## 8.5 Master Product

### Objective

Mengelola data produk, baik bahan baku maupun barang jadi.

### Functional Requirements

- Admin dapat membuat produk.
- Produk memiliki SKU unik.
- Produk memiliki barcode opsional.
- Produk memiliki category.
- Produk memiliki unit.
- Produk memiliki tipe:
  - `raw_material`
  - `finished_good`
  - `semi_finished`
  - `packaging`
- Produk memiliki minimum stock untuk kebutuhan low stock alert.
- Produk tidak boleh dihapus jika sudah memiliki stock mutation.

### Suggested Table: `products`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| sku | varchar unique | SKU produk |
| barcode | varchar nullable unique | Barcode produk |
| name | varchar | Nama produk |
| category_id | foreignId | Relasi ke categories |
| unit_id | foreignId | Relasi ke units |
| type | enum | `raw_material`, `finished_good`, `semi_finished`, `packaging` |
| min_stock | decimal | Batas minimum stok |
| description | text nullable | Deskripsi |
| is_active | boolean | Status aktif |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/products` | List product |
| POST | `/api/v1/products` | Create product |
| GET | `/api/v1/products/{id}` | Detail product |
| PUT | `/api/v1/products/{id}` | Update product |
| DELETE | `/api/v1/products/{id}` | Delete product |
| GET | `/api/v1/products/barcode/{barcode}` | Get product by barcode |

### Query Parameters

| Parameter | Example | Description |
|---|---|---|
| search | `?search=kayu` | Search name, SKU, barcode |
| type | `?type=raw_material` | Filter product type |
| category_id | `?category_id=1` | Filter category |
| is_active | `?is_active=1` | Filter status |

---

## 8.6 Inventory Stock

### Objective

Menampilkan saldo stok saat ini berdasarkan produk dan gudang.

### Functional Requirements

- Sistem menyimpan current stock per product dan warehouse.
- Stock tidak diinput langsung oleh user.
- Stock berubah karena stock mutation atau production execution.
- Kombinasi product dan warehouse harus unik.
- User dapat melihat stok berdasarkan warehouse, product, dan status low stock.

### Suggested Table: `stocks`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| product_id | foreignId | Relasi ke products |
| warehouse_id | foreignId | Relasi ke warehouses |
| qty | decimal | Current stock |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### Constraint

- Unique index: `product_id + warehouse_id`

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/inventory/stocks` | List current stock |
| GET | `/api/v1/inventory/stocks/{id}` | Detail stock |
| GET | `/api/v1/inventory/scan/{barcode}` | Scan product stock by barcode |

### Query Parameters

| Parameter | Example | Description |
|---|---|---|
| product_id | `?product_id=1` | Filter product |
| warehouse_id | `?warehouse_id=1` | Filter warehouse |
| low_stock | `?low_stock=1` | Filter stock below min_stock |
| search | `?search=kayu` | Search product name/SKU |

---

## 8.7 Stock Mutation

### Objective

Mencatat seluruh pergerakan stok, baik barang masuk, barang keluar, maupun transfer antar gudang.

### Functional Requirements

- Semua mutation harus menggunakan `DB::transaction()`.
- Setiap mutation mencatat user pembuat transaksi.
- Mutation tidak boleh membuat stok menjadi minus, kecuali sistem memang mengizinkan negative stock.
- Mutation type:
  - `in`
  - `out`
  - `transfer`
  - `production_in`
  - `production_out`
  - `adjustment`
- Sistem otomatis update table `stocks`.
- Semua mutation menjadi audit trail dan tidak disarankan untuk dihapus.

### Suggested Table: `stock_mutations`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| mutation_number | varchar unique | Nomor transaksi |
| product_id | foreignId | Relasi ke products |
| type | enum | Jenis mutasi |
| from_warehouse_id | foreignId nullable | Gudang asal |
| to_warehouse_id | foreignId nullable | Gudang tujuan |
| qty | decimal | Quantity |
| reference_type | varchar nullable | Sumber transaksi, contoh: production_order |
| reference_id | bigint nullable | ID transaksi sumber |
| reference_no | varchar nullable | Nomor referensi |
| notes | text nullable | Catatan |
| created_by | foreignId | User pembuat |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/inventory/mutations` | List mutation history |
| POST | `/api/v1/inventory/mutations` | Create mutation |
| GET | `/api/v1/inventory/mutations/{id}` | Detail mutation |

### Mutation Payload: Stock In

```json
{
  "product_id": 1,
  "type": "in",
  "to_warehouse_id": 1,
  "qty": 100,
  "reference_no": "INIT-001",
  "notes": "Initial stock"
}
```

### Mutation Payload: Stock Out

```json
{
  "product_id": 1,
  "type": "out",
  "from_warehouse_id": 1,
  "qty": 20,
  "reference_no": "OUT-001",
  "notes": "Barang keluar"
}
```

### Mutation Payload: Transfer

```json
{
  "product_id": 1,
  "type": "transfer",
  "from_warehouse_id": 1,
  "to_warehouse_id": 2,
  "qty": 10,
  "reference_no": "TRF-001",
  "notes": "Transfer ke gudang produksi"
}
```

### Business Rules

| Type | From Warehouse | To Warehouse |
|---|---|---|
| in | nullable | required |
| out | required | nullable |
| transfer | required | required |
| production_in | nullable | required |
| production_out | required | nullable |
| adjustment | optional | optional |

---

## 8.8 Bill of Materials — BOM

### Objective

Membuat resep produksi untuk barang jadi.

### Functional Requirements

- BOM hanya dibuat untuk produk tipe `finished_good` atau `semi_finished`.
- BOM memiliki banyak material.
- Material biasanya produk tipe `raw_material`, `semi_finished`, atau `packaging`.
- Satu produk finished good dapat memiliki satu atau lebih BOM.
- Salah satu BOM bisa ditandai sebagai default.
- BOM tidak boleh dieksekusi langsung, tetapi digunakan oleh production order.

### Suggested Table: `boms`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| product_id | foreignId | Produk hasil produksi |
| code | varchar unique | Kode BOM |
| name | varchar | Nama BOM |
| description | text nullable | Deskripsi |
| output_qty | decimal | Output standar, default 1 |
| is_default | boolean | BOM default |
| is_active | boolean | Status aktif |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### Suggested Table: `bom_items`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| bom_id | foreignId | Relasi ke boms |
| material_id | foreignId | Relasi ke products |
| qty_needed | decimal | Kebutuhan material |
| unit_id | foreignId nullable | Relasi ke units |
| notes | text nullable | Catatan |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/boms` | List BOM |
| POST | `/api/v1/boms` | Create BOM |
| GET | `/api/v1/boms/{id}` | Detail BOM |
| PUT | `/api/v1/boms/{id}` | Update BOM |
| DELETE | `/api/v1/boms/{id}` | Delete BOM |
| GET | `/api/v1/products/{product_id}/boms` | Get BOM by product |

### Create BOM Payload

```json
{
  "product_id": 10,
  "code": "BOM-FG-001",
  "name": "Resep Produk A",
  "output_qty": 1,
  "is_default": true,
  "items": [
    {
      "material_id": 1,
      "qty_needed": 2
    },
    {
      "material_id": 2,
      "qty_needed": 5
    }
  ]
}
```

---

## 8.9 Production Order

### Objective

Membuat dan menjalankan perintah produksi berdasarkan BOM.

### Functional Requirements

- Production order dibuat untuk produk hasil produksi.
- Production order menggunakan BOM tertentu.
- Sistem menghitung kebutuhan material berdasarkan target quantity.
- Production order memiliki status:
  - `planned`
  - `in_progress`
  - `completed`
  - `cancelled`
- Production order dapat dieksekusi jika stok material cukup.
- Saat dieksekusi:
  - Stok bahan baku berkurang.
  - Stok barang jadi bertambah.
  - Stock mutation tercatat otomatis.
- Proses execute wajib menggunakan `DB::transaction()`.

### Suggested Table: `production_orders`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| order_number | varchar unique | Nomor production order |
| product_id | foreignId | Produk yang dibuat |
| bom_id | foreignId | BOM yang dipakai |
| warehouse_output_id | foreignId | Gudang hasil produksi |
| target_qty | decimal | Target produksi |
| actual_qty | decimal nullable | Hasil aktual |
| status | enum | `planned`, `in_progress`, `completed`, `cancelled` |
| notes | text nullable | Catatan |
| created_by | foreignId | User pembuat |
| started_at | timestamp nullable | Waktu mulai |
| completed_at | timestamp nullable | Waktu selesai |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### Suggested Table: `production_order_materials`

| Field | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| production_order_id | foreignId | Relasi ke production_orders |
| material_id | foreignId | Material yang digunakan |
| warehouse_id | foreignId | Gudang material |
| required_qty | decimal | Kebutuhan material |
| consumed_qty | decimal nullable | Quantity aktual digunakan |
| created_at | timestamp | Laravel timestamp |
| updated_at | timestamp | Laravel timestamp |

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/production-orders` | List production order |
| POST | `/api/v1/production-orders` | Create production order |
| GET | `/api/v1/production-orders/{id}` | Detail production order |
| PUT | `/api/v1/production-orders/{id}` | Update production order |
| PUT | `/api/v1/production-orders/{id}/status` | Update status |
| POST | `/api/v1/production-orders/{id}/execute` | Execute production |
| POST | `/api/v1/production-orders/{id}/cancel` | Cancel production order |

### Create Production Order Payload

```json
{
  "product_id": 10,
  "bom_id": 1,
  "warehouse_output_id": 3,
  "target_qty": 100,
  "materials": [
    {
      "material_id": 1,
      "warehouse_id": 1
    },
    {
      "material_id": 2,
      "warehouse_id": 1
    }
  ],
  "notes": "Produksi batch pertama"
}
```

### Execute Production Payload

```json
{
  "actual_qty": 98,
  "notes": "2 pcs reject"
}
```

### Execute Production Business Flow

1. Validate production order exists.
2. Validate status is `planned` or `in_progress`.
3. Calculate required material quantity:
   - `required_qty = bom_item.qty_needed * target_qty / bom.output_qty`
4. Check stock availability for each material.
5. Start database transaction.
6. Create stock mutation `production_out` for each material.
7. Decrease material stock.
8. Create stock mutation `production_in` for finished good.
9. Increase finished good stock.
10. Update production order status to `completed`.
11. Commit transaction.
12. Return production result.

---

## 8.10 Dashboard

### Objective

Menampilkan ringkasan data inventory dan produksi untuk kebutuhan monitoring.

### Functional Requirements

- Menampilkan total product.
- Menampilkan total warehouse.
- Menampilkan total stock value atau total qty.
- Menampilkan low stock product.
- Menampilkan production order aktif.
- Menampilkan chart produksi mingguan / bulanan.
- Data dashboard diambil dari query aggregation, bukan tabel khusus.

### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/dashboard/summary` | Dashboard summary |
| GET | `/api/v1/dashboard/low-stock` | Low stock product |
| GET | `/api/v1/dashboard/production-chart` | Production chart |
| GET | `/api/v1/dashboard/recent-mutations` | Recent stock mutation |

### Dashboard Summary Response

```json
{
  "success": true,
  "message": "Dashboard summary berhasil diambil",
  "data": {
    "total_products": 120,
    "total_warehouses": 4,
    "low_stock_count": 8,
    "active_production_orders": 3,
    "completed_production_orders": 20
  }
}
```

---

# 9. Vue Page Requirement

## 9.1 Suggested Vue Pages

| Page | Route | Description |
|---|---|---|
| Login | `/login` | Login page |
| Dashboard | `/dashboard` | Main dashboard |
| Units | `/master/units` | Unit management |
| Categories | `/master/categories` | Category management |
| Warehouses | `/master/warehouses` | Warehouse management |
| Products | `/master/products` | Product management |
| Stocks | `/inventory/stocks` | Current stock |
| Mutations | `/inventory/mutations` | Stock mutation history |
| Create Mutation | `/inventory/mutations/create` | Create stock mutation |
| BOM | `/production/boms` | BOM management |
| BOM Form | `/production/boms/create` | Create/edit BOM |
| Production Orders | `/production/orders` | Production order list |
| Production Order Detail | `/production/orders/:id` | Production order detail |

## 9.2 Suggested Vue Folder Structure

```txt
src/
├── assets/
├── components/
│   ├── common/
│   ├── form/
│   └── layout/
├── layouts/
│   ├── AuthLayout.vue
│   └── DashboardLayout.vue
├── router/
│   └── index.ts
├── stores/
│   ├── authStore.ts
│   ├── unitStore.ts
│   ├── categoryStore.ts
│   ├── warehouseStore.ts
│   ├── productStore.ts
│   └── inventoryStore.ts
├── services/
│   ├── api.ts
│   ├── authService.ts
│   ├── unitService.ts
│   ├── categoryService.ts
│   ├── warehouseService.ts
│   ├── productService.ts
│   ├── inventoryService.ts
│   └── productionService.ts
├── types/
│   ├── unit.ts
│   ├── category.ts
│   ├── warehouse.ts
│   ├── product.ts
│   ├── stock.ts
│   └── production.ts
├── views/
│   ├── auth/
│   ├── dashboard/
│   ├── master/
│   ├── inventory/
│   └── production/
└── main.ts
```

---

# 10. Laravel Development Guideline

## 10.1 Suggested Laravel Structure

```txt
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/
│   │       ├── AuthController.php
│   │       ├── UnitController.php
│   │       ├── CategoryController.php
│   │       ├── WarehouseController.php
│   │       ├── ProductController.php
│   │       ├── StockController.php
│   │       ├── StockMutationController.php
│   │       ├── BomController.php
│   │       ├── ProductionOrderController.php
│   │       └── DashboardController.php
│   ├── Requests/
│   │   ├── Unit/
│   │   ├── Category/
│   │   ├── Warehouse/
│   │   ├── Product/
│   │   ├── StockMutation/
│   │   ├── Bom/
│   │   └── ProductionOrder/
│   └── Resources/
│       ├── UnitResource.php
│       ├── CategoryResource.php
│       ├── WarehouseResource.php
│       ├── ProductResource.php
│       ├── StockResource.php
│       ├── StockMutationResource.php
│       ├── BomResource.php
│       └── ProductionOrderResource.php
├── Models/
├── Services/
│   ├── StockService.php
│   └── ProductionService.php
└── Actions/
```

## 10.2 Service Layer Recommendation

Business logic berat sebaiknya tidak diletakkan di controller.

Gunakan service:

- `StockService`
  - handle stock in
  - handle stock out
  - handle stock transfer
  - update current stock
  - create stock mutation
- `ProductionService`
  - calculate material requirements
  - validate material stock
  - execute production
  - create production stock mutations

---

# 11. Database Relationship Summary

```txt
users
  └── stock_mutations.created_by
  └── production_orders.created_by

units
  └── products.unit_id

categories
  └── products.category_id

warehouses
  └── stocks.warehouse_id
  └── stock_mutations.from_warehouse_id
  └── stock_mutations.to_warehouse_id
  └── production_orders.warehouse_output_id
  └── production_order_materials.warehouse_id

products
  └── stocks.product_id
  └── stock_mutations.product_id
  └── boms.product_id
  └── bom_items.material_id
  └── production_orders.product_id
  └── production_order_materials.material_id

boms
  └── bom_items.bom_id
  └── production_orders.bom_id

production_orders
  └── production_order_materials.production_order_id
```

---

# 12. API Development Priority

Karena saat ini sudah sampai Unit, urutan development berikutnya disarankan seperti ini:

## Phase 1 — Master Data

1. Finish Unit API
2. Category API
3. Warehouse API
4. Product API

## Phase 2 — Inventory Core

5. Stock list API
6. Stock mutation API
7. Barcode / SKU scan API
8. Inventory history API

## Phase 3 — Production Core

9. BOM API
10. BOM Item API
11. Production Order API
12. Execute Production API

## Phase 4 — Dashboard

13. Dashboard summary API
14. Low stock API
15. Production chart API
16. Recent mutation API

---

# 13. Acceptance Criteria

## Master Category

- Admin dapat CRUD category.
- Category code unik.
- Category tidak dapat dihapus jika sudah digunakan product.
- API mendukung search dan pagination.

## Master Warehouse

- Admin dapat CRUD warehouse.
- Warehouse code unik.
- Warehouse memiliki type.
- Warehouse tidak dapat dihapus jika sudah digunakan stock atau mutation.

## Master Product

- Admin dapat CRUD product.
- Product memiliki SKU unik.
- Product terhubung ke category dan unit.
- Product memiliki type.
- Product dapat dicari berdasarkan name, SKU, atau barcode.

## Inventory Stock

- Stock otomatis terbentuk saat mutation pertama.
- Stock tampil berdasarkan warehouse dan product.
- Stock tidak boleh minus.
- Low stock dapat ditampilkan.

## Stock Mutation

- Mutation in menambah stock.
- Mutation out mengurangi stock.
- Mutation transfer mengurangi stock gudang asal dan menambah stock gudang tujuan.
- Semua proses mutation menggunakan database transaction.
- Semua mutation tercatat sebagai history.

## BOM

- BOM dapat dibuat untuk finished good.
- BOM memiliki minimal satu material.
- Material quantity wajib lebih dari 0.
- BOM dapat diupdate bersama detail item-nya.

## Production Order

- Production order dapat dibuat dari BOM.
- Sistem dapat menghitung kebutuhan material.
- Execute production gagal jika stok material tidak cukup.
- Execute production berhasil jika stok cukup.
- Setelah execute:
  - material stock berkurang
  - finished good stock bertambah
  - stock mutation tercatat
  - status menjadi completed

## Dashboard

- Summary menampilkan total data utama.
- Low stock menampilkan product dengan stock di bawah min stock.
- Production chart menampilkan data produksi berdasarkan periode.

---

# 14. Risks & Considerations

| Risk | Impact | Mitigation |
|---|---|---|
| Stock tidak sinkron | High | Gunakan DB transaction dan lock row saat mutation |
| Logic produksi terlalu berat di controller | Medium | Gunakan service layer |
| Data master dihapus padahal sudah dipakai | High | Gunakan soft delete atau validasi sebelum delete |
| API response tidak konsisten | Medium | Gunakan API Resource dan response helper |
| Vue kesulitan consume API | Medium | Standarkan response structure |
| Barcode tidak unik | Medium | Validasi unique barcode/SKU |

---

# 15. Recommended Laravel Route Structure

```php
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/user/profile', [AuthController::class, 'profile']);

        Route::apiResource('units', UnitController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('products', ProductController::class);

        Route::get('/products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);

        Route::get('/inventory/stocks', [StockController::class, 'index']);
        Route::get('/inventory/stocks/{id}', [StockController::class, 'show']);
        Route::get('/inventory/scan/{barcode}', [StockController::class, 'scan']);

        Route::get('/inventory/mutations', [StockMutationController::class, 'index']);
        Route::post('/inventory/mutations', [StockMutationController::class, 'store']);
        Route::get('/inventory/mutations/{id}', [StockMutationController::class, 'show']);

        Route::apiResource('boms', BomController::class);
        Route::get('/products/{product_id}/boms', [BomController::class, 'getByProduct']);

        Route::apiResource('production-orders', ProductionOrderController::class);
        Route::put('/production-orders/{id}/status', [ProductionOrderController::class, 'updateStatus']);
        Route::post('/production-orders/{id}/execute', [ProductionOrderController::class, 'execute']);
        Route::post('/production-orders/{id}/cancel', [ProductionOrderController::class, 'cancel']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/dashboard/low-stock', [DashboardController::class, 'lowStock']);
        Route::get('/dashboard/production-chart', [DashboardController::class, 'productionChart']);
        Route::get('/dashboard/recent-mutations', [DashboardController::class, 'recentMutations']);
    });
});
```

---

# 16. Non-Functional Requirements

## Security

- API private wajib menggunakan Sanctum.
- Password harus di-hash.
- Role middleware digunakan untuk membatasi akses.
- Validasi semua input menggunakan Form Request.
- Jangan expose stack trace di production.
- Gunakan rate limit untuk login.

## Performance

- Pagination wajib untuk list besar.
- Tambahkan index pada field:
  - `sku`
  - `barcode`
  - `product_id`
  - `warehouse_id`
  - `mutation_number`
  - `order_number`
- Gunakan eager loading untuk mencegah N+1 query.
- Dashboard menggunakan query aggregation.

## Maintainability

- Controller hanya menerima request dan mengembalikan response.
- Business logic masuk ke Service.
- Gunakan API Resource untuk response.
- Gunakan naming yang konsisten.
- Gunakan migration dan foreign key constraint dengan hati-hati.

---

# 17. Open Questions

Pertanyaan yang perlu diputuskan sebelum lanjut coding:

1. Apakah product boleh memiliki lebih dari satu barcode?
2. Apakah sistem mengizinkan negative stock?
3. Apakah produk bisa memiliki harga atau nilai persediaan?
4. Apakah stock mutation bisa dibatalkan?
5. Apakah production order butuh approval?
6. Apakah material production harus selalu dari satu gudang atau bisa multi-gudang?
7. Apakah product finished good bisa menjadi material untuk product lain?
8. Apakah delete data master menggunakan soft delete?
9. Apakah ada kebutuhan export Excel/PDF?
10. Apakah role cukup sederhana atau perlu permission detail?

---

# 18. Definition of Done

Sebuah modul dianggap selesai jika:

- Migration sudah dibuat.
- Model dan relationship sudah dibuat.
- Form Request validation sudah dibuat.
- Controller API sudah dibuat.
- API Resource sudah dibuat.
- Endpoint terdaftar di `routes/api.php`.
- Unit/manual test via Postman berhasil.
- Response API konsisten.
- Error validation jelas.
- Vue dapat consume API tanpa transformasi berlebihan.
- Dokumentasi endpoint sudah ditambahkan ke Postman collection.

---

# 19. Next Development Task

Prioritas task setelah Unit:

1. Buat migration, model, request, resource, dan controller untuk `categories`.
2. Buat migration, model, request, resource, dan controller untuk `warehouses`.
3. Buat migration, model, request, resource, dan controller untuk `products`.
4. Buat API list stock.
5. Buat service untuk stock mutation.
6. Buat endpoint stock mutation.
7. Buat BOM.
8. Buat production order.
9. Buat dashboard summary.
