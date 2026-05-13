# SFIPT API Documentation

## Base URL
All endpoints are relative to the `/api` prefix (e.g., `http://localhost/api` or `http://localhost:8000/api`).

## Authentication
Endpoints under the `auth:sanctum` middleware require an `Authorization` header containing a valid Bearer token.
```http
Authorization: Bearer {token}
```

---

## 1. Authentication

### Login
Authenticate a user and return a token.
- **Method**: `POST`
- **Endpoint**: `/login`
- **Body (JSON)**:
  - `email` (string, required): User's email address.
  - `password` (string, required): User's password.
- **Response**:
  - `200 OK`: Returns user details and `token`.
  - `401 Unauthorized`: Invalid credentials.
  - `422 Unprocessable Entity`: Validation errors.

### Get Current User (Me)
Retrieve the authenticated user's profile and permissions.
- **Method**: `GET`
- **Endpoint**: `/me`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with user object.

### Logout
Revoke the current user's access token.
- **Method**: `POST`
- **Endpoint**: `/logout`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with success message.

---

## 2. Admin - Users Management

> **Note**: These endpoints require the corresponding `users.*` permissions (e.g., `users.view`, `users.create`).

### List Users
Retrieve a paginated list of users.
- **Method**: `GET`
- **Endpoint**: `/admin/users`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**:
  - `per_page` (integer, optional): Number of items per page. Default: 15. Min: 1, Max: 100.
- **Response**: `200 OK` with paginated user list.

### Create User
Create a new user with optional roles and permissions.
- **Method**: `POST`
- **Endpoint**: `/admin/users`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, required, max: 255): User's full name.
  - `email` (string, required, unique, max: 255): User's email address.
  - `password` (string, required, min: 8): User's password.
  - `role` (string, optional): A single role name to assign.
  - `roles` (array of strings, optional): Array of role names.
  - `permissions` (array of strings, optional): Array of permission names.
- **Response**: `201 Created` with created user details.

### Get User Details
Retrieve details of a specific user.
- **Method**: `GET`
- **Endpoint**: `/admin/users/{user_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with user details including roles and permissions.

### Update User
Update an existing user's information.
- **Method**: `PUT`
- **Endpoint**: `/admin/users/{user_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, optional, max: 255)
  - `email` (string, optional, unique, max: 255)
  - `password` (string, optional, min: 8)
  - `role` (string, optional)
  - `roles` (array of strings, optional)
  - `permissions` (array of strings, optional)
- **Response**: `200 OK` with updated user details. 
- **Errors**: Returns `422` if attempting to update your own account.

### Delete User
Delete a user from the system.
- **Method**: `DELETE`
- **Endpoint**: `/admin/users/{user_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with success message.
- **Errors**: Returns `422` if attempting to delete your own account.

---

## 3. Admin - Roles Management

> **Note**: These endpoints require the corresponding `roles.*` permissions.

### List Roles
Retrieve all available roles.
- **Method**: `GET`
- **Endpoint**: `/admin/roles`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with a list of roles ordered by name.

### Create Role
Create a new role with assigned permissions.
- **Method**: `POST`
- **Endpoint**: `/admin/roles`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, required, unique, max: 255): Name of the role.
  - `permissions` (array of strings, optional): Array of permission names to attach.
- **Response**: `201 Created` with the new role.

### Get Role Details
Retrieve a specific role and its permissions.
- **Method**: `GET`
- **Endpoint**: `/admin/roles/{role_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with the role details.

### Update Role
Modify a role's name or permissions.
- **Method**: `PUT`
- **Endpoint**: `/admin/roles/{role_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, optional, unique, max: 255)
  - `permissions` (array of strings, optional)
- **Response**: `200 OK` with the updated role.
- **Errors**: Returns `422` if attempting to rename the 'admin' role to something else.

### Delete Role
Delete an existing role.
- **Method**: `DELETE`
- **Endpoint**: `/admin/roles/{role_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK`.
- **Errors**: Returns `422` if attempting to delete the 'admin' role.

---

## 4. Admin - Permissions Management

> **Note**: These endpoints require the corresponding `permissions.*` permissions.

### List Permissions
Retrieve all available permissions.
- **Method**: `GET`
- **Endpoint**: `/admin/permissions`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with list of permissions.

### Create Permission
Create a new permission entry.
- **Method**: `POST`
- **Endpoint**: `/admin/permissions`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, required, max: 255): Permission identifier (e.g., `users.view`).
- **Response**: `201 Created` with the new permission.

### Delete Permission
Remove a permission.
- **Method**: `DELETE`
- **Endpoint**: `/admin/permissions/{permission_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK`.
- **Errors**: Returns `422` if attempting to delete protected permissions like `admin.access` or `app.access`.

---

## 5. Admin - Warehouses Management

> **Note**: These endpoints require the corresponding `warehouses.*` permissions.

### List Warehouses
Retrieve a paginated list of warehouses.
- **Method**: `GET`
- **Endpoint**: `/admin/warehouses`
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**:
  - `per_page` (integer, optional): Items per page (default: 15, max: 100).
- **Response**: `200 OK` with a paginated list.

### Create Warehouse
Add a new warehouse to the system.
- **Method**: `POST`
- **Endpoint**: `/admin/warehouses`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, required, max: 255): Name of the warehouse.
  - `location` (string, required, max: 255): Location details.
  - `type` (string, optional): Type of warehouse. Must be one of `raw`, `wip`, `finished`.
  - `is_active` (boolean, optional): Status of the warehouse.
- **Response**: `201 Created` with the new warehouse.

### Get Warehouse Details
View details of a specific warehouse.
- **Method**: `GET`
- **Endpoint**: `/admin/warehouses/{warehouse_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK` with warehouse details.

### Update Warehouse
Update details of an existing warehouse.
- **Method**: `PUT`
- **Endpoint**: `/admin/warehouses/{warehouse_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Body (JSON)**:
  - `name` (string, optional, max: 255)
  - `location` (string, optional, max: 255)
  - `type` (string, optional): Must be one of `raw`, `wip`, `finished`.
  - `is_active` (boolean, optional)
- **Response**: `200 OK` with the updated warehouse.

### Delete Warehouse
Delete a warehouse record.
- **Method**: `DELETE`
- **Endpoint**: `/admin/warehouses/{warehouse_id}`
- **Headers**: `Authorization: Bearer {token}`
- **Response**: `200 OK`.
- **Errors**: Returns `500` if the warehouse is still used by other records.
