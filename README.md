# Patent Analytics API  
CakePHP 5 + PostgreSQL 18 + Docker

---

## Overview

This project is a backend analytics API built using **CakePHP 5** and **PostgreSQL 18**, containerized using **Docker**.

The API performs statistical analysis on patent data including:

- Summary statistics
- Year-based filtering
- Frequency distribution
- Correlation analysis

The entire application runs inside Docker containers for easy setup and portability.

---

## 🏗 Tech Stack

- PHP 8.2
- CakePHP 5
- PostgreSQL 18
- Docker & Docker Compose
- Apache

---

##  Project Structure

```
├── src/
├── config/
├── webroot/
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

## Quick Start (Docker Setup)

### 1 Clone the repository

```bash
git clone git@github.com:mohdahsanshah/patent-analytics-api.git
cd patent-analytics-api
```

### 2 Build and start containers

```bash
docker compose up --build
```

### 3 Access the application

```
http://localhost:8080
```

---

## Docker Services

| Service | Description |
|----------|------------|
| app | CakePHP application |
| db | PostgreSQL 18 database |

Database credentials are configured using environment variables inside `docker-compose.yml`.

---

## Database Configuration

Environment variables used:

```
DATABASE_HOST=db
DATABASE_PORT=5432
DATABASE_USERNAME=postgres
DATABASE_PASSWORD=postgres
DATABASE_NAME=patents_db
```

The application connects internally to the PostgreSQL container using the service name `db`.

---

## API Endpoints

---

### 1. Summary Statistics

```
GET /summary
```

Returns:

- Total patents
- Mean publication year
- Minimum publication year
- Maximum publication year
- Standard deviation
- Median publication year
- Year frequency distribution (Top 10)
- Top 5 assignees

Caching is implemented to optimize performance.

---

### 2. Filter by Publication Year

```
GET /query?year=2023
```

Returns:

- patent records with count-paginated
- Validates year input
- Returns HTTP 400 for invalid input

---

### 3. Correlation Analysis

```
GET /correlation
```

Computes Pearson correlation between:

- Filing creation year
- Publication year

Uses PostgreSQL `CORR()` function.

---

##  API Features

- Input validation
- Proper HTTP status codes
- Error handling (try-catch)
- Response formatted in JSON
- Caching using CakePHP FileEngine
- Dockerized for portability

---

##  Statistical Functions Used

- `COUNT()`
- `AVG()`
- `MIN()`
- `MAX()`
- `STDDEV()`
- `PERCENTILE_CONT()`
- `CORR()`
- `GROUP BY`


---

##  Tested Environment

- Docker Desktop (WSL2 backend)
- PostgreSQL 18
- PHP 8.2

---

--------------------------------------------
--------------------------------------------
---

##  Running Without Docker (Local Setup)

If you prefer to run the application without Docker, follow these steps:

### Requirements

- PHP 8.2+
- Composer
- PostgreSQL 18
- CakePHP CLI

---

### 1 Clone the repository

```bash
git clone git@github.com:mohdahsanshah/patent-analytics-api.git
cd patent-analytics-api
```

---

### 2 Configure Database

Open:

```
config/app_local.php
```

Update database settings:

```php
'host' => 'localhost',
'port' => '5432',
'username' => 'postgres',
'password' => 'your_password',
'database' => 'patents_db',
```

Make sure PostgreSQL service is running locally.

---

### 3 Start Development Server

```bash
bin/cake server
```

Application will run at:

```
http://localhost:8765
```

---

##  Notes

- Default CakePHP dev server runs on port **8765**
- Ensure PostgreSQL is running before starting the server
- Update credentials in `app_local.php` according to your local setup

## 📬 Postman Collection

A Postman collection is included inside the `/postman` directory.

Import the collection into Postman and test the endpoints locally:

Base URL:
http://localhost:8080

Local server URL: localhost:8765

##  Author

Mohd Ahsan  
Backend Developer (PHP / Laravel / CakePHP)

---

##  License

For evaluation / assignment purposes only.
