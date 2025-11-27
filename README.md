# Product Master List Management System

A Laravel 12 + Vue 3 application for managing product inventory with Excel file upload functionality.

## Features

- 📊 **Product Master List Display**: View all products with pagination, sorting, and search
- 📤 **Excel File Upload**: Upload `product_status_list.xlsx` files to update product quantities
- 🔄 **Queue Processing**: Asynchronous file processing using Laravel queues
- 🔍 **Search & Filter**: Search products by Product ID
- 📄 **Backend Pagination**: Efficient server-side pagination
- 🎨 **Modern UI**: Beautiful, responsive interface built with Vue 3 and Tailwind CSS

## Requirements

- PHP >= 8.2
- MySQL 5.7+ or MariaDB 10.3+
- Node.js >= 18.x
- Composer
- Laravel Queue Worker (for processing file uploads)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd compasia-assessment
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database**
   Edit `.env` file and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Configure Queue**
   Set queue connection in `.env`:
   ```env
   QUEUE_CONNECTION=database
   ```

7. **Run Migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed Database** (Optional - adds sample products)
   ```bash
   php artisan db:seed
   ```

9. **Build Frontend Assets**
   ```bash
   npm run build
   # Or for development:
   npm run dev
   ```

## Running the Application

### Development Mode

1. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

2. **Start Queue Worker** (Required for file processing)
   ```bash
   php artisan queue:work
   ```
   ⚠️ **Important**: Keep this running in a separate terminal. Without it, file uploads won't be processed.

3. **Start Vite Dev Server** (if using `npm run dev`)
   ```bash
   npm run dev
   ```

4. **Access the Application**
   - Open your browser and navigate to: `http://localhost:8000/products`

### Production Mode

1. **Optimize Application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Build Assets**
   ```bash
   npm run build
   ```

3. **Start Queue Worker** (use a process manager like Supervisor in production)
   ```bash
   php artisan queue:work --daemon
   ```

## Excel File Format

The uploaded Excel file (`product_status_list.xlsx`) should have the following structure:

| Product ID | Types      | Brand | Model           | Capacity   | Status |
|------------|------------|-------|-----------------|------------|--------|
| 4450       | Smartphone | Apple | iPhone SE       | 2GB/16GB   | Sold   |
| 6039       | Smartphone | Apple | iPhone SE (2020) | 3GB/64GB   | Buy    |

### Rules:
- **First row** should contain headers: `Product ID`, `Types`, `Brand`, `Model`, `Capacity`, `Status`
- **Status** column must contain either `Sold` or `Buy`
- Each row represents **1 transaction**:
  - `Sold` = Deduct 1 from quantity
  - `Buy` = Add 1 to quantity
- **Product ID** must exist in the database

## API Endpoints

### Get Products List
```
GET /api/products
```

**Query Parameters:**
- `product_id` (optional): Filter by Product ID
- `per_page` (optional): Items per page (default: 10)
- `sort_by` (optional): Column to sort by (id, product_id, types, brand, model, capacity, quantity)
- `sort_order` (optional): Sort direction (asc, desc)

**Example:**
```bash
GET /api/products?product_id=4450&per_page=10&sort_by=product_id&sort_order=asc
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "product_id": 4450,
      "types": "Smartphone",
      "brand": "Apple",
      "model": "iPhone SE",
      "capacity": "2GB/16GB",
      "quantity": 13
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 10,
  "total": 5
}
```

### Upload Status File
```
POST /api/products/upload
```

**Request:**
- Content-Type: `multipart/form-data`
- Body: `file` (Excel file: .xlsx or .xls)

**Response:**
```json
{
  "message": "File uploaded. Processing started."
}
```

**Status Code:** 202 (Accepted - processing in background)

## Project Structure

```
compasia-assessment/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── ProductController.php    # API endpoints
│   ├── Imports/
│   │   └── StatusImport.php                 # Excel import logic
│   ├── Jobs/
│   │   └── ProcessStatusFileJob.php         # Queue job for file processing
│   └── Models/
│       └── Product.php                      # Product model
├── database/
│   ├── migrations/
│   │   └── create_products_table.php        # Products table migration
│   └── seeders/
│       └── ProductSeeder.php               # Sample data seeder
├── resources/
│   └── js/
│       └── Pages/
│           └── ProductsView.vue             # Main Vue component
└── routes/
    ├── api.php                              # API routes
    └── web.php                              # Web routes
```

## Queue Management

### View Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry {job-id}
# Or retry all:
php artisan queue:retry all
```

### Clear Failed Jobs
```bash
php artisan queue:flush
```

### Restart Queue Worker
After code changes, restart the queue worker:
```bash
# Stop current worker (Ctrl+C)
# Then restart:
php artisan queue:work
```

## Troubleshooting

### File Upload Not Processing

1. **Check if queue worker is running**
   ```bash
   # Make sure you have a terminal running:
   php artisan queue:work
   ```

2. **Check logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Verify file exists**
   Files are stored in: `storage/app/private/uploads/`

### Quantity Not Updating

1. **Check queue worker logs** - Look for processing errors
2. **Verify Excel file format** - Must match the required structure
3. **Check database** - Ensure Product IDs in Excel exist in database
4. **Review import logs** - Check `storage/logs/laravel.log` for StatusImport messages

### Common Issues

**Issue**: "File does not exist" error
- **Solution**: Restart queue worker after code changes

**Issue**: Quantities not updating
- **Solution**: Ensure queue worker is running and check logs for errors

**Issue**: 404 error on API routes
- **Solution**: Run `php artisan route:clear` and restart server

## Development

### Code Style
```bash
# Format PHP code
./vendor/bin/pint
```

### Testing
```bash
php artisan test
```

## Technologies Used

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS
- **Database**: MySQL
- **Queue**: Laravel Queue (Database driver)
- **Excel Processing**: Maatwebsite Excel

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
