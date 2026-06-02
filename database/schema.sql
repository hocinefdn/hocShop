CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion d'un utilisateur de test (password123 haché)
INSERT INTO users (email, password) 
VALUES ('hocine@wshop.fr', '$2y$12$RxN8F2/aZQDF0XEpjWbue.DO4njredx9xRU845TxYnOghzurK4/tW');

CREATE TABLE IF NOT EXISTS `stores` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `postal_code` VARCHAR(10) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;