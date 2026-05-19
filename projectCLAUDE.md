# ONLINE CAR RENT — FULL PROJECT PLAN & ARCHITECTURE

### (Detailed Technical Documentation for Development)

---

## 1. PROJECT OVERVIEW

An **Online Car Rental System** built in PHP MVC (no framework), running locally on **XAMPP**. Hosted on GitHub with one feature branch per task merged into `main` via PRs.

**Two user roles:**

- **ADMIN** — manages cars and members; views all orders; moderates all blog posts
- **MEMBER** — browses cars by category; places orders; pays; views rental history; posts and deletes own blog entries

**Tech stack:**

- Backend: PHP 8.x, PDO, sessions/cookies
- Frontend: HTML5, CSS3, vanilla JS (ES6), Fetch API for AJAX
- Database: MySQL via XAMPP (phpMyAdmin)
- Version control: Git + GitHub

---

## 2. TEAM & TASK ALLOCATION

| Student ID | Task   | Scope                                                                                       |
| ---------- | ------ | ------------------------------------------------------------------------------------------- |
| 23-54051-3 | Task 1 | Auth (register/login/logout/remember-me), Profile management, Home page, Category browsing  |
| 23-54040-3 | Task 2 | Admin — Car CRUD, Delete members, View all order history, Admin dashboard                   |
| 19-39469-1 | Task 3 | Member — Car selection, Order placement, Invoice (cancel/finalize), Payment, Rental history |
| 24-57937-2 | Task 4 | Blog — Create post, View all, Delete own (member), Delete any (admin)                       |

> All four tasks share one codebase, one database schema, and one GitHub repository. No code sharing between tasks except the shared schema.

---

## 3. DATABASE SCHEMA

One shared schema. **Do not alter table structure once agreed.**

```sql
-- users
CREATE TABLE users (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    role             ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    profile_picture  VARCHAR(255) DEFAULT NULL,
    address          TEXT DEFAULT NULL,
    phone            VARCHAR(20) DEFAULT NULL,
    remember_token   VARCHAR(64) DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- cars
CREATE TABLE cars (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100) NOT NULL,
    model               VARCHAR(100) NOT NULL,
    type                ENUM('Private car','Microbus','Pick-up','SUV','Sedan','Other') NOT NULL,
    price_per_day       DECIMAL(10,2) NOT NULL CHECK (price_per_day > 0),
    availability_status TINYINT(1) NOT NULL DEFAULT 1,
    image_path          VARCHAR(255) DEFAULT NULL,
    description         TEXT DEFAULT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- orders
CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    car_id          INT NOT NULL,
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,
    total_cost      DECIMAL(10,2) NOT NULL,
    status          ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    payment_method  VARCHAR(50) DEFAULT NULL,
    order_date      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id)  REFERENCES cars(id)  ON DELETE RESTRICT
);

-- payments
CREATE TABLE payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  ENUM('credit_card','bkash','nagad','bank_transfer','cash_on_delivery') NOT NULL,
    transaction_id  VARCHAR(100) DEFAULT NULL,
    payment_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- blogs
CREATE TABLE blogs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    content     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

> `rental_history` is **not a separate table** — it is derived by querying `orders` where `status = 'confirmed'`, joined with `cars`.

---

## 4. FILE & DIRECTORY ARCHITECTURE

```text
online-car-rent/
│
│   # ─── ROOT LEVEL ────────────────────────────────────────────────────────
├── .htaccess                        # Rewrites ALL traffic to public/ (Apache rule)
├── .gitignore                       # Ignores: /public/uploads/*, config/database.php, .DS_Store
├── README.md                        # Setup guide: XAMPP, DB import, virtual host config
│
│   # ─── PUBLIC WEB ROOT (only this folder is browser-accessible) ──────────
├── public/
│   ├── index.php                    # Front controller — bootstraps app, calls Router
│   ├── .htaccess                    # RewriteRule: everything → index.php (clean URLs)
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css             # Global styles: typography, colors, layout, cards
│   │   │   ├── auth.css             # Login / register page styles
│   │   │   └── admin.css            # Admin panel sidebar + dashboard styles
│   │   │
│   │   ├── js/
│   │   │   ├── validation.js        # Reusable client-side validation helpers
│   │   │   ├── auth.js              # JS validation for register + login forms
│   │   │   ├── cars.js              # JS validation for car form (admin)
│   │   │   ├── orders.js            # AJAX cost calculator; AJAX order cancel
│   │   │   └── blog.js              # AJAX post create; AJAX post delete + DOM refresh
│   │   │
│   │   └── images/
│   │       ├── logo.png             # Site logo
│   │       ├── hero-banner.jpg      # Home page hero image
│   │       └── placeholder/
│   │           ├── car.png          # Fallback car image
│   │           └── avatar.png       # Fallback profile picture
│   │
│   └── uploads/                     # Runtime-written; excluded from Git via .gitignore
│       ├── profiles/                # User profile pictures (JPEG/PNG ≤2MB)
│       └── cars/                    # Car listing images (JPEG/PNG ≤2MB)
│
│   # ─── APPLICATION CORE (not accessible from browser) ────────────────────
├── app/
│   │
│   ├── Controllers/
│   │   │
│   │   ├── Core/
│   │   │   ├── Router.php           # URL parser → maps GET/POST to Controller@method
│   │   │   ├── BaseController.php   # render(), redirect(), requireAuth(), requireRole()
│   │   │   ├── AuthMiddleware.php   # Guards: isLoggedIn(), isAdmin(), isMember()
│   │   │   └── Session.php          # session_start(), flash messages (set/get/clear)
│   │   │
│   │   ├── Api/                     # AJAX-only endpoints — always return JSON
│   │   │   ├── OrderApiController.php   # POST /api/orders/cost  → {total_cost}
│   │   │   │                            # POST /api/orders/cancel → {success, message}
│   │   │   ├── BlogApiController.php    # POST /api/blogs        → {success, html}
│   │   │   │                            # DELETE /api/blogs/{id} → {success, message}
│   │   │   └── MemberApiController.php  # DELETE /api/members/{id} → {success, message}
│   │   │
│   │   ├── AuthController.php       # GET/POST /register, /login, GET /logout
│   │   ├── HomeController.php       # GET / — featured cars + category bar
│   │   ├── ProfileController.php    # GET/POST /profile/edit, GET /profile/history
│   │   ├── CarController.php        # GET /cars, /cars/{id}, /cars?type=SUV (public)
│   │   ├── AdminCarController.php   # GET/POST /admin/cars, /admin/cars/create,
│   │   │                            #          /admin/cars/{id}/edit, POST /admin/cars/{id}/delete
│   │   ├── OrderController.php      # GET/POST /orders/create/{car_id}
│   │   │                            # GET /orders/{id}/invoice
│   │   │                            # POST /orders/{id}/finalize
│   │   │                            # GET/POST /orders/{id}/payment
│   │   │                            # GET /orders/{id}/success
│   │   ├── AdminController.php      # GET /admin/dashboard
│   │   │                            # GET /admin/members (list + delete UI)
│   │   │                            # GET /admin/orders  (all order history)
│   │   └── BlogController.php       # GET /blog (list; create form for logged-in)
│   │
│   ├── Models/
│   │   │
│   │   ├── Core/
│   │   │   ├── Database.php         # PDO singleton; connect(), query(), exec()
│   │   │   └── BaseModel.php        # find(), findAll(), insert(), update(), delete()
│   │   │
│   │   ├── User.php                 # findByEmail(), createUser(), updateProfile(),
│   │   │                            # updatePassword(), setRememberToken(), getAllMembers()
│   │   ├── Car.php                  # getFeatured(), getByType(), getById(), createCar(),
│   │   │                            # updateCar(), deleteCar(), getDistinctTypes()
│   │   ├── Order.php                # createOrder(), getById(), updateStatus(),
│   │   │                            # getByUser(), getAll(), calcCost()
│   │   ├── Payment.php              # createPayment(), getByOrder()
│   │   └── Blog.php                 # createPost(), getAll(), getById(),
│   │                                # deletePost(), isOwner()
│   │
│   └── Views/
│       │
│       ├── layouts/
│       │   ├── main.php             # Public/member layout: navbar + flash + content + footer
│       │   └── admin.php            # Admin layout: sidebar + topbar + content
│       │
│       ├── partials/
│       │   ├── navbar.php           # Role-aware nav links (guest / member / admin)
│       │   ├── footer.php           # Site footer
│       │   ├── flash.php            # Renders session flash messages (success / error / info)
│       │   └── car-card.php         # Reusable car card partial (used on home + cars list)
│       │
│       ├── auth/
│       │   ├── login.php            # Login form (email, password, remember-me checkbox)
│       │   └── register.php         # Registration form (name, email, password, role, address, phone)
│       │
│       ├── home/
│       │   └── index.php            # Hero section + featured cars + category filter bar
│       │
│       ├── profile/
│       │   ├── edit.php             # Profile update form (name, email, phone, address, picture, password)
│       │   └── history.php          # Member rental history table (confirmed + cancelled orders)
│       │
│       ├── cars/
│       │   ├── index.php            # Car listing grid (filter by category, search)
│       │   └── show.php             # Single car detail page + "Rent This Car" CTA
│       │
│       ├── admin/
│       │   ├── dashboard.php        # Stats: total cars / members / orders / blogs
│       │   ├── members.php          # All members table + delete button per row
│       │   ├── orders.php           # All orders table (filter by status/date)
│       │   ├── cars/
│       │   │   ├── index.php        # Admin car list with edit/delete actions
│       │   │   └── form.php         # Create + Edit car form (shared, pre-filled on edit)
│       │   └── blog/
│       │       └── index.php        # Admin blog list with delete-any button
│       │
│       ├── orders/
│       │   ├── create.php           # Date picker form; JS live cost calculator
│       │   ├── invoice.php          # Order summary + Cancel / Finalize buttons
│       │   ├── payment.php          # Payment method selector (credit card / bKash / Nagad etc.)
│       │   └── success.php          # Confirmation page with order summary
│       │
│       └── blog/
│           ├── index.php            # All blog posts + create-post form (for logged-in users)
│           └── _post.php            # Single post partial — injected by AJAX on new post / delete
│
│   # ─── CONFIG (never committed with real credentials) ───────────────────
├── config/
│   ├── app.php                      # APP_NAME, BASE_URL, UPLOAD_MAX_SIZE, timezone
│   ├── database.php                 # DB_HOST, DB_NAME, DB_USER, DB_PASS (gitignored)
│   ├── database.example.php         # Safe template committed to Git (no real credentials)
│   └── routes.php                   # Full URL → Controller@method routing table
│
│   # ─── DATABASE MIGRATIONS & SEEDS ──────────────────────────────────────
└── database/
    ├── schema.sql                   # Full CREATE TABLE statements (import via phpMyAdmin)
    └── seed.sql                     # Sample data: 1 admin, 3 members, 5 cars, sample orders/blogs
```

---

## 5. URL ROUTES MAP

| Method | URL                       | Controller@Method                | Auth Required |
| ------ | ------------------------- | -------------------------------- | ------------- |
| GET    | `/`                       | `HomeController@index`           | No            |
| GET    | `/register`               | `AuthController@showRegister`    | No            |
| POST   | `/register`               | `AuthController@register`        | No            |
| GET    | `/login`                  | `AuthController@showLogin`       | No            |
| POST   | `/login`                  | `AuthController@login`           | No            |
| GET    | `/logout`                 | `AuthController@logout`          | Yes           |
| GET    | `/profile/edit`           | `ProfileController@edit`         | Yes           |
| POST   | `/profile/edit`           | `ProfileController@update`       | Yes           |
| GET    | `/profile/history`        | `ProfileController@history`      | Member        |
| GET    | `/cars`                   | `CarController@index`            | No            |
| GET    | `/cars/{id}`              | `CarController@show`             | No            |
| GET    | `/orders/create/{car_id}` | `OrderController@create`         | Member        |
| POST   | `/orders/create/{car_id}` | `OrderController@store`          | Member        |
| GET    | `/orders/{id}/invoice`    | `OrderController@invoice`        | Member        |
| POST   | `/orders/{id}/finalize`   | `OrderController@finalize`       | Member        |
| GET    | `/orders/{id}/payment`    | `OrderController@payment`        | Member        |
| POST   | `/orders/{id}/payment`    | `OrderController@processPayment` | Member        |
| GET    | `/orders/{id}/success`    | `OrderController@success`        | Member        |
| GET    | `/blog`                   | `BlogController@index`           | No            |
| GET    | `/admin/dashboard`        | `AdminController@dashboard`      | Admin         |
| GET    | `/admin/members`          | `AdminController@members`        | Admin         |
| GET    | `/admin/orders`           | `AdminController@orders`         | Admin         |
| GET    | `/admin/cars`             | `AdminCarController@index`       | Admin         |
| GET    | `/admin/cars/create`      | `AdminCarController@create`      | Admin         |
| POST   | `/admin/cars/create`      | `AdminCarController@store`       | Admin         |
| GET    | `/admin/cars/{id}/edit`   | `AdminCarController@edit`        | Admin         |
| POST   | `/admin/cars/{id}/edit`   | `AdminCarController@update`      | Admin         |
| POST   | `/admin/cars/{id}/delete` | `AdminCarController@destroy`     | Admin         |

---

## 6. AJAX ENDPOINTS (JSON API)

Each endpoint returns `Content-Type: application/json`. All validate session server-side before acting.

| Method | Endpoint                  | Handler                       | Response                           | Used By |
| ------ | ------------------------- | ----------------------------- | ---------------------------------- | ------- |
| POST   | `/api/orders/cost`        | `OrderApiController@calcCost` | `{total_cost: float}`              | Task 3  |
| POST   | `/api/orders/{id}/cancel` | `OrderApiController@cancel`   | `{success: bool, message: string}` | Task 3  |
| POST   | `/api/blogs`              | `BlogApiController@store`     | `{success: bool, html: string}`    | Task 4  |
| DELETE | `/api/blogs/{id}`         | `BlogApiController@destroy`   | `{success: bool, message: string}` | Task 4  |
| DELETE | `/api/members/{id}`       | `MemberApiController@destroy` | `{success: bool, message: string}` | Task 2  |

---

## 7. GRADING CHECKLIST (10 Criteria × 4 Tasks)

Track each criterion per task as development progresses.

| #   | Criterion                                                       | Task 1 | Task 2 | Task 3 | Task 4 |
| --- | --------------------------------------------------------------- | ------ | ------ | ------ | ------ |
| 1   | Basic Web Security (SQL injection, XSS, CSRF, hashed passwords) | ☐      | ☐      | ☐      | ☐      |
| 2   | UI — clean, responsive, user-friendly                           | ☐      | ☐      | ☐      | ☐      |
| 3   | Feature completeness — all assigned requirements work           | ☐      | ☐      | ☐      | ☐      |
| 4   | DB — correct schema usage, relationships, integrity             | ☐      | ☐      | ☐      | ☐      |
| 5   | Auth — session management, role-based access, remember-me       | ☐      | ☐      | ☐      | ☐      |
| 6   | MVC — clean separation of concerns                              | ☐      | ☐      | ☐      | ☐      |
| 7   | JS validation on all forms                                      | ☐      | ☐      | ☐      | ☐      |
| 8   | PHP server-side validation on all inputs                        | ☐      | ☐      | ☐      | ☐      |
| 9   | AJAX — at least one JSON endpoint per task                      | ☐      | ☐      | ☐      | ☐      |
| 10  | Git — feature branches, ≥3 commits each, merge via PR           | ☐      | ☐      | ☐      | ☐      |

---

## 8. GIT WORKFLOW

**Branch naming convention (as required by faculty):**

feature/task1-23-54051-3
feature/task2-23-54040-3
feature/task3-19-39469-1
feature/task4-24-57937-2

**Flow:**

main (protected — no direct push)
└── feature/task1-23-54051-3 ← Task 1 development
└── feature/task2-23-54040-3 ← Task 2 development
└── feature/task3-19-39469-1 ← Task 3 development
└── feature/task4-24-57937-2 ← Task 4 development

**Commit message convention:**

Each student (task): minimum **3 meaningful commits** on their feature branch before opening a PR to `main`.

**`.gitignore` must include:**

/public/uploads/profiles/_
/public/uploads/cars/_
/config/database.php
.DS_Store
\*.log

---

## 9. XAMPP LOCAL SETUP

1. Clone repo into `htdocs/online-car-rent/`
2. Create virtual host in Apache `httpd-vhosts.conf`:

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

3. Add `127.0.0.1 carrent.local` to OS hosts file
4. Import `database/schema.sql` via phpMyAdmin → create DB `online_car_rent`
5. Import `database/seed.sql` for sample data
6. Copy `config/database.example.php` → `config/database.php` and fill in credentials
7. Ensure `public/uploads/profiles/` and `public/uploads/cars/` exist and are writable

---

## 10. BUILD PHASES

| Phase                         | Scope                                                                                            | Depends On |
| ----------------------------- | ------------------------------------------------------------------------------------------------ | ---------- |
| **Phase 1 — Foundation**      | Router, BaseController, Database PDO singleton, Session, config files, layouts, schema.sql       | Nothing    |
| **Phase 2 — Auth**            | Register, Login, Logout, Remember Me, role middleware                                            | Phase 1    |
| **Phase 3 — Home + Browse**   | Featured cars query, category bar, car listing, car detail page                                  | Phase 2    |
| **Phase 4 — Admin Panel**     | Car CRUD + image upload, member delete (AJAX), order history view, dashboard stats               | Phase 3    |
| **Phase 5 — Member Ordering** | Order form, AJAX cost calc, invoice, cancel (AJAX), finalize, payment, success, rental history   | Phase 3    |
| **Phase 6 — Blog**            | Blog list, AJAX create post, AJAX delete, admin delete-any                                       | Phase 2    |
| **Phase 7 — Polish**          | CSS responsiveness, all JS validation passes, XSS escaping audit, CSRF tokens, seed data, README | All        |

---

## 11. SECURITY IMPLEMENTATION NOTES

- **SQL injection** — PDO prepared statements on every query, zero string-concatenated SQL
- **XSS** — `htmlspecialchars()` on every `echo` of user-supplied data in views
- **CSRF** — generate a `$_SESSION['csrf_token']` per form; validate on every POST before processing
- **Passwords** — `password_hash($pass, PASSWORD_BCRYPT)` on store; `password_verify()` on login
- **File uploads** — validate MIME type via `finfo_file()` (not extension alone); enforce ≤2MB; store with `uniqid()` filename, never original filename
- **Role gates** — `AuthMiddleware::requireRole('admin')` called at the top of every admin controller method; redirect to login if check fails
- **Remember Me** — store a secure random token (`bin2hex(random_bytes(32))`) in DB; set HttpOnly cookie; validate token on each request before auto-login
