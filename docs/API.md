# CarCarePlus API Reference

This document covers every endpoint currently registered in `routes/api.php`. It's generated from the actual controllers, Form Requests, and API Resources — not from comments — so field names and rules here should match what the backend really does.

## Conventions

**Base path:** every route below is prefixed with `/api`.

**Auth:** the API uses Laravel Sanctum bearer tokens. After `POST /api/auth/login` (or a Type-1 customer registration), you get a `token` in the response. Send it as:
```
Authorization: Bearer <token>
```
Endpoints marked "Auth: none" are public. Everything else requires a valid token (`auth:sanctum`), or you get `401 Unauthenticated`.

**Response envelope** — every response, success or error, has this shape:
```json
{
  "status": 1,
  "data": { "...or [...] or null" },
  "message": "Human-readable message",
  "status_code": 200,
  "timestamp": "2026-08-01T12:00:00+00:00"
}
```
`status: 1` = success, `status: 0` = error. Check `status` (or the HTTP status code), not just HTTP 2xx, since some error paths still return structured JSON on non-2xx codes.

**Standard error codes:**
| Code | Meaning | When |
|---|---|---|
| 401 | Unauthenticated | missing/invalid bearer token |
| 403 | Forbidden | missing `can:` permission, `active.*` middleware rejection (inactive account), or an ownership check failed in the service layer |
| 404 | Not found | route-model binding / `findOrFail` missed |
| 422 | Validation / business rule error | Form Request validation failed, or a domain rule was violated (e.g. insufficient stock, duplicate branch admin) |
| 400 | Bad request | a few endpoints use this for "you must supply X" instead of 422 — noted per-endpoint below |
| 429 | Too many requests | password-reset throttling |

**Extra middleware you'll see per-route:**
- `can:<permission>` — Spatie permission gate. "Who can call" below is derived from which roles are seeded with that permission.
- `active.user` — blocks the request with `403 "Your account is inactive."` if the authenticated user (**any** role) has `is_active = false`.
- `active.admin` — same check, but **only** applies when the caller's role is `admin`. `super_admin` is exempt.

**Roles in the system:** `super_admin`, `admin` (branch manager), `workshop`, `customer_personal`, `customer_company`, `employee_washer`, `employee_mechanic`. A user's `role` field in responses is their first Spatie role name.

**Create/update convention:** almost everywhere in this API, "update" is `POST`, not `PUT`/`PATCH`. Update Form Requests generally make every field `sometimes` (partial update) — only send the fields you want to change.

---

## Table of contents

1. [Auth](#1-auth)
2. [Cars](#2-cars)
3. [Staff Accounts & Registration Approvals](#3-staff-accounts--registration-approvals)
4. [Profile & User Lookup](#4-profile--user-lookup)
5. [Categories](#5-categories)
6. [Services](#6-services)
7. [Sub-Services](#7-sub-services)
8. [Car Types](#8-car-types)
9. [Car Brands](#9-car-brands)
10. [Pricing Rule Types](#10-pricing-rule-types)
11. [Pricing Rules](#11-pricing-rules)
12. [Packages](#12-packages)
13. [Package Services](#13-package-services)
14. [Package Service Sub-Services](#14-package-service-sub-services)
15. [Branches](#15-branches)
16. [Admins](#16-admins)
17. [Materials](#17-materials)
18. [Material Units](#18-material-units)
19. [Inventories](#19-inventories)
20. [Inventory Transactions](#20-inventory-transactions)
21. [Points](#21-points)
22. [Points Transactions](#22-points-transactions)
23. [User Packages](#23-user-packages)
24. [Points Configs](#24-points-configs)
25. [Problem Types](#25-problem-types)
26. [Suggested Problems](#26-suggested-problems)
27. [System Settings](#27-system-settings)
28. [AI Rules](#28-ai-rules)

---

## 1. Auth

### POST /api/auth/register/customer
Auth: none (public).

| Field | Type | Required | Rules |
|---|---|---|---|
| name | string | yes | max:255 |
| email | string | yes | email, max:255, unique:users,email |
| phone | string | no | max:20, unique:users,phone |
| password | string | yes | min:8, confirmed (send `password_confirmation` too) |
| is_active | bool | no | — |
| image_url | file | no | image, mimes:jpg,jpeg,png,webp, max:2048KB |

Response: `UserResource` + `token` (`id, name, email, phone, image_url, is_active, role, token`). HTTP 201.

Notes: account is **active immediately**, role `customer_personal`, token returned right away — no approval step. ⚠️ `image_url` is validated as a file but the upload is never actually stored/persisted by the backend for this endpoint — don't rely on it working yet.

### POST /api/auth/register/company
Auth: none (public).

| Field | Type | Required | Rules |
|---|---|---|---|
| name, email, phone, password, is_active, image_url | — | — | same as customer registration above |
| company_name | string | yes | max:255 |
| company_name_ar | string | yes | max:255 |
| commercial_reg | string | yes | unique:companies,commercial_reg |
| tax_number | string | yes | unique:companies,tax_number |
| company_address | string | yes | — |

Response: `UserResource`, **no token**. HTTP 201.

Notes: creates role `customer_company`, `is_active = false`, and a `Company` row with `status = pending`. This is a registration **request** — login will fail until a super admin approves it via `admin/registration-requests/companies/{company}/approve`.

### POST /api/auth/register/workshop
Auth: none (public).

| Field | Type | Required | Rules |
|---|---|---|---|
| name, email, phone, password, is_active, image_url | — | — | same base fields as above |
| workshop_name | string | yes | max:255 |
| workshop_name_ar | string | yes | max:255 |
| workshop_address | string | yes | — |
| workshop_city | string | yes | — |
| latitude | numeric | yes | — |
| longitude | numeric | yes | — |

Response: `UserResource`, no token. HTTP 201. Same pending-approval pattern as company registration (role `workshop`, approve via `admin/registration-requests/workshops/{workshop}/approve`).

### POST /api/auth/login
Auth: none (public).

| Field | Type | Required |
|---|---|---|
| email | string | yes |
| password | string | yes |

Response: `UserResource` + `token`. Fails with a generic invalid-credentials error, or an "account inactive" error if `is_active` is false (e.g. still-pending company/workshop). Updates `last_login_at`.

### POST /api/auth/logout
Auth: `auth:sanctum`. No body. Deletes only the **current** access token, not all of the user's tokens. Response: `data: null`.

### POST /api/auth/forgot-password
Auth: none. Body: `email` (required, email). Always returns a generic success message (prevents email enumeration). Sends a reset-link email. HTTP 429 if throttled.

### POST /api/auth/reset-password
Auth: none. Body: `token` (from the emailed link), `email`, `password` (min:8, confirmed). On success, revokes **all** of the user's existing tokens — frontend should send the user back to login.

### POST /api/auth/password/otp/send
Auth: none. Body: `email` (required, email). Always a generic response. Emails a one-time code.

### POST /api/auth/password/otp/reset
Auth: none. Body: `email`, `otp`, `password` (min:8, confirmed). Wrong/expired code returns one unified error (no enumeration hint). On success, revokes all tokens.

---

## 2. Cars

All under `/api/cars/*`, all require `auth:sanctum`.

### GET /api/cars/all
Auth: `can:show.cars` + `active.user`. Who: `super_admin`, `admin`.
No params. **Paginated** (10/page) collection of cars. `admin` only sees cars in branches they manage; `super_admin` sees everything.

Car object fields: `id, user_id, brand_id, car_type_id, branch_id, plate_number, model, year, color, fuel_type, cylinders, mileage, image_url, is_active, owner, car_type, branch, created_at, updated_at`. `fuel_type` ∈ `petrol | diesel | electric | hybrid`.

### GET /api/cars/indexClient/{customer_id?}
Auth: `can:show.client.cars` + `active.admin`. Who: `super_admin`, `admin`, `customer_personal`, `customer_company`.

`customer_id` (route, optional): **required** for `super_admin`/`admin` (omit → `Response::Error` "customer_id is required"); **must be omitted** for a customer — always resolves to their own id.

Response: plain (non-paginated) array of cars, filtered by owner.

### POST /api/cars/{customer_id?}
Auth: `can:add.car` + `active.user`. Multipart form.

| Field | Type | Required | Rules |
|---|---|---|---|
| customer_id | int (route, optional) | conditional | required for SA/admin, must be omitted for a customer (car created for themselves) |
| brand_id | int | yes | exists:car_brands,id |
| car_type_id | int | yes | exists:car_types,id |
| branch_id | int | yes | exists:branches,id |
| plate_number | string | yes | max:255, unique:cars,plate_number |
| model | string | yes | max:255 |
| year | int | yes | digits:4, min:1900, max:current year |
| color | string | yes | max:255 |
| fuel_type | string | yes | in: petrol, diesel, electric, hybrid |
| cylinders | int | no | min:1, max:16 |
| mileage | int | no | min:0 |
| image | file | no | image, mimes:jpg,jpeg,png,webp, max:2048KB |

Response: single car object.

### GET /api/cars/show/{id}
Auth: `can:show.car` (no `active.*`). Who: essentially every role. Response: single car object with `owner`, `car_type`, `branch` all loaded. No ownership restriction — any authorized role can view any car by id.

### POST /api/cars/update/{id}
Auth: `can:edit.car` + `active.user`. Multipart form, all fields `sometimes`/partial: `brand_id, car_type_id, branch_id (nullable), plate_number, model, year, color, fuel_type, cylinders, mileage, is_active, image`.

Business rule: only the car's own owner, or `super_admin`/`admin`, may update it — otherwise 403.

### GET /api/cars/delete/{id}
Auth: `can:delete.car` + `active.user`. **Note: this is a GET, not DELETE**, per the actual route. Who: `super_admin`, `customer_personal`, `customer_company` — **`admin` is not granted `delete.car`**, so branch admins can't delete cars here. Service-level check restricts it further to the car's owner or `super_admin`. Response: `data: []`.

---

## 3. Staff Accounts & Registration Approvals

All under `/api/admin/*`, all require `auth:sanctum`.

### POST /api/admin/employees
Auth: `can:add.staff_account`. Who: `super_admin` only.

| Field | Type | Required | Rules |
|---|---|---|---|
| name | string | yes | max:255 |
| email | string | yes | unique:users,email |
| phone | string | yes | unique:users,phone |
| password | string | yes | min:8 |
| branch_id | int | yes | exists:branches,id |
| type | string | yes | in: `washer`, `mechanic`, `admin` |
| is_active | bool | no | — |

Response: `UserResource`. HTTP 201.

Notes: `type` maps to a role (`washer→employee_washer`, `mechanic→employee_mechanic`, `admin→admin`) and creates an `Employee` record. **If `type=admin`**: the given `branch_id` must already exist, and its `admin_id` is set to this new user — **unless that branch already has a real admin** (a user holding the `admin` role), in which case you get a 422 on `branch_id`: *"This branch already has an admin assigned."* See [Branches](#15-branches) for the full bootstrap flow.

### GET /api/admin/registration-requests/companies
Auth: `can:show.registration_requests`. Who: `super_admin` only. Returns only `status = pending` companies, with `owner` loaded.

### GET /api/admin/registration-requests/workshops
Same as above, for workshops.

### POST /api/admin/registration-requests/companies/{company}/approve
Auth: `can:manage.registration_requests`. Who: `super_admin` only. Sets `Company.status = approved`, `is_active = true` on both the company and its owning user (this is what unblocks their login). Sends an approval notification.

### POST /api/admin/registration-requests/companies/{company}/reject
Auth: `can:manage.registration_requests`. Body: `reason` (string, optional, **not validated** — no length/format constraint server-side). Sets `status = rejected`, `is_active = false` on company + owner.

### POST /api/admin/registration-requests/workshops/{workshop}/approve
Same pattern as company approval, for workshops.

### POST /api/admin/registration-requests/workshops/{workshop}/reject
Same pattern as company rejection, for workshops. Body: `reason` (optional, not validated).

---

## 4. Profile & User Lookup

### GET /api/profile/showProfile
Auth: `auth:sanctum` only — no permission/active checks. Any authenticated user gets their own profile. Response: `UserResource` (`id, name, email, phone, image_url, is_active, role`).

### POST /api/profile/updateProfile
Auth: `auth:sanctum` only. Always updates the caller's own account — there's no way to target another user here.

| Field | Type | Required | Rules |
|---|---|---|---|
| name | string | no | max:255 |
| email | string | no | email, max:255, unique (ignoring own id) |
| phone | string | no | max:20, unique (ignoring own id) |
| image_url | file | no | mimes:jpg,jpeg,png,webp, max:**255KB** |

Note: no `password` field here — this endpoint can't change the password. ⚠️ Same as registration, `image_url` upload isn't actually persisted server-side yet.

### GET /api/users/{user}
Auth: `can:show.profile` + `active.user`. Who: `super_admin`, `admin`, `employee_washer`, `employee_mechanic` **only** — customers and workshop do not have `show.profile`, so this is a staff-only lookup, not a general "view any user" endpoint. Response: `UserResource` of the target user. No ownership scoping beyond the permission — any staff caller can look up any user by id.

---

## 5. Categories
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/categories | GET | show.categories | broadly available (most roles) |
| /api/categories/{id} | GET | show.categories | same |
| /api/categories | POST | manage.categories | super_admin only |
| /api/categories/{category} | POST | manage.categories | super_admin only |
| /api/categories/{category} | DELETE | manage.categories | super_admin only |

Create/update body (`name` required max:255, `name_ar` required max:255, `description` nullable string, `is_active` boolean, update fields all `sometimes`).

Response object:
```json
{ "id": 1, "name": "string", "name_ar": "string", "description": "string|null", "is_active": true, "created_at": "Y-m-d H:i:s|null", "updated_at": "Y-m-d H:i:s|null" }
```

---

## 6. Services
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/services | GET | show.services |
| /api/services/{id} | GET | show.services |
| /api/services | POST | manage.services (super_admin only) |
| /api/services/{service} | POST | manage.services |
| /api/services/{service} | DELETE | manage.services |

Create body: `category_id` (required, exists:categories,id), `name`/`name_ar` (required, max:255), `description` (nullable), `base_price` (required, numeric, min:0), `is_vip_available` (required, boolean), `vip_extra_price` (required **only if** `is_vip_available=true`, else nullable, numeric min:0), `duration_minutes` (required, integer, min:1). Update: all `sometimes`.

Response includes nested `category` (CategoryResource, always loaded on index/show):
```json
{ "id": 1, "category_id": 1, "category": {...}, "name": "...", "name_ar": "...", "description": "string|null", "base_price": 100.0, "is_vip_available": true, "vip_extra_price": 20.0, "duration_minutes": 30, "created_at": "...", "updated_at": "..." }
```

---

## 7. Sub-Services
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/sub-services | GET | show.sub_services |
| /api/sub-services/{id} | GET | show.sub_services |
| /api/sub-services | POST | manage.sub_services (super_admin only) |
| /api/sub-services/{sub_service} | POST | manage.sub_services |
| /api/sub-services/{sub_service} | DELETE | manage.sub_services |

Create body: `service_id` (required, exists:services,id), `name`/`name_ar` (required, max:255), `description` (nullable), `price` (required, numeric, min:0), `is_active` (sometimes, boolean). Update: all `sometimes`.

Response (nests full `service`, which nests `category`; no timestamps on this resource):
```json
{ "id": 1, "service_id": 1, "service": {...}, "name": "...", "name_ar": "...", "description": "string|null", "price": 15.0, "is_active": true }
```

---

## 8. Car Types
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/car-types | GET | show.car_types |
| /api/car-types/{id} | GET | show.car_types |
| /api/car-types | POST | manage.car_types (super_admin only) |
| /api/car-types/{car_type} | POST | manage.car_types |
| /api/car-types/{car_type} | DELETE | manage.car_types |

Body: `name`/`name_ar` (required, max:255), `price_multiplier` (sometimes, numeric, min:0 — pricing coefficient, e.g. SUV vs sedan), `is_active` (sometimes, boolean). Update: all `sometimes`.

```json
{ "id": 1, "name": "string", "name_ar": "string", "price_multiplier": 1.2, "is_active": true, "created_at": "...", "updated_at": "..." }
```

---

## 9. Car Brands
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/car-brands | GET | show.car_brands |
| /api/car-brands/{id} | GET | show.car_brands |
| /api/car-brands | POST | manage.car_brands (super_admin only) |
| /api/car-brands/{car_brand} | POST | manage.car_brands |
| /api/car-brands/{car_brand} | DELETE | manage.car_brands |

Body: `name` (required, max:255, **unique:car_brands,name**), `logo` (nullable, string max:255 — a path/URL string, not a file upload), `is_active` (sometimes, boolean). Update: `name` unique check ignores current record.

```json
{ "id": 1, "name": "string", "logo": "string|null", "is_active": true, "created_at": "...", "updated_at": "..." }
```

---

## 10. Pricing Rule Types
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/pricing-rule-types | GET | show.pricing_rule_types |
| /api/pricing-rule-types/{pricing_rule_type} | GET | show.pricing_rule_types |
| /api/pricing-rule-types | POST | manage.pricing_rule_types (super_admin only) |
| /api/pricing-rule-types/{pricing_rule_type} | POST | manage.pricing_rule_types |
| /api/pricing-rule-types/{pricing_rule_type} | DELETE | manage.pricing_rule_types |

Who can view: `super_admin`, `admin` only (not workshop/customers/employees). Body: `name`/`name_ar` (required, max:255). Minimal resource, no timestamps:
```json
{ "id": 1, "name": "string", "name_ar": "string" }
```
This is a free-form lookup table for categorizing pricing rules (e.g. "peak hours", "VIP surcharge") — no enum/constraint on how it's interpreted.

---

## 11. Pricing Rules
Auth: all `auth:sanctum` **+ `active.user`** on the GET routes (the only resource in this doc where `active.user` applies to index/show, not just mutations).

| Endpoint | Method | Permission |
|---|---|---|
| /api/pricing-rules | GET | show.pricing_rules + active.user |
| /api/pricing-rules/{pricing_rule} | GET | show.pricing_rules + active.user |
| /api/pricing-rules | POST | manage.pricing_rule (super_admin only) |
| /api/pricing-rules/{pricing_rule} | POST | manage.pricing_rule |
| /api/pricing-rules/{pricing_rule} | DELETE | manage.pricing_rule |

Who can view: `super_admin`, `admin`, `employee_washer`, `employee_mechanic` (not workshop, not customers).

Body: `pricing_rule_type_id` (required, integer, exists:pricing_rule_types,id), `name`/`name_ar` (required, max:255), `value` (required, numeric — **no min:0**, so negative values pass validation; likely intentional if rules can represent discounts), `conditions` (nullable, array — stored as free-form JSON), `is_active` (nullable, boolean). Update: all `sometimes`.

```json
{ "id": 1, "pricing_rule_type_id": 1, "rule_type": {...}, "name": "...", "name_ar": "...", "value": 10.5, "conditions": {}, "is_active": true }
```
⚠️ Permission naming is inconsistent: `manage.pricing_rule` (singular) for mutations vs `show.pricing_rules` (plural) for reads — make sure your role/permission checks use the exact strings.

---

## 12. Packages
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/packages | GET | show.packages |
| /api/packages/{id} | GET | show.packages |
| /api/packages | POST | manage.packages (super_admin only) |
| /api/packages/{package} | POST | manage.packages |
| /api/packages/{package} | DELETE | manage.packages |

Body: `name` (required, max:255), `description` (nullable), `type` (required, one of `weekly`, `monthly`, `company`), `price` (required, numeric, min:0), `discount_pct` (sometimes, numeric, 0–100), `services_count` (required, integer, min:0), `valid_days` (required, integer, min:0), `is_active` (sometimes, boolean). Update: all `sometimes`.

```json
{ "id": 1, "name": "string", "description": "string|null", "type": "weekly|monthly|company", "price": 0, "discount_pct": 0, "services_count": 0, "valid_days": 0, "is_active": true, "created_at": "...", "updated_at": "..." }
```

---

## 13. Package Services
Join between a Package and a Service — how many times that service is allowed within the package.

| Endpoint | Method | Permission |
|---|---|---|
| /api/package-services | GET | show.package_services |
| /api/package-services/{id} | GET | show.package_services |
| /api/package-services | POST | manage.package_services (super_admin only) |
| /api/package-services/{package_service} | POST | manage.package_services |
| /api/package-services/{package_service} | DELETE | manage.package_services |

Body: `package_id` (required, exists:packages,id), `service_id` (required, exists:services,id), `allowed_count` (required, integer, min:0). Update: all `sometimes`.

```json
{ "id": 1, "package_id": 1, "package": {"...only when eager-loaded"}, "service_id": 1, "service": {"...only when eager-loaded"}, "allowed_count": 0, "created_at": "...", "updated_at": "..." }
```
Note: `package`/`service` use `whenLoaded` — they may be **absent from the JSON entirely** on some calls, don't assume they're always there.

---

## 14. Package Service Sub-Services
Links a `package_service` to a specific `sub_service`, optionally overriding its price.

| Endpoint | Method | Permission |
|---|---|---|
| /api/package-service-sub-services | GET | show.package_service_sub_services |
| /api/package-service-sub-services/{id} | GET | show.package_service_sub_services |
| /api/package-service-sub-services | POST | manage.package_service_sub_services (super_admin only) |
| /api/package-service-sub-services/{package_service_sub_service} | POST | manage.package_service_sub_services |
| /api/package-service-sub-services/{package_service_sub_service} | DELETE | manage.package_service_sub_services |

Body: `package_service_id` (required, exists:package_services,id), `sub_service_id` (required, exists:sub_services,id), `price_override` (nullable, numeric, min:0), `is_active` (sometimes, boolean). Update: all `sometimes`.

```json
{ "id": 1, "package_service_id": 1, "package_service": {"...when loaded"}, "sub_service_id": 1, "sub_service": {"...when loaded"}, "price_override": 0, "is_active": true, "created_at": "...", "updated_at": "..." }
```

---

## 15. Branches
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/branches | GET | show.branches | super_admin, admin |
| /api/branches/{branch} | GET | show.branches | super_admin, admin |
| /api/branches | POST | add.branch | super_admin only |
| /api/branches/{branch} | POST | edit.branch | super_admin (any) / admin (own branch only, else 403) |
| /api/branches/{branch} | DELETE | delete.branch | super_admin only |

Note: `GET /branches` returns **every** branch regardless of caller — there's no admin-scoping on the list endpoint (unlike inventories).

Create body (`CreateBranchRequest`):
| Field | Type | Required | Rules |
|---|---|---|---|
| admin_id | int | yes | exists:users,id, **and** must already hold the `admin` role, else: "The selected admin must be a user with the admin role." |
| name, name_ar | string | yes | max:255 |
| city | string | yes | max:255 |
| address | string | yes | — |
| latitude | numeric | no | between -90,90 |
| longitude | numeric | no | between -180,180 |
| phone | string | yes | max:30 |
| is_active | bool | no | — |
| working_hours | array | no | — |
| is_24h | bool | no | — |

Update: same fields, all `sometimes`; `admin_id` keeps the "must already be an admin" rule when supplied. `PUT`-style scoping: an `admin` can only edit the branch they manage (`branch.admin_id === auth id`) — enforced in the service layer (403), even though the route-level `can:edit.branch` gate already passed.

```json
{ "id": 1, "admin_id": 1, "manager": {"...UserResource, when loaded"}, "name": "...", "name_ar": "...", "city": "...", "address": "...", "latitude": 0.0, "longitude": 0.0, "phone": "...", "is_active": true, "working_hours": {}, "is_24h": false }
```

### ⚠️ Branch ↔ Admin bootstrap flow — read this before building the "create branch" screen

Creating a branch **requires an existing `admin_id`** pointing at a user who already has the `admin` role — there's no way around that at the DB level (`admin_id` is `NOT NULL`). Two paths create admin users, and they behave differently:

1. **`POST /api/admins`** — creates a bare admin user, no branch attached. Use this to create your first "seed" admin, then pass their id into `POST /api/branches`.
2. **`POST /api/admin/employees` with `type=admin`** — requires an **existing** `branch_id`, and reassigns that branch's `admin_id` to the new user. If the branch already has a real admin, this is rejected (422 on `branch_id`).

**Practical flow:** `POST /api/admins` (get an admin user) → `POST /api/branches` with that user as `admin_id` → later, promote a *different* real admin onto that branch via `POST /api/admin/employees` (type=admin) if needed. See [Admins](#16-admins) below.

---

## 16. Admins
Auth: all `auth:sanctum`. **All endpoints here are `super_admin`-only** — the `admin` role has none of these permissions.

Route note: `{admin}` resolves only to users holding the `admin` role (custom route binding) — passing a non-admin user id 404s.

| Endpoint | Method | Permission |
|---|---|---|
| /api/admins | GET | show.admins |
| /api/admins/{admin} | GET | show.admins |
| /api/admins | POST | add.admin |
| /api/admins/{admin} | POST | edit.admin |
| /api/admins/{admin}/deactivate | POST | edit.admin |
| /api/admins/{admin}/activate | POST | edit.admin |
| /api/admins/{admin} | DELETE | delete.admin |

Create body: `name` (required, max:255), `email` (required, unique:users,email), `phone` (nullable, max:30), `password` (required, min:8, confirmed), `is_active` (sometimes, boolean), `image_url` (nullable, string, max:2048). **No `branch_id` here** — see the bootstrap note in [Branches](#15-branches). HTTP 201 on create.

Update: same fields, all optional; `email` uniqueness ignores the admin's own id; `password` nullable (omit to leave unchanged).

`deactivate`/`activate`: no body, just flips `is_active`.

`DELETE`: ⚠️ does **not** touch any `Branch.admin_id` pointing at this admin — no cascade/nullify. Deleting an admin still assigned to a branch leaves a dangling reference; reassign the branch's admin first.

```json
{ "id": 1, "name": "string", "email": "string", "phone": "string|null", "image_url": "string|null", "is_active": true, "role": "admin", "created_at": "ISO datetime", "updated_at": "ISO datetime" }
```

---

## 17. Materials
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/materials | GET | show.materials | super_admin, admin |
| /api/materials/{material} | GET | show.materials | super_admin, admin |
| /api/materials | POST | manage.material | super_admin only |
| /api/materials/{material} | POST | manage.material | super_admin only |
| /api/materials/{material} | DELETE | manage.material | super_admin only |

Body: `material_unit_id` (required, exists:material_units,id), `name`/`name_ar` (required, max:255), `description` (nullable), `unit_price` (required, numeric, min:0), `is_vip_material` (nullable, boolean), `is_active` (nullable, boolean). Update: all `sometimes`.

```json
{ "id": 1, "material_unit_id": 1, "unit": {"id":1,"name":"...","name_ar":"...","is_decimal":false}, "name": "...", "name_ar": "...", "description": "string|null", "unit_price": 0, "is_vip_material": false, "is_active": true }
```
Note: `unit` is **not** loaded on the create response (only on show/update) — call `GET /materials/{id}` if you need it right after creating.

⚠️ Deleting a material **cascade-deletes** its inventory rows and inventory-transaction history (FK `cascadeOnDelete`). Confirm before allowing this in the UI.

---

## 18. Material Units
Auth: all `auth:sanctum`.

| Endpoint | Method | Permission |
|---|---|---|
| /api/material-units | GET | show.material_units |
| /api/material-units/{material_unit} | GET | show.material_units |
| /api/material-units | POST | manage.material_units (super_admin only) |
| /api/material-units/{material_unit} | POST | manage.material_units |
| /api/material-units/{material_unit} | DELETE | manage.material_units |

Body: `name`/`name_ar` (required, max:255), `is_decimal` (nullable boolean, e.g. liters vs. pieces). Update: all `sometimes`.

```json
{ "id": 1, "name": "string", "name_ar": "string", "is_decimal": false }
```
⚠️ Deleting a unit cascade-deletes every material using it (and by extension their inventory/transactions too) — same destructive-chain warning as above.

---

## 19. Inventories
Wrapped in `active.admin` — `admin` callers must be active.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/inventories | GET | show.inventory | super_admin (all branches), admin (own branch only) |
| /api/inventories/{inventory} | GET | show.inventory | super_admin (any) / admin (own branch, else 403) |
| /api/inventories | POST | manage.inventory | super_admin, admin |
| /api/inventories/{inventory} | POST | manage.inventory | super_admin (any) / admin (own branch, else 403) |
| /api/inventories/{inventory} | DELETE | manage.inventory | super_admin (any) / admin (own branch, else 403) |

Create body: `branch_id` (**required only for super_admin** — for `admin` it's ignored/overridden server-side to the branch they manage; exists:branches,id; unique per branch+material), `material_id` (required, exists:materials,id), `quantity` (nullable, min:0), `min_quantity` (nullable, min:0). Update: all `sometimes`, uniqueness check ignores current row.

```json
{ "id": 1, "branch_id": 1, "branch": {"id":1,"name":"...","name_ar":"..."}, "material_id": 1, "material": {"...MaterialResource"}, "quantity": 0, "min_quantity": 0, "updated_at": "..." }
```

⚠️ **This endpoint directly overwrites `quantity`/`min_quantity` and does NOT create an audit-trail row.** For stock movements (receiving, usage, transfers), use [Inventory Transactions](#20-inventory-transactions) instead — reserve this one for corrections/metadata (`min_quantity`, reassigning `material_id`).

---

## 20. Inventory Transactions
Wrapped in `active.admin`. **Append-only ledger — no update/delete endpoints exist.**

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/inventory-transactions | GET | show.inventory_transactions | super_admin (all), admin (own branches, incl. transfer-in legs received there) |
| /api/inventory-transactions/{inventory_transaction} | GET | show.inventory_transactions | super_admin (any) / admin (own branch, else 403) |
| /api/inventory-transactions | POST | manage.inventory_transactions | super_admin, admin |

List/show response fields: `id, branch_id, branch, destination_branch_id, destination_branch (nullable), material_id, material, created_by, creator, type, quantity, quantity_before, quantity_after, reference_id, note, created_at`. Ordered newest-first.

### POST /api/inventory-transactions (the important one)

| Field | Type | Required | Rules |
|---|---|---|---|
| branch_id | int | **required only for super_admin** | exists:branches,id — for `admin`, always derived server-side from the branch they manage; anything sent is ignored |
| destination_branch_id | int | required **only when** `type=transfer_out`, **prohibited** otherwise | exists:branches,id, different:branch_id |
| material_id | int | yes | exists:materials,id |
| type | string enum | yes | one of `in`, `out`, `transfer_out` — **`transfer_in` is rejected**, it's system-generated only |
| quantity | numeric | yes | min:0.01 (must be positive) |
| reference_id | string | no | nullable, max:255 (DB column is `char(36)`/UUID — non-UUID strings near the max length can hit a DB truncation error even though request validation passes) |
| note | string | no | nullable |

Business logic (all inside one DB transaction):
- `type=in` → stock `+quantity`; `out`/`transfer_out` → stock `-quantity`. The branch/material `Inventory` row is auto-created (starting at 0) if it doesn't exist, and row-locked during the update.
- **Stock cannot go negative** — insufficient stock returns **422** with a dedicated error rather than a generic validation error.
- If `destination_branch_id === branch_id` (including after server-side branch resolution for admins) → 422 on `destination_branch_id`.
- **`type=transfer_out` creates TWO ledger rows atomically**: the requested `transfer_out` leg on the source branch, and a system-generated `transfer_in` leg on the destination branch (reversed `branch_id`/`destination_branch_id`, same quantity/material/reference/note). Only the first (source) row is returned in the response — the `transfer_in` leg shows up later via `GET /inventory-transactions` on the destination branch's history.
- `type=in` never uses/needs `destination_branch_id` — don't send it.

---

## 21. Points

### GET /api/points/show/{customer_id?}
Auth: `can:show.user_points` + `active.user`. Who: `super_admin`, `admin`, `customer_personal`, `customer_company`.

`customer_id` (route, optional): **required** for `super_admin`/`admin` — omit it and you get **HTTP 400** `"customer_id is required"` (not a 422). For any other role, it's ignored and forced to the caller's own id — a customer can never view someone else's balance through this param.

Response: `{ id, customer_id, balance, customer (nested UserResource, when loaded) }`.

### GET /api/points
Auth: `can:show.all_user_points` + `active.admin`. Who: `super_admin`, `admin` only. Lists **all** customers' balances, no params.

---

## 22. Points Transactions

### GET /api/points/transactions/{customer_id?}
Auth: `can:show.points_transactions` + `active.user`. Same optional/forced `customer_id` pattern as [Points](#21-points) (400 if omitted for SA/admin, forced to self for everyone else).

Response: array of `{ id, customer_id, type (earn|redeem), points, balance_before, balance_after, reference_type, reference_id, expires_at, note, created_at }`.

### GET /api/points/transactions/show/{transaction}
Auth: `can:show.points_transactions` + `active.user`. `super_admin`/`admin` can view any transaction by id; anyone else gets **403 "Unauthorized"** unless `transaction.customer_id` matches their own id.

> Note: a `POST /api/points/transactions` (manual adjust) endpoint exists in the codebase (`PointsTransactionController::store`, validated by `AdjustPointsRequest`: `customer_id`, `type` in earn/redeem, `points` min:1, `note`) but its route is currently **commented out** in `routes/api.php` — not reachable today.

---

## 23. User Packages

| Endpoint | Method | Permission | Who / behavior |
|---|---|---|---|
| /api/user-packages/{customer_id?} | GET | show.user_packages + active.user | list; same optional/forced `customer_id` pattern as Points (400 if omitted for SA/admin) |
| /api/user-packages/show/{user_package} | GET | show.user_packages + active.user | SA/admin any; others only their own (403 otherwise) |
| /api/user-packages/{customer_id?} | POST | add.user_package + active.user | create; same customer_id pattern — server sets `user_id` from the resolved id, client can't pass an arbitrary `user_id` in the body |
| /api/user-packages/update/{user_package} | POST | edit.user_package + active.user | SA/admin any; others only their own |
| /api/user-packages/{user_package} | DELETE | manage.user_packages | super_admin only; **no ownership check** |

Create body (`CreateUserPackageRequest`): `package_id` (required, exists:packages,id), `status` (sometimes, one of `active`,`expired`,`cancelled`,`suspended`).

Update body (`UpdateUserPackageRequest`): `remaining_count` (sometimes, integer, min:0), `status` (sometimes, same enum).

Response: `{ id, user_id, user (when loaded), package_id, package (when loaded), start_date, end_date, remaining_count, status, created_at }`.

---

## 24. Points Configs
Index/show wrapped in `active.admin`; mutations are not.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/points-configs | GET | show.point_config + active.admin | super_admin, admin |
| /api/points-configs/{id} | GET | show.point_config + active.admin | super_admin, admin |
| /api/points-configs | POST | manage.point_config | super_admin only |
| /api/points-configs/{points_config} | POST | manage.point_config | super_admin only |
| /api/points-configs/{points_config} | DELETE | manage.point_config | super_admin only |

Body: `earn_per_amount` (required, numeric, min:0), `redeem_value` (required, numeric, min:0), `min_redeem` (required, integer, min:0), `max_earn_per_order` (required, integer, min:0), `is_active` (sometimes, boolean). Update: all `sometimes`.

```json
{ "id": 1, "earn_per_amount": 0, "redeem_value": 0, "min_redeem": 0, "max_earn_per_order": 0, "is_active": true, "updated_at": "..." }
```

---

## 25. Problem Types
Auth: all `auth:sanctum`, no `active.*`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/problem-types | GET | show.problem_types | super_admin, admin only |
| /api/problem-types/{problem_type} | GET | show.problem_types | super_admin, admin only |
| /api/problem-types | POST | manage.problem_types | super_admin only |
| /api/problem-types/{problem_type} | POST | manage.problem_types | super_admin only |
| /api/problem-types/{problem_type} | DELETE | manage.problem_types | super_admin only |

Body: `name`/`name_ar` (required, max:255), `is_active` (nullable, boolean). Update: `sometimes`.

```json
{ "id": 1, "name": "string", "name_ar": "string", "is_active": true }
```

---

## 26. Suggested Problems
Auth: all `auth:sanctum`, no `active.*`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/suggested-problems | GET | show.suggested_problems | broadly available (most roles, incl. customers/workshop/employees) |
| /api/suggested-problems/{suggested_problem} | GET | show.suggested_problems | same |
| /api/suggested-problems | POST | manage.suggested_problems | super_admin only |
| /api/suggested-problems/{suggested_problem} | POST | manage.suggested_problems | super_admin only |
| /api/suggested-problems/{suggested_problem} | DELETE | manage.suggested_problems | super_admin only |

Body: `name`/`name_ar` (required, max:255), `description` (nullable), `category` (required, one of enum values: `engine`, `brakes`, `electrical`, `tires`, `mechanical`, `locksmith`). Update: `sometimes`, description stays nullable.

```json
{ "id": 1, "name": "string", "name_ar": "string", "description": "string|null", "category": "engine|brakes|electrical|tires|mechanical|locksmith" }
```

---

## 27. System Settings
Auth: all `auth:sanctum`, no `active.*`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/system-settings | GET | show.system_settings | super_admin, admin |
| /api/system-settings/{system_setting} | GET | show.system_settings | super_admin, admin |
| /api/system-settings | POST | manage.system_setting | super_admin only |
| /api/system-settings/{system_setting} | POST | manage.system_setting | super_admin only |
| /api/system-settings/{system_setting} | DELETE | manage.system_setting | super_admin only |

Body: `key` (required, max:255, **unique**), `value` (required, string), `type` (required, one of `string`, `number`, `boolean`, `json`), `description` (nullable). Update: `key` unique ignoring current record, rest `sometimes`.

```json
{ "id": 1, "key": "string", "value": "string", "type": "string|number|boolean|json", "description": "string|null", "updated_at": "..." }
```

---

## 28. AI Rules
Auth: all `auth:sanctum`, no `active.*`.

| Endpoint | Method | Permission | Who |
|---|---|---|---|
| /api/ai-rules | GET | show.ai_rules | super_admin, admin |
| /api/ai-rules/{ai_rule} | GET | show.ai_rules | super_admin, admin |
| /api/ai-rules | POST | add.ai_rule | super_admin only |
| /api/ai-rules/{ai_rule} | POST | edit.ai_rule | super_admin only |
| /api/ai-rules/{ai_rule} | DELETE | delete.ai_rule | super_admin only |

Body: `brand_id` (nullable, exists:car_brands,id), `name`/`name_ar` (required, max:255), `type` (required, one of `maintenance`, `recommendation`, `warning`, `promotion`, `upsell`, `diagnosis`), `condition_key`/`condition_value` (nullable, max:255), `car_type` (nullable, one of `sedan`, `suv`, `hatchback`, `pickup`), `fuel_type` (nullable, enum — same values as cars' `fuel_type`), `response_template` (required, string), `is_active` (nullable, boolean). Update: `sometimes`.

```json
{ "id": 1, "brand_id": 1, "brand": {"...CarBrandResource, when loaded"}, "name": "...", "name_ar": "...", "type": "maintenance|recommendation|warning|promotion|upsell|diagnosis", "condition_key": "string|null", "condition_value": "string|null", "car_type": "sedan|suv|hatchback|pickup|null", "fuel_type": "petrol|diesel|electric|hybrid|null", "response_template": "string", "is_active": true }
```

---

## Known gaps / things frontend should be aware of

- **No pagination** on almost every list endpoint — only `cars/all` and `points` (index) paginate; everything else returns the full collection. Expect this to change for high-growth lists (orders, inventory-transactions) — check with backend before building infinite-scroll against endpoints that aren't paginated yet.
- **Image upload fields on `auth/register/*` and `profile/updateProfile`** are validated but not actually persisted to storage yet — don't build upload UI against these until backend confirms it's wired up. (Cars' `image` field on create/update *does* work correctly.)
- **`POST /api/points/transactions` (manual point adjustment) is disabled** — route is commented out.
- **Optional `{customer_id?}` route params** (`points/show`, `points/transactions`, `user-packages`) are enforced as effectively-required in the controller for `super_admin`/`admin` (400 if omitted) — don't rely on the route signature alone.