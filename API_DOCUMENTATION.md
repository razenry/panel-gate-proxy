# Raznar Control Panel API Documentation

Welcome to the comprehensive API documentation for the Raznar Control Panel. This API adheres to strict Clean Architecture design, ensuring reliable, decoupled interactions ready for production-grade third-party billing integrations (e.g., Paymenter) and administrative webhooks.

---

## 1. Base Information

### Base URL
All endpoints stem from the core installation root prefixed by `/api`.
```text
Production Base URL: https://<your-domain.com>/api
```

### Request Headers
In order to enforce standard encoding and response types, all payload queries MUST establish JSON expectations explicitly.
```http
Accept: application/json
Content-Type: application/json
```

### Authentication (API Key)
To access secured endpoints, an **API Key** mechanism is permanently enforced. Token interactions are entirely stateless JWTs.
- Insert the key using the standard Bearer configuration:
```http
Authorization: Bearer {your-api-key}
```

### IP Whitelist Enforcing
To avoid token hijacking, structural API calls are barricaded by an `IpWhitelist` middleware filter explicitly checking `.env:API_WHITELIST_IPS`.
- If the variable is populated, and your client IP does not exactly match the listing, your access falls into a definitive `403 Forbidden` response despite having a valid token.

---

## 2. Global Response Structuring

The API uniformly utilizes an underlying `ApiResponses` trait establishing an immutable structural standard to maximize predictability.

### Expected Success Payload (HTTP 20X)
```json
{
  "status": "success",
  "message": "Action successfully recorded.",
  "data": { ... }
}
```

### Expected Error Payload (HTTP 4XX & 500)
Whether the request was `401 Unauthenticated`, `403 Forbidden`, `422 Validation Error`, `404 Not Found`, or `500 Server Error`, the format remains strictly grouped:
```json
{
  "status": "error",
  "message": "A human-readable error description.",
  "errors": {
      "identifier": ["The identifier has already been taken."] // Included only on Validation scenarios.
  }
}
```

---

## 3. Account & API Key Management

### API Key Genesis
**How to create:**
API Keys are exclusively generated natively via the web context Admin Dashboard (`/admin/api-keys`). 
Administrators input a label and expiry constraint, the backend cryptographically generates a one-time JWT visibility pass, and irreversibly hashes the shadow into the database linking it to their respective Admin `user_id`.

**Expiration:**
When configuring keys, expiry timestamps are hard-encoded. Any request authenticated against an expired API Key will transparently receive:
```json
{
  "status": "error",
  "message": "Unauthenticated. API key has expired."
}
```

---

## 4. Endpoints Definition

### Authentication Identity
* **URL:** `/api/user`
* **Method:** `GET`
* **Description:** Retrieves metrics of the User Identity linked internally to the provided API Key. Good for testing configuration links.
* **Authentication:** Required (API Key).

---

### Node Management
Administrative endpoints orchestrating the structural hardware ecosystem.

#### Create Node
* **URL:** `/api/admin/nodes`
* **Method:** `POST`
* **Authentication:** Required (API Key).
* **Request Params:**
  * `name` (required, string, alpha-dash, unique)
  * `label` (required, string)
  * `api_url` (required, url)
  * `api_token` (required, string)
* **Response Example:**
```json
{
  "status": "success",
  "message": "Node created successfully.",
  "data": {
    "id": 1,
    "name": "fra-1",
    "label": "Frankfurt Alpha"
  }
}
```

#### List & View Nodes
* **URL:** `/api/admin/nodes` | `/api/admin/nodes/{id}`
* **Method:** `GET`
* **Authentication:** Required (API Key).

#### Remove Node
* **URL:** `/api/admin/nodes/{id}`
* **Method:** `DELETE`
* **Constraint Validation:** Nodes storing active Servers structurally throw a graceful `422 Unprocessable Entity` averting infrastructure breaks.

---

### Subscription Hooks (Paymenter Core)

Subscriptions strictly govern user billing statuses, quotas (`max_server`), expiry thresholds, and active state interactions. Changes on these endpoints heavily orchestrate downstream `ServerService` mechanisms.

#### Create / Sync Subscription
* **URL:** `/api/admin/subscriptions`
* **Method:** `POST`
* **Description:** Fuses the payment event into the database. If the payload uses a fresh email, it quietly generates a user mapping dynamically.
* **Request Params:**
  * `email` (string, required) - Valid email of the paying client.
  * `name`  (string, required) - Customer name.
  * `plan_name` (string, required) - Exact identifier mapping an existing Plan struct.
  * `status` (string, required) - Must be `active`, `suspended`, `terminated`, or `expired`.
  * `external_id` (string, optional) - Traceable Payment Gateway string.
  * `expired_at` (datetime, optional) - ISO Timestamp mapping next billing.

#### Toggle Subscription State
* **URL:** `/api/admin/subscriptions/{id}`
* **Method:** `PUT`/`PATCH`
* **Behavior Interaction:**
  * Setting `"status": "suspended"` actively instructs the underlying logic to iteratively sever internal proxy limits dropping network routing for associated user servers down gracefully.
  * Setting `"status": "active"` automatically restores server connectivity blocks restoring traffic flows!

#### Hard Termination
* **URL:** `/api/admin/subscriptions/{id}`
* **Method:** `DELETE`
* **Description:** A finalized termination hook routing an aggressive sever sequence clearing out nodes explicitly before wiping the relational DB row.

---

### Server Hooks

#### Global Overlook (Admin View)
* **URL:** `/api/admin/servers`
* **Method:** `GET`
* **Request Params (Filters optionally):**
  * `user_id` (int)
  * `subscription_id` (int)
* **Description:** Retrieve an overall nested blueprint map of proxies configured across the ecosystem.

#### Server Provisioning Flow (Client View)
* **URL:** `/api/servers`
* **Method:** `POST`
* **Description:** An authenticated API Key user dispatches a new server route to the proxy queue allocating remote capabilities dynamically. 
* **Request Params:**
  * `label` (required, string)
  * `identifier` (required, string, alpha-dash constraints) - Internally generates routing (e.g. proxying to `{identifier}.raznar.net/port`).
  * `node_id` & `subscription_id` (required, numeric, valid foreign linkages)
  * `src_ip` (required, ip_address)
* **Under-the-Hood Workflow:**
  1. Validation triggers and Node boundaries confirm eligibility limits against nested `.plan` objects.
  2. Data writes locally and yields a `201 Server provisioning started`.
  3. `ProvisionServerJob` deploys in the background establishing structural HTTP calls over to the target node's core creating the exact proxy layout bindings seamlessly via `NodeService`.

#### Manual Reload
* **URL:** `/api/admin/servers/{id}/redeploy`
* **Method:** `POST`
* **Description:** Flushes existing cached state and forcefully queues a manual reconfiguration re-verifying proxy hooks downstream!

---
