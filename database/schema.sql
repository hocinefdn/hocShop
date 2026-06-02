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


-- Insert realistic test stores with different cities and states
INSERT INTO stores (name, address, postal_code, city, is_active) VALUES
('WSHOP Paris Haussmann', '42 Boulevard Haussmann', '75009', 'Paris', 1),
('WSHOP Paris Marais', '12 Rue des Francs Bourgeois', '75003', 'Paris', 1),
('WSHOP Lyon Presqu\'ile', '15 Rue de la République', '69002', 'Lyon', 1),
('WSHOP Lyon Part-Dieu', 'Centre Commercial Part-Dieu', '69003', 'Lyon', 0),
('WSHOP Marseille Vieux-Port', '28 Quai du Port', '13002', 'Marseille', 1),
('WSHOP Bordeaux Lac', 'Avenue des Quarante Journaux', '33300', 'Bordeaux', 0),
('WSHOP Lille Grand Place', '5 Place du Général de Gaulle', '59000', 'Lille', 1);