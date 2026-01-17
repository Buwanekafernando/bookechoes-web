# BookEchoes Backend Setup & Usage

## 1. Prerequisites
- **PHP** installed and in your system PATH (test by running `php -v` in terminal).
- **MySQL** running (configured in `config/Database.php`).

## 1.1. Database
Open Xampp Control Panel and start the MySQL service.
go to http://localhost/phpmyadmin
then click bookdb

## 2. Configuration
Check `config/Database.php` to ensure it matches your local database credentials:
```php
private $host = "localhost";
private $user = "root";
private $password = "db@366";
private $database = "bookdb";
private $port = 3307; // Change if using default 3306
```

## 3. Running the Server
You can use PHP's built-in development server.
Open a terminal in the `backend/` folder and run:
```bash
php -S localhost:8000
```
This will start the API at `http://localhost:8000`.

## 4. Testing the API
### Option A: Run the Test Script
While the server is running in one terminal, open a **new** terminal window in the `backend/` folder and run:
```bash
php test_api_v3.php
```
This script acts as a client and will:
1.  Login as admin.
2.  Create test data (Author, Publisher, Book).
3.  Fetch the data to verify it exists.

### Option B: Use Postman or Browser
You can manually test endpoints.
**GET** `http://localhost:8000/api/index.php?endpoint=authors`
**GET** `http://localhost:8000/api/index.php?endpoint=books`

To create data (POST), you need to add the `Authorization` header with the Bearer token returned from the login endpoint.
**POST Login**: `http://localhost:8000/api/index.php?endpoint=auth&action=login`
Body:
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```
