CREATE DATABASE IF NOT EXISTS db_sports_loan;
USE db_sports_loan;

DROP TABLE IF EXISTS loans;
DROP TABLE IF EXISTS borrowers;
DROP TABLE IF EXISTS equipment;

CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

CREATE TABLE borrowers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(15)
);

CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    borrower_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed','returned') NOT NULL DEFAULT 'borrowed',
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    FOREIGN KEY (borrower_id) REFERENCES borrowers(id)
);

INSERT INTO equipment (name, category, stock) VALUES
('Bola Futsal', 'Futsal', 5),
('Raket Badminton', 'Badminton', 8),
('Bola Basket', 'Basket', 4),
('Net Voli', 'Voli', 2);

INSERT INTO borrowers (name, nim, phone) VALUES
('Budi Santoso', '2300001', '081234567890'),
('Siti Aminah', '2300002', '081298765432');

INSERT INTO loans (equipment_id, borrower_id, quantity, loan_date, due_date, status) VALUES
(1, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'borrowed');
UPDATE equipment SET stock = stock - 1 WHERE id = 1;
