intent-discounting-frontend
This repository contains the source code for almeapp-laravel, a robust e-commerce application built with the Laravel framework. The application is designed to function as an integrated tool for managing user interactions, orders, and analytics,  connecting to a platform Shopify

Features

Shopify Integration: Seamlessly integrates with Shopify stores to handle various e-commerce workflows, including order processing, discount management, and webhook events.

User & Shop Management: Maintains a comprehensive record of identified users, their browsing habits, and their relationship with specific shops. The system tracks user activities like last_visited, added_to_cart, and purchased to build detailed user profiles.

Dynamic Analytics: Gathers and processes detailed analytics on user behavior and engagement. It records impressions, clicks on notifications, coupon redemptions, and contact form submissions.

Discount and Pricing Rules: Provides a system for creating and managing flexible price rules and discount codes. This includes setting coupon validity and minimum order values.

User Segmentation: Offers a powerful mechanism for segmenting users based on various criteria and rules, such as lastSeen-filter and custom rule definitions.

Product Recommendations: Supports product recommendation logic based on user data, with settings for user_liked, crowd_fav, and prev_browsing.

Webhook & Event Handling: Includes a system for processing and logging Shopify webhook events and a mechanism for retrying failed purchase events.

Database Schema
The core functionality of the application is powered by a well-structured database schema. Key tables include:

identified_users: Stores user-specific data and a history of their engagement.

shopify_orders: Contains detailed information about orders synchronized from Shopify.

alme_click_analytics: Records user interactions with notifications and coupons.

price_rules & discount_codes: Manages the application's discount and pricing logic.

segment_rules: Stores the custom rules used for user segmentation.

Routes
The application exposes a number of routes, primarily for testing and internal functionality:

API Routes (routes/web.php):

/testFullOrder/{id}: Endpoint to test full order data retrieval.

/testCustomers: Endpoint to test customer data.

/testPurchaseEvent: Endpoint for testing purchase event handling.

/testAlmePayload/{id}: Endpoint to test the Alme payload for a specific ID.

/segment_list: Retrieves a list of user segments.

/sampleMinOrderCoupon/{id}: Tests coupon generation with a minimum order value.

/createDiscountCode: Endpoint for testing discount code creation.

/checkStoreInstallAndScript: Checks for a store's installation and script status.

User API Routes (routes/api.php):

/user: Returns the authenticated user's data (requires auth:sanctum middleware).

Getting Started
To get a local copy of this project up and running, follow these steps:

Clone the repository:

git clone [your-repo-url]
cd almeapp-laravel

Install dependencies:

composer install

Set up the environment file:

Copy .env.example to .env.

Configure your database and other services in the .env file.

Run database migrations:

php artisan migrate

Serve the application:

php artisan serve
