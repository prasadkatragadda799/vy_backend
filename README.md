# PHP Backend - Yoga Class + Donation APIs

Production-style layered PHP backend with:

- `Controllers` (API layer)
- `Services` (business rules)
- `Repositories` (database access)
- Validation + centralized exception handling
- Partial payment flow for class fees by same mobile number

## Deploy targets

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

## APIs

### Health
- `GET /api/health`

### Classes list (dropdown data)
- `GET /api/classes`

### Class registration (with partial payment)
- `POST /api/classes/register-payment`

Use **multipart/form-data** so you can send the two Aadhaar documents. Required fields:

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

Allowed file types for Aadhaar: **JPEG, PNG, WebP, PDF**. Max **5 MB** per file. Files are stored under `storage/uploads/registrations/`.

Logic:

- First payment can be partial (e.g. fee 5000, paid 500).
- Next submission with same `mobile` and same `class_id` reduces the remaining fee.
- Overpayment is rejected. Status returned as `partial` or `paid`.

### Payment summary by mobile
- `GET /api/classes/payment-summary?mobile=9876543210`

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
- `GET /api/donations?mobile=9876543210`

Returns donations for that mobile, including `aadhaar_front_path`, `aadhaar_back_path`, `transaction_rep_path` (relative paths for stored docs).

### Existing database: run migrations

If you created tables before adding new columns, run the matching migration in your DB:

**Donation doc columns**

- **SQLite:** `database/migrations/add_donation_docs_sqlite.sql`
- **MySQL:** `database/migrations/add_donation_docs_mysql.sql`

**Registration fields (location, siblings_name, transaction_msg, Aadhaar doc paths)**

- **SQLite:** `database/migrations/add_registration_fields_sqlite.sql`
- **MySQL:** `database/migrations/add_registration_fields_mysql.sql`
