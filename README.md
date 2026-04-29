# PHP Backend - Yoga Class + Donation APIs

Production-style layered PHP backend with:

- `Controllers` (API layer)
- `Services` (business rules)
- `Repositories` (database access)
- Validation + centralized exception handling
- **Class registration:** user pays advance (register API); admin can set a custom/agreed price per user (Aadhaar + class); user API shows pending amount by mobile + Aadhaar
- **Donations:** separate from registration; anyone can donate (no need to be registered)

## Deploy targets

- **AWS** — ECS Fargate or App Runner via Docker + ECR (templates in `aws/`).
- **Render** — Docker deploy with `render.yaml`; bind to `PORT`; use external MySQL.
- **Hostinger** — Apache + `.htaccess`; doc root `public/` or subfolder; MySQL from hPanel.
- **Local** — `php -S localhost:8080 -t public` or Docker.

## Folder Structure

```text
php/
  .htaccess
  index.php
  Dockerfile
  render.yaml
  .env.example
  config/
  database/
  public/
  src/
  storage/
```

## Environment variables

Copy `.env.example` to `.env` and set values. All config can also be set via environment (no `.env` on Render; use Dashboard).

| Variable | Description |
|----------|-------------|
| `DB_DRIVER` | `mysql` or `sqlite` (use `mysql` on Render/Hostinger) |
| `DB_HOST` | MySQL host |
| `DB_PORT` | MySQL port (default `3306`) |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database user |
| `DB_PASSWORD` | Database password |
| `DB_CHARSET` | `utf8mb4` |
| `APP_BASE_PATH` | Optional subfolder (e.g. `/api`) |
| `UPLOAD_*` | Optional upload paths and limits |

## Deploy on Render

1. Push this repo to GitHub/GitLab and connect it in [Render](https://render.com).
2. **New → Blueprint** and select the repo; Render will read `render.yaml`.  
   Or **New → Web Service** → choose repo → set **Runtime** to **Docker** and **Dockerfile Path** to `./Dockerfile`.
3. In the service **Environment** tab, set:
   - `DB_DRIVER=mysql`
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (from an external MySQL or e.g. PlanetScale/Aiven).
   - Render does not offer managed MySQL; use an external provider and run `database/schema_mysql.sql` and `database/seed_classes.sql` once.
4. Deploy. The app listens on `PORT` (set by Render). Health: **GET** `https://<your-service>.onrender.com/api/health`.

**Notes:**

- **Disk:** Render’s filesystem is ephemeral. Uploaded files (donations/registrations) are lost on redeploy. For production, plan to use external file storage (e.g. S3) and wire paths in config later.
- **Database:** Use MySQL (or compatible) and run the schema + seed SQL manually after first deploy.

## Deploy on AWS

This project now ships with AWS-ready Docker runtime and templates:

- `Dockerfile` uses Apache + PHP and exposes port `80`
- health check endpoint is `/api/health`
- `aws/task-definition.json` is an ECS Fargate task definition template
- `aws/apprunner.yaml` is an App Runner config template

### Option A: ECS Fargate (recommended)

1. Create an ECR repository (for example `vy-backend`).
2. Build and push image:
   - `docker build -t vy-backend:latest .`
   - tag/push to your ECR URI (`<account>.dkr.ecr.<region>.amazonaws.com/vy-backend:latest`)
3. Store DB secrets in AWS Systems Manager Parameter Store:
   - `/vy-backend/DB_HOST`
   - `/vy-backend/DB_DATABASE`
   - `/vy-backend/DB_USERNAME`
   - `/vy-backend/DB_PASSWORD`
4. Update placeholders in `aws/task-definition.json`:
   - `<account-id>`, `<region>`, execution/task role ARNs, image URI.
5. Register the task definition and create/update ECS service behind an ALB.
6. Set ALB target group health check path to `/api/health`.

### Option B: App Runner

1. Push this repo to GitHub.
2. In AWS App Runner, create service from source code or from ECR image.
3. If using source code mode, point to `aws/apprunner.yaml`.
4. Add runtime environment variables:
   - `DB_DRIVER=mysql`, `DB_PORT=3306`, `DB_CHARSET=utf8mb4`
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (from App Runner secrets or SSM).
5. Deploy and verify: `GET /api/health`.

### AWS production notes

- Container filesystem is ephemeral, so uploads in `storage/uploads/*` are not durable.
- For real production, move uploaded files to S3 (or EFS) and persist only file URLs/keys in DB.
- Use RDS MySQL (or Aurora MySQL-compatible) and run `database/schema_mysql.sql` and `database/seed_classes.sql` once.

## Hostinger Deployment Steps

### Option A: Deploy in domain root (`public_html`)

1. Upload all project files into `public_html`.
2. Keep `.htaccess` and `index.php` in root as uploaded.
3. Configure DB settings for MySQL.
4. Open `https://your-domain.com/api/health`.

### Option B: Deploy in subfolder (example: `public_html/api`)

1. Upload project inside `public_html/api`.
2. Set `APP_BASE_PATH=/api`.
3. Open `https://your-domain.com/api/api/health` if domain points to parent folder.

Tip: to avoid double `/api`, keep project in root and call `/api/*`.

## API docs (Swagger)

- **Swagger UI:** `GET /api/docs` — interactive API documentation and try-it-out.
- **OpenAPI 3.0 spec:** `GET /api/openapi.json` — machine-readable schema.

## APIs

### Health
- `GET /api/health`

### Classes list (dropdown data)
- `GET /api/classes`

### Create class
- `POST /api/classes`  
  Body (JSON): `{ "class_name": "Mind Power - 1st Level", "total_fee": 4000, "is_active": true }`  
  `class_name` and `total_fee` (positive number) are required; `is_active` defaults to `true`.

### Update class (change amount / name / active)
- `PUT /api/classes`  
  Body (JSON): `{ "id": 1, "total_fee": 5500 }` or `{ "id": 1, "class_name": "New Name", "is_active": false }`  
  `id` is required; at least one of `class_name`, `total_fee`, `is_active` is required.

### Admin: Set agreed/negotiated fee for a user and class
- `PUT /api/classes/agreed-fee`  
  Body (JSON): `{ "aadhaar_number": "123456789012", "class_id": 1, "agreed_fee": 3500 }`  
  Use when the user bargains the class price; this sets the fee for that specific person (Aadhaar) and class. Remaining and pending amount are computed from this agreed fee. Can be called before or after the user’s first payment.

### Class registration (with partial payment)
- `POST /api/classes/register-payment`

Use **multipart/form-data** so you can send Aadhaar documents and transaction receipt. Required fields:

| Field | Type | Required |
|-------|------|----------|
| `name` | text | Yes |
| `mobile` | text (10–15 digits) | Yes |
| `email` | text | No |
| `class_id` | number | Yes (from `GET /api/classes`) |
| `preferred_time` | text | No |
| `location` | text | No |
| `siblings_name` | text | No |
| `transaction_msg` | text | No |
| `transaction_id` | text | No |
| `message` | text (additional message) | No |
| `amount_paid` | number | Yes |
| `aadhaar_doc` | file | Yes (front) |
| `aadhaar_doc_back` | file | Yes (back) |
| `transaction_receipt_image` | file | Yes (receipt/screenshot) |

Allowed file types: **JPEG, PNG, WebP, PDF**. Max **5 MB** per file. Files are stored under `storage/uploads/registrations/`.

**Flow:** User first registers by paying an advance (this API). Then:

- **GET payment-summary** (with same mobile + Aadhaar) shows how much they paid and how much is pending. Different users can use the same mobile; each is identified by Aadhaar.
- **Admin** can change the price for that specific user via `PUT /api/classes/agreed-fee` (agreed/negotiated fee). Pending amount = agreed_fee − paid_so_far.

Logic:

- Users are identified by **Aadhaar number** (and class). Same mobile can be used by multiple people; each has a separate payment state per class.
- On first payment, the agreed fee defaults to the class’s `total_fee`; admin can override it via agreed-fee API.
- First payment can be partial (e.g. agreed 5000, paid 500). Next submission with same `aadhaar_number` and `class_id` reduces the remaining amount for that person.
- Overpayment is rejected. Status returned as `partial` or `paid`.

### User: Pending amount / payment summary (mobile + Aadhaar)
- `GET /api/classes/payment-summary?mobile=9876543210&aadhaar_number=123456789012`

Both query params are required: `mobile` (10–15 digits) and `aadhaar_number` (12 digits). Returns one row per class the user has registered for. Each row includes `agreed_fee` (price for this user), `paid_amount`, `remaining_amount`, `pending_amount` (amount still to pay), and `payment_status`. Use this so the user can see what they have paid and what is pending.

### Donations (separate from class registration)

Donations are a separate concept: anyone can donate even if they are not registered for a class.

### Donation submit (with documents)
- `POST /api/donations`

Use **multipart/form-data** (e.g. form with `enctype="multipart/form-data"`). Required fields:

| Field | Type | Required |
|-------|------|----------|
| `name` | text | Yes |
| `mobile` | text (10–15 digits) | Yes |
| `amount_paid` | number | Yes |
| `transaction_id` | text | No |
| `aadhaar_front_doc` | file | Yes |
| `aadhaar_back_doc` | file | Yes |
| `transaction_rep_doc` | file | Yes |

Allowed file types: **JPEG, PNG, WebP, PDF**. Max file size: **5 MB** per file (configurable via `config.php` or `UPLOAD_MAX_BYTES`). On Hostinger, ensure PHP `upload_max_filesize` and `post_max_size` (e.g. in hPanel or `.user.ini`) are at least 10–15 MB so all three docs can be sent.

Uploaded files are stored under `storage/uploads/donations/`. Response includes `donation_id`, `amount_paid`, and stored paths for the three documents.

### Donation history
- `GET /api/donations?mobile=9876543210&aadhaar_number=123456789012`

Both query params are required: `mobile` (10–15 digits) and `aadhaar_number` (12 digits). Returns donations for that person, including `aadhaar_front_path`, `aadhaar_back_path`, `transaction_rep_path` (relative paths for stored docs).

### Admin: registrations and donations (no mobile or Aadhaar)

Admins can list all registrations and all donations **directly** without providing mobile or Aadhaar:

- **GET /api/admin/registrations** — all registrations (optional: search, status, limit, offset, start_date, end_date)
- **GET /api/admin/donations** — all donations (optional: search, status, limit, offset, start_date, end_date)

No query params are required; call with no params to get everything (paginated by limit/offset). See the Admin Dashboard APIs section in the OpenAPI spec (`/api/openapi.json`) for full list of admin endpoints.

### Existing database: run migrations

If you created tables before adding new columns, run the matching migration in your DB:

**Donation doc columns**

- **SQLite:** `database/migrations/add_donation_docs_sqlite.sql`
- **MySQL:** `database/migrations/add_donation_docs_mysql.sql`

**Registration fields (location, siblings_name, transaction_msg, Aadhaar doc paths)**

- **SQLite:** `database/migrations/add_registration_fields_sqlite.sql`
- **MySQL:** `database/migrations/add_registration_fields_mysql.sql`

**Transaction receipt image (registration)**

- **SQLite:** `database/migrations/add_transaction_receipt_registration_sqlite.sql`
- **MySQL:** `database/migrations/add_transaction_receipt_registration_mysql.sql`

**Aadhaar number (class_payments and donations)**

- **SQLite:** `database/migrations/add_aadhaar_number_sqlite.sql`
- **MySQL:** `database/migrations/add_aadhaar_number_mysql.sql`

**Agreed fee per user per class (class_user_fees)**

- **SQLite:** `database/migrations/add_class_user_fees_sqlite.sql`
- **MySQL:** `database/migrations/add_class_user_fees_mysql.sql`

**Donation status (pending, verified, rejected)**

- **SQLite:** `database/migrations/add_donations_status_sqlite.sql`
- **MySQL:** `database/migrations/add_donations_status_mysql.sql`
