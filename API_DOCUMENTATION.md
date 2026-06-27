# SFIPT API Documentation

## Base URL

All endpoints below are relative to the `/api/v1` prefix.

Example:

```http
http://localhost/api/v1
http://localhost:8000/api/v1
```

## Authentication

Endpoints under the `auth:sanctum` middleware require an `Authorization` header containing a valid Bearer token.

```http
Authorization: Bearer {token}
```

## Response Format

### Success

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {}
}
```

### Paginated

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

### Error

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required."]
    }
}
```

---

## 1. Authentication

### Login

Authenticate a user and return a Sanctum token.

- **Method**: `POST`
- **Endpoint**: `/login`
- **Auth**: No
- **Body (JSON)**:

```json
{
    "email": "admin@gmail.com",
    "password": "password"
}
```

- **Response**:
    - `200 OK`: Returns user details and token.
    - `401 Unauthorized`: Invalid credentials.
    - `422 Unprocessable Entity`: Validation errors.

### Get Current User

Retrieve the authenticated user's profile, roles, and permissions.

- **Method**: `GET`
- **Endpoint**: `/me`
- **Auth**: Yes
- **Response**: `200 OK`

### Logout

Revoke the current user's access token.

- **Method**: `POST`
- **Endpoint**: `/logout`
- **Auth**: Yes
- **Response**: `200 OK`

---

## 2. Admin - Users Management

> These endpoints require the corresponding `users.*` permissions.

| Method | Endpoint              | Permission     | Description     |
| ------ | --------------------- | -------------- | --------------- |
| GET    | `/admin/users`        | `users.view`   | List users      |
| POST   | `/admin/users`        | `users.create` | Create user     |
| GET    | `/admin/users/{user}` | `users.view`   | Get user detail |
| PUT    | `/admin/users/{user}` | `users.update` | Update user     |
| DELETE | `/admin/users/{user}` | `users.delete` | Delete user     |

### Create User Body

```json
{
    "name": "Operator Gudang",
    "email": "operator@example.com",
    "password": "password",
    "roles": ["warehouse_operator"],
    "permissions": ["products.view"]
}
```

Notes:

- `role` can be used for assigning a single role.
- `roles` can be used for assigning multiple roles.
- `permissions` can be used for assigning direct permissions.

---

## 3. Admin - Roles Management

> These endpoints require the corresponding `roles.*` permissions.

| Method | Endpoint              | Permission     | Description     |
| ------ | --------------------- | -------------- | --------------- |
| GET    | `/admin/roles`        | `roles.view`   | List roles      |
| POST   | `/admin/roles`        | `roles.create` | Create role     |
| GET    | `/admin/roles/{role}` | `roles.view`   | Get role detail |
| PUT    | `/admin/roles/{role}` | `roles.update` | Update role     |
| DELETE | `/admin/roles/{role}` | `roles.delete` | Delete role     |

### Create Role Body

```json
{
    "name": "viewer",
    "permissions": ["products.view", "units.view"]
}
```

---

## 4. Admin - Permissions Management

> These endpoints require the corresponding `permissions.*` permissions.

| Method | Endpoint                          | Permission           | Description       |
| ------ | --------------------------------- | -------------------- | ----------------- |
| GET    | `/admin/permissions`              | `permissions.view`   | List permissions  |
| POST   | `/admin/permissions`              | `permissions.create` | Create permission |
| DELETE | `/admin/permissions/{permission}` | `permissions.delete` | Delete permission |

### Create Permission Body

```json
{
    "name": "products.view"
}
```

---

## 5. Inventory - Units

> These endpoints require the corresponding `units.*` permissions.

| Method | Endpoint                  | Permission     | Description     |
| ------ | ------------------------- | -------------- | --------------- |
| GET    | `/inventory/units`        | `units.view`   | List units      |
| POST   | `/inventory/units`        | `units.create` | Create unit     |
| GET    | `/inventory/units/{unit}` | `units.view`   | Get unit detail |
| PUT    | `/inventory/units/{unit}` | `units.update` | Update unit     |
| DELETE | `/inventory/units/{unit}` | `units.delete` | Delete unit     |

### List Units Query Parameters

| Parameter   | Example          | Description                                                 |
| ----------- | ---------------- | ----------------------------------------------------------- |
| `search`    | `?search=kg`     | Search by code, name, or description                        |
| `q`         | `?q=kg`          | Alias for search                                            |
| `per_page`  | `?per_page=10`   | Items per page, min 1, max 100                              |
| `sort`      | `?sort=code`     | Sort field: `id`, `code`, `name`, `is_active`, `created_at` |
| `direction` | `?direction=asc` | `asc` or `desc`                                             |

### Create Unit Body

```json
{
    "code": "KG",
    "name": "Kilogram",
    "description": "Satuan berat kilogram",
    "is_active": true
}
```

---

## 6. Inventory - Categories

> These endpoints require the corresponding `categories.*` permissions.

| Method | Endpoint                           | Permission          | Description         |
| ------ | ---------------------------------- | ------------------- | ------------------- |
| GET    | `/inventory/categories`            | `categories.view`   | List categories     |
| POST   | `/inventory/categories`            | `categories.create` | Create category     |
| GET    | `/inventory/categories/{category}` | `categories.view`   | Get category detail |
| PUT    | `/inventory/categories/{category}` | `categories.update` | Update category     |
| DELETE | `/inventory/categories/{category}` | `categories.delete` | Delete category     |

### List Categories Query Parameters

| Parameter   | Example          | Description                                                 |
| ----------- | ---------------- | ----------------------------------------------------------- |
| `search`    | `?search=kayu`   | Search by code, name, or description                        |
| `q`         | `?q=kayu`        | Alias for search                                            |
| `per_page`  | `?per_page=10`   | Items per page, min 1, max 100                              |
| `sort`      | `?sort=code`     | Sort field: `id`, `code`, `name`, `is_active`, `created_at` |
| `direction` | `?direction=asc` | `asc` or `desc`                                             |

### Create Category Body

```json
{
    "code": "KAYU",
    "name": "Kayu",
    "description": "Material berbahan kayu",
    "is_active": true
}
```

---

## 7. Inventory - Warehouses

> These endpoints require the corresponding `warehouses.*` permissions.

| Method | Endpoint                            | Permission          | Description          |
| ------ | ----------------------------------- | ------------------- | -------------------- |
| GET    | `/inventory/warehouses`             | `warehouses.view`   | List warehouses      |
| POST   | `/inventory/warehouses`             | `warehouses.create` | Create warehouse     |
| GET    | `/inventory/warehouses/{warehouse}` | `warehouses.view`   | Get warehouse detail |
| PUT    | `/inventory/warehouses/{warehouse}` | `warehouses.update` | Update warehouse     |
| DELETE | `/inventory/warehouses/{warehouse}` | `warehouses.delete` | Delete warehouse     |

### List Warehouses Query Parameters

| Parameter   | Example          | Description                                                                     |
| ----------- | ---------------- | ------------------------------------------------------------------------------- |
| `search`    | `?search=raw`    | Search by code, name, location, or type                                         |
| `q`         | `?q=raw`         | Alias for search                                                                |
| `per_page`  | `?per_page=10`   | Items per page, min 1, max 100                                                  |
| `sort`      | `?sort=code`     | Sort field: `id`, `code`, `name`, `location`, `type`, `is_active`, `created_at` |
| `direction` | `?direction=asc` | `asc` or `desc`                                                                 |

### Create Warehouse Body

```json
{
    "code": "WH-RAW",
    "name": "Gudang Bahan Baku",
    "location": "Area A",
    "type": "raw",
    "is_active": true
}
```

Allowed `type` values:

- `raw`
- `wip`
- `finished`
- `general`

---

## 8. Inventory - Products

> These endpoints require the corresponding `products.*` permissions.

| Method | Endpoint                                | Permission        | Description            |
| ------ | --------------------------------------- | ----------------- | ---------------------- |
| GET    | `/inventory/products`                   | `products.view`   | List products          |
| POST   | `/inventory/products`                   | `products.create` | Create product         |
| GET    | `/inventory/products/barcode/{barcode}` | `products.view`   | Get product by barcode |
| GET    | `/inventory/products/{product}`         | `products.view`   | Get product detail     |
| PUT    | `/inventory/products/{product}`         | `products.update` | Update product         |
| DELETE | `/inventory/products/{product}`         | `products.delete` | Delete product         |

Important:

- Put `/inventory/products/barcode/{barcode}` before `/inventory/products/{product}` when adding routes, so `barcode` is not interpreted as a product ID.
- Product delete is allowed only while the product is not referenced by related records. Once stock, mutation, BOM, or production references exist, product should be deactivated with `is_active = false` instead of hard deleted.

### Product Fields

| Field         | Type            | Required | Notes                                     |
| ------------- | --------------- | -------- | ----------------------------------------- |
| `sku`         | string          | Yes      | Unique, max 100, automatically uppercased |
| `barcode`     | string nullable | No       | Unique if provided, max 100               |
| `name`        | string          | Yes      | Max 255                                   |
| `category_id` | integer         | Yes      | Must exist in `categories`                |
| `unit_id`     | integer         | Yes      | Must exist in `units`                     |
| `type`        | string          | Yes      | Product type                              |
| `min_stock`   | number          | No       | Minimum 0, default 0                      |
| `description` | string nullable | No       | Product description                       |
| `is_active`   | boolean         | No       | Default true                              |

Allowed `type` values:

- `raw_material`
- `finished_good`
- `semi_finished`
- `packaging`

### List Products Query Parameters

| Parameter     | Example              | Description                                                                     |
| ------------- | -------------------- | ------------------------------------------------------------------------------- |
| `search`      | `?search=kayu`       | Search by SKU, barcode, name, or description                                    |
| `q`           | `?q=kayu`            | Alias for search                                                                |
| `type`        | `?type=raw_material` | Filter by product type                                                          |
| `category_id` | `?category_id=1`     | Filter by category                                                              |
| `unit_id`     | `?unit_id=1`         | Filter by unit                                                                  |
| `is_active`   | `?is_active=1`       | Filter by active status                                                         |
| `per_page`    | `?per_page=10`       | Items per page, min 1, max 100                                                  |
| `sort`        | `?sort=sku`          | Sort field: `id`, `sku`, `name`, `type`, `min_stock`, `is_active`, `created_at` |
| `direction`   | `?direction=asc`     | `asc` or `desc`                                                                 |

### Create Product Body

```json
{
    "sku": "RM-KAYU-001",
    "barcode": "899000000001",
    "name": "Kayu Raw Material",
    "category_id": 1,
    "unit_id": 1,
    "type": "raw_material",
    "min_stock": 10,
    "description": "Bahan baku kayu",
    "is_active": true
}
```

### Update Product Body

All fields are optional. Send only fields that need to be updated.

```json
{
    "name": "Kayu Raw Material Updated",
    "min_stock": 25,
    "is_active": true
}
```

### Product Response Example

```json
{
    "success": true,
    "message": "Product retrieved successfully",
    "data": {
        "id": 1,
        "sku": "RM-KAYU-001",
        "barcode": "899000000001",
        "name": "Kayu Raw Material",
        "category_id": 1,
        "unit_id": 1,
        "type": "raw_material",
        "min_stock": "10.0000",
        "description": "Bahan baku kayu",
        "is_active": true,
        "category": {
            "id": 1,
            "code": "KAYU",
            "name": "Kayu",
            "description": "Material berbahan kayu",
            "is_active": true,
            "created_at": "2026-05-27T00:00:00.000000Z",
            "updated_at": "2026-05-27T00:00:00.000000Z"
        },
        "unit": {
            "id": 1,
            "code": "PCS",
            "name": "Pcs",
            "description": "Satuan per item",
            "is_active": true,
            "created_at": "2026-05-27T00:00:00.000000Z",
            "updated_at": "2026-05-27T00:00:00.000000Z"
        },
        "created_at": "2026-05-27T00:00:00.000000Z",
        "updated_at": "2026-05-27T00:00:00.000000Z"
    }
}
```

### Validation Errors

Common validation cases:

- `sku` is required and must be unique.
- `barcode` must be unique when provided.
- `category_id` must reference an existing category.
- `unit_id` must reference an existing unit.
- `type` must be one of the allowed product types.
- `min_stock` must be greater than or equal to 0.

Example:

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "sku": ["The sku has already been taken."]
    }
}
```

---

## 9. Inventory - Stocks

> These endpoints require `stocks.view`. Stock is read-only and is created or updated only through stock mutations.

| Method | Endpoint                    | Permission    | Description                                         |
| ------ | --------------------------- | ------------- | --------------------------------------------------- |
| GET    | `/inventory/stocks`         | `stocks.view` | List current stocks                                 |
| GET    | `/inventory/stocks/{stock}` | `stocks.view` | Get stock detail                                    |
| GET    | `/inventory/scan/{barcode}` | `stocks.view` | Scan product barcode and return product with stocks |

### List Stocks Query Parameters

| Parameter      | Example           | Description                                                                     |
| -------------- | ----------------- | ------------------------------------------------------------------------------- |
| `search`       | `?search=RM-KAYU` | Search by product SKU, barcode, product name, warehouse code, or warehouse name |
| `q`            | `?q=RM-KAYU`      | Alias for search                                                                |
| `product_id`   | `?product_id=1`   | Filter by product                                                               |
| `warehouse_id` | `?warehouse_id=1` | Filter by warehouse                                                             |
| `low_stock`    | `?low_stock=1`    | Filter rows where `stocks.qty < products.min_stock`                             |
| `per_page`     | `?per_page=10`    | Items per page, min 1, max 100                                                  |
| `sort`         | `?sort=qty`       | Sort field: `id`, `product_id`, `warehouse_id`, `qty`, `created_at`             |
| `direction`    | `?direction=desc` | `asc` or `desc`                                                                 |

---

## 10. Inventory - Stock Mutations

> These endpoints require `mutations.view` or `mutations.create`.

| Method | Endpoint                          | Permission         | Description                            |
| ------ | --------------------------------- | ------------------ | -------------------------------------- |
| GET    | `/inventory/mutations`            | `mutations.view`   | List stock mutation audit trail        |
| POST   | `/inventory/mutations`            | `mutations.create` | Create stock mutation and update stock |
| GET    | `/inventory/mutations/{mutation}` | `mutations.view`   | Get stock mutation detail              |

There is no delete endpoint for stock mutations.

### Create Stock In Body

```json
{
    "product_id": 1,
    "type": "in",
    "to_warehouse_id": 1,
    "qty": 25,
    "reference_no": "GRN-001",
    "notes": "Initial stock"
}
```

### Create Stock Out Body

```json
{
    "product_id": 1,
    "type": "out",
    "from_warehouse_id": 1,
    "qty": 5
}
```

### Create Transfer Body

```json
{
    "product_id": 1,
    "type": "transfer",
    "from_warehouse_id": 1,
    "to_warehouse_id": 2,
    "qty": 10
}
```

Allowed `type` values for manual API input:

- `in`
- `out`
- `transfer`
- `adjustment`

Notes:

- `in` requires `to_warehouse_id`.
- `out` requires `from_warehouse_id`.
- `transfer` requires both warehouses and they must be different.
- `adjustment` uses exactly one direction: `to_warehouse_id` to add stock or `from_warehouse_id` to reduce stock.
- Mutations fail with `422 Unprocessable Entity` if stock is not enough.

---

## 11. Production - Bill of Materials (BOM)

> These endpoints require the corresponding `boms.*` permissions.

| Method | Endpoint                                | Permission    | Description                          |
| ------ | --------------------------------------- | ------------- | ------------------------------------ |
| GET    | `/production/boms`                      | `boms.view`   | List BOMs                            |
| POST   | `/production/boms`                      | `boms.create` | Create a new BOM                     |
| GET    | `/production/boms/{bom}`                | `boms.view`   | Get BOM details                      |
| PUT    | `/production/boms/{bom}`                | `boms.update` | Update BOM details and items         |
| DELETE | `/production/boms/{bom}`                | `boms.delete` | Delete BOM                           |
| GET    | `/inventory/products/{product_id}/boms` | `boms.view`   | Get BOMs list for a specific product |

### List BOMs Query Parameters

| Parameter    | Example          | Description                                                                             |
| ------------ | ---------------- | --------------------------------------------------------------------------------------- |
| `search`     | `?search=resep`  | Search by code, name, or description                                                    |
| `q`          | `?q=resep`       | Alias for search                                                                        |
| `product_id` | `?product_id=1`  | Filter by output product                                                                |
| `is_active`  | `?is_active=1`   | Filter by status (0/1)                                                                  |
| `per_page`   | `?per_page=10`   | Items per page, min 1, max 100                                                          |
| `sort`       | `?sort=code`     | Sort field: `id`, `code`, `name`, `output_qty`, `is_default`, `is_active`, `created_at` |
| `direction`  | `?direction=asc` | `asc` or `desc`                                                                         |

### Create BOM Body

```json
{
    "product_id": 10,
    "code": "BOM-FG-001",
    "name": "Resep Produk A",
    "description": "Deskripsi opsional",
    "output_qty": 1.0,
    "is_default": true,
    "is_active": true,
    "items": [
        {
            "material_id": 1,
            "qty_needed": 2.5,
            "unit_id": 2,
            "notes": "Catatan untuk material ini"
        },
        {
            "material_id": 2,
            "qty_needed": 5.0
        }
    ]
}
```

Notes:

- Output product `product_id` must be of type `finished_good` or `semi_finished`.
- Materials in `items` must be of type `raw_material`, `semi_finished`, or `packaging`.
- Setting `is_default` to `true` automatically resets other BOMs for the same product to be non-default.
