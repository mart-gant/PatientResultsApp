# Patient Results App

Laravel + Vue application for importing patient test results from CSV, authenticating with JWT, and browsing grouped results in the browser.

## Features

- `results:import {file}` console command for CSV import.
- JWT-based `POST /api/login` and `GET /api/results`.
- Vue frontend with LocalStorage session persistence and automatic logout on token expiry.
- PostgreSQL/MySQL-ready migrations for patients, orders, and test results.
- GitLab CI and Docker Compose configuration for local development.

## Import

Use the provided `results.csv` format with semicolon-separated columns:

`patientId;patientName;patientSurname;patientSex;patientBirthDate;orderId;testName;testValue;testReference`

Example:

```bash
php artisan results:import /path/to/results.csv
```

Import logs are written to `storage/logs/import-results.log`.

## API

- `POST /api/login`
  - Body: `{ "login": "PiotrKowalski", "password": "1983-04-12" }`
  - Response: `{ "token": "...", "expiresAt": "..." }`
- `GET /api/results`
  - Header: `Authorization: Bearer <token>`
  - Response: patient data grouped by order

## Frontend

Open `/` in the browser. The app renders a login card and, after login, a results dashboard with patient details and all results grouped by order.

## Local Run

```bash
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

## Docker

```bash
docker compose up --build
```

Then, in a second terminal:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Open the app at `http://localhost:8000` and the frontend dev server at `http://localhost:5173`.

Demo credentials from the seed:

- `PiotrKowalski` / `1983-04-12`
- `AnnaJablonska` / `2002-12-12`

If you want to import the provided CSV instead of the demo seed, copy it into the container or mount it into the `app` service and run:

```bash
docker compose exec app php artisan results:import storage/app/results.csv
```

## CI

`.gitlab-ci.yml` runs PHP tests and a frontend build.
