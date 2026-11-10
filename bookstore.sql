-- ============================================================
-- Database: bookstore
-- SQL file for MySQL / XAMPP
-- ============================================================

-- Hapus database jika sudah ada (untuk development ulang)
DROP DATABASE IF EXISTS bookstore;
CREATE DATABASE bookstore
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE bookstore;

-- ============================================================
-- TABEL: users
-- ============================================================
CREATE TABLE users (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL: categories
-- ============================================================
CREATE TABLE categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)  NOT NULL,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL: books
-- ============================================================
CREATE TABLE books (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(255)  NOT NULL,
    author       VARCHAR(100)  NOT NULL,
    category_id  INT UNSIGNED DEFAULT NULL,
    price        DECIMAL(10,2) NOT NULL,
    description  TEXT          NOT NULL,
    image        VARCHAR(255)  DEFAULT 'no-image.jpg',
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL: orders
-- ============================================================
CREATE TABLE orders (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED DEFAULT NULL,
    name         VARCHAR(100)  NOT NULL,
    email        VARCHAR(100)  NOT NULL,
    address      TEXT          NOT NULL,
    phone        VARCHAR(20)   NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status       ENUM('pending','processing','shipped','delivered') DEFAULT 'pending',
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABEL: order_details
-- ============================================================
CREATE TABLE order_details (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id   INT UNSIGNED NOT NULL,
    book_id    INT UNSIGNED,
    quantity   INT          NOT NULL DEFAULT 1,
    price      DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATA AWAL: KATEGORI
-- ============================================================
INSERT INTO categories (name) VALUES
    ('Fiksi'),
    ('Non-Fiksi'),
    ('Teknologi'),
    ('Bisnis'),
    ('Pengembangan Diri');

-- ============================================================
-- DATA AWAL: BUKU
-- ============================================================
INSERT INTO books (title, author, category_id, price, description, image) VALUES
    ('The Great Gatsby', 'F. Scott Fitzgerald', 1, 85000.00,
     'The Great Gatsby is a novel written by F. Scott Fitzgerald that follows a cast of characters who "lived and loved" in 1900s New York. It is one of the greatest American novels, exploring themes of decadence, idealism, and the American Dream.',
     'great-gatsby.jpg'),
    ('To Kill a Mockingbird', 'Harper Lee', 1, 95000.00,
     'A story of growing up during the racially charged atmosphere of the 1930s in the fictional town of Maycomb, Alabama. Through the eyes of young Scout Finch, we witness issues of racial injustice and moral growth.',
     'mockingbird.jpg'),
    ('Clean Code', 'Robert C. Martin', 3, 150000.00,
     'A handbook of agile software craftsmanship. Even if you are not a software engineer yet, Clean Code will change the way you think about programming. It provides rules and examples for writing code that is easy to read and maintain.',
     'clean-code.jpg'),
    ('Atomic Habits', 'James Clear', 5, 110000.00,
     'An Easy & Proven Way to Build Good Habits & Break Bad Ones. This book offers practical strategies for making tiny changes that compound into remarkable results.',
     'atomic-habits.jpg'),
    ('Rich Dad Poor Dad', 'Robert Kiyosaki', 4, 75000.00,
     'The book explores the philosophical, social, and political roots of the Iraq War (as its main theme). Through the use of the two central figures in the narrative - the poor dad and the rich dad - it contrasts two ways of thinking about money.',
     'rich-dad.jpg'),
    ('Sapiens', 'Yuval Noah Harari', 2, 120000.00,
     'A brief history of humankind. From the emergence of Homo sapiens in Africa to the present, Yuval Noah Harari tells the story of how our species came to dominate the planet.',
     'sapiens.jpg'),
    ('The Psychology of Money', 'Morgan Housel', 4, 90000.00,
     'Timeless Lessons on Wealth, Greed, and the Mystery of Money. The psychology of money is about the influence that emotions and personal beliefs have on the management of money.',
     'psychology-money.jpg'),
    ('The Lean Startup', 'Eric Ries', 3, 130000.00,
     'The Lean Startup methodology is a scientific approach to creating and managing successful startups. This book teaches entrepreneurs how to gather validated learning to understand what customers really want.',
     'lean-startup.jpg');

-- ============================================================
-- DATA AWAL: ADMIN USER
-- Password untuk admin123 (di-hash dengan bcrypt)
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
    ('Admin Bookstore', 'admin@bookstore.com', '$2y$10$uMl7kIXJFx6wDWSiHQw5iOjO8rpAoXpYgViiZ1YS3/TUSIvne9kye', 'admin');

-- ============================================================
-- DATA AWAL: REGULAR USER
-- Password untuk user123
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
    ('John Doe', 'user@bookstore.com', '$2y$10$wKJtzua3lM5ydKwFemgI0u0bF4NP64b9H8dEIJbPRJZdQ/XnLPmNK', 'user');
