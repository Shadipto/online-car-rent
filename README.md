# Online Car Rent

PHP 8 MVC online car rental system for XAMPP. The project is fully assembled with all four assigned task areas: authentication/profile/home browsing, admin management, member ordering/payment, and blogs.

## Setup

1. Place the project folder at `C:/xampp/htdocs/online-car-rent/`.
2. Start Apache and MySQL from XAMPP.
3. Create a MySQL database named `online_car_rent`.
4. Import `database/schema.sql` in phpMyAdmin.
5. Copy `config/database.example.php` to `config/database.php` and set local credentials. XAMPP defaults are usually `root` with an empty password.
6. Ensure these folders exist and are writable:
   - `public/uploads/profiles/`
   - `public/uploads/cars/`
7. Recommended Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName carrent.local
    DocumentRoot "C:/xampp/htdocs/online-car-rent/public"
    <Directory "C:/xampp/htdocs/online-car-rent/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

8. Add `127.0.0.1 carrent.local` to the OS hosts file.
9. Visit `http://carrent.local`.

The app also includes root and public `.htaccess` rewrite rules. If a virtual host is not configured, make sure Apache rewrite support is enabled and browse through the `public` front controller path.

## Feature Summary

### Task 1: Auth, Profile, Home, Browse

- Admin/member registration with unique email validation and hashed passwords.
- Login, logout, session-based authentication, and remember-me auto login.
- Profile editing for both roles, including profile picture upload and password change.
- Home page with featured cars and distinct category links.
- Public car browsing by category and car detail pages.
- Role-aware navigation.
- Browser-side validation for auth/profile forms.

### Task 2: Admin

- Admin dashboard with counts for cars, members, orders, and blogs.
- Admin-only car CRUD for name, model, type, price per day, availability, description, and optional JPEG/PNG image upload.
- Car edit forms pre-fill existing data.
- Car deletion removes the uploaded image and is blocked when existing orders reference the car.
- Member list with AJAX delete.
- All rent order history with member name, car details, dates, total cost, status, and payment method.
- Browser-side validation for car forms.

### Task 3: Member Orders

- Member-only order creation from car detail pages.
- Server-side and browser-side date validation.
- Live AJAX rental cost calculation.
- Invoice page with cancel/finalize actions.
- AJAX order cancellation updates status to `cancelled`.
- Payment methods: `credit_card`, `bkash`, `nagad`, `bank_transfer`, `cash_on_delivery`.
- Successful payment creates a payment row and confirms the order.
- Member rental history shows confirmed and cancelled orders.

### Task 4: Blog

- Blog page visible to guests, members, and admins.
- Logged-in members/admins can create posts with title and content.
- AJAX blog creation refreshes the list without a full reload.
- Members can delete only their own posts.
- Admins can delete any post.
- AJAX delete refreshes the list without a full reload.
- No comments section.

## Route Summary

### Public/Auth

- `GET /`
- `GET /register`
- `POST /register`
- `GET /login`
- `POST /login`
- `GET /logout`
- `GET /cars`
- `GET /cars/{id}`
- `GET /blog`

### Profile/Member

- `GET /profile/edit`
- `POST /profile/edit`
- `GET /profile/history`
- `GET /orders/create/{car_id}`
- `POST /orders/create/{car_id}`
- `GET /orders/{id}/invoice`
- `POST /orders/{id}/finalize`
- `GET /orders/{id}/payment`
- `POST /orders/{id}/payment`
- `GET /orders/{id}/success`

### Admin

- `GET /admin/dashboard`
- `GET /admin/members`
- `GET /admin/orders`
- `GET /admin/cars`
- `GET /admin/cars/create`
- `POST /admin/cars/create`
- `GET /admin/cars/{id}/edit`
- `POST /admin/cars/{id}/edit`
- `POST /admin/cars/{id}/delete`

### JSON API

- `GET /api/cars`
- `POST /api/orders/cost`
- `POST /api/orders/{id}/cancel`
- `POST /api/blogs`
- `DELETE /api/blogs/{id}`
- `DELETE /api/members/{id}`

## Security Baseline

- PDO prepared statements are used for database queries.
- Passwords are stored with `password_hash()` and checked with `password_verify()`.
- Sessions power authentication and role checks.
- Admin/member routes are protected through route metadata and middleware.
- CSRF tokens are used on POST/DELETE write actions.
- Views escape output with `e()` / `htmlspecialchars()`.
- Profile and car uploads validate MIME type and enforce a 2MB limit.
- `config/database.php` is ignored by Git.
- Runtime uploads are ignored except `.gitkeep` placeholders.

## Final Notes

- The shared database schema in `database/schema.sql` has not been changed.
- Rental history is derived from `orders`; there is no separate rental history table.
- Car deletion is intentionally blocked when orders exist because the schema uses `ON DELETE RESTRICT` for `orders.car_id`.
- Member deletion relies on the schema cascade rules for related user data.
- Seed data is not included yet, so create users and cars through the app or insert sample data manually for testing.
