# Products Management System

Laravel 12 + Vue 3 application for managing product inventory with Excel file upload functionality.

## Features

- Display products with pagination, sorting, and search
- Real-time search by Product ID (partial matching - filters as you type)
- Upload Excel files to update product quantities
- Asynchronous file processing using Laravel queues
- Auto-create products if they don't exist in database

## Requirements

- PHP >= 8.2
- MySQL 5.7+
- Node.js >= 20.19 or >= 22.12 (required for Vite 7)
- Composer

## Installation

1. Clone the repository
   ```bash
   git clone https://github.com/lilytulips/compasia-assessment.git
   cd compasia-assessment
   ```

2. Install dependencies
   ```bash
   composer install
   npm install
   ```
   
   **Note:** If you use `nvm`, the project includes a `.nvmrc` file. Run `nvm use` to automatically switch to the correct Node.js version.

3. Setup environment
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure database in `.env`
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   QUEUE_CONNECTION=database
   ```

5. Run migrations
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional: adds sample data
   ```

6. Build assets
   ```bash
   npm run build
   ```

## Running

1. Start Laravel server
   ```bash
   php artisan serve
   ```

2. Start queue worker (required for file processing)
   ```bash
   php artisan queue:work
   ```

3. Access application
   - Open `http://localhost:8000/products`

## Excel File Format

Upload `product_status_list.xlsx` with the following columns:

| Product ID | Types      | Brand | Model           | Capacity   | Status |
|------------|------------|-------|-----------------|------------|--------|
| 4450       | Smartphone | Apple | iPhone SE       | 2GB/16GB   | Sold   |
| 6039       | Smartphone | Apple | iPhone SE (2020) | 3GB/64GB   | Buy    |

**Rules:**
- First row should contain headers
- Status must be `Sold` or `Buy`
- Each row = 1 transaction (Sold deducts 1, Buy adds 1)
- Products not in database will be created automatically

## API Endpoints

**GET /api/products**
- Query params: 
  - `product_id` - Partial match search (e.g., "4" finds all products starting with 4)
  - `per_page` - Items per page (default: 10)
  - `sort_by` - Column to sort by (id, product_id, types, brand, model, capacity, quantity)
  - `sort_order` - Sort direction (asc, desc)
- Returns paginated product list

**POST /api/products/upload**
- Upload Excel file (.xlsx or .xls)
- Returns 202 Accepted (processing in background)

## Troubleshooting

**File upload not processing:**
- Ensure queue worker is running: `php artisan queue:work`

**Quantities not updating:**
- Check queue worker is running
- Verify Excel file format matches requirements
- Check logs: `tail -f storage/logs/laravel.log`

**404 on API routes:**
- Run `php artisan route:clear` and restart server

## Technologies

- Backend: Laravel 12, PHP 8.2+
- Frontend: Vue 3, Inertia.js, Tailwind CSS
- Database: MySQL
- Queue: Laravel Queue (Database driver)
- Excel: Maatwebsite Excel
