# Gate Proxy Panel - External Integration API Documentation

This API allows external systems (like Paymenter) to manage users and subscriptions within the Gate Proxy Panel.

## 1. Authentication

### API Key (JWT)
All requests must include a Bearer Token in the `Authorization` header.
- **Header**: `Authorization: Bearer {your_jwt_token}`
- **Note**: The token is signed using the Panel's `APP_KEY`.

### IP Whitelisting
The API is further secured by IP whitelisting. Only IPs listed in the `.env` file under `API_WHITELIST_IPS` are allowed to make requests.

---

## 2. Global Response Format

Success Response:
```json
{
    "status": "success",
    "message": "Action performed successfully",
    "data": { ... }
}
```

Error Response:
```json
{
    "status": "error",
    "message": "Error description",
    "errors": { ... }
}
```

---

## 3. Endpoints

### 3.1 User Sync
**URL**: `/api/admin/external/users/sync`  
**Method**: `POST`  
**Description**: Synchronizes a user based on their email. Creates a new user if they don't exist.

**Request Body**:
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Yes | The user's email address. |
| `name` | `string` | No | The user's full name. |

---

### 3.2 Subscription Sync (Idempotent)
**URL**: `/api/admin/external/subscriptions/sync`  
**Method**: `POST`  
**Description**: Full synchronization of a subscription. Safe to call multiple times.

**Request Body**:
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `external_id` | `string` | Yes | Unique ID from your billing system (e.g., Paymenter ID). |
| `email` | `string` | Yes | Associated user's email. |
| `plan_name` | `string` | Yes | The exact name of the Plan in the Panel. |
| `max_server` | `int` | No | Overwrite max servers limit (optional). |
| `expired_at` | `date` | No | Expiration date (`YYYY-MM-DD HH:MM:SS`). |
| `status` | `string` | No | `pending`, `active`, `suspended`, `terminated`. |

---

### 3.3 Activate Subscription
**URL**: `/api/admin/external/subscriptions/{external_id}/activate`  
**Method**: `POST`  
**Description**: Marks a subscription as `active` and ensures servers are active.

**Path Parameters**:
- `external_id`: The ID provided during creation.

**Request Body**:
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Yes | Associated user's email. |
| `plan_name` | `string` | Yes | Plan name. |
| `expired_at` | `date` | No | New expiration date. |

---

### 3.4 Suspend Subscription
**URL**: `/api/admin/external/subscriptions/{external_id}/suspend`  
**Method**: `POST`  
**Description**: Suspends the subscription and removes active proxy nodes/access.

---

### 3.5 Terminate Subscription
**URL**: `/api/admin/external/subscriptions/{external_id}/terminate`  
**Method**: `POST`  
**Description**: Deletes all servers, proxies, and DNS records associated with the subscription. **Irreversible.**

---

## 4. Example cURL Request

```bash
curl -X POST http://panel.raznar.id/api/admin/external/subscriptions/sync \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
        "external_id": "paymenter_service_101",
        "email": "customer@email.com",
        "plan_name": "Pro Proxy Plan",
        "status": "active",
        "expired_at": "2026-05-01 12:00:00"
     }'
```
