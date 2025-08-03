# AlmeApp Laravel Backend

This is the Laravel backend for **AlmeApp**, providing APIs, Shopify integrations, analytics, and customer engagement features.  

## Features
- Shopify store integration (themes, orders, products, customers).
- Customer analytics: visits, sessions, carts, purchases, product visits.
- Notification and discount code management.
- Segment rules for user targeting.
- Retry logic for failed purchase events.
- API documentation for analytics and conversions.

## Requirements
- PHP >= 8.1  
- Laravel >= 10  
- MySQL 8+  
- Composer  
- Node.js (for asset management)

## Setup
```bash
git clone <repo-url>
cd almeapp-laravel
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
