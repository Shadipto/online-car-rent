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

CREATE TABLE payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  ENUM('credit_card','bkash','nagad','bank_transfer','cash_on_delivery') NOT NULL,
    transaction_id  VARCHAR(100) DEFAULT NULL,
    payment_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE blogs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    content     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
