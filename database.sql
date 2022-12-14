
CREATE TABLE voucher (
    id INT AUTO_INCREMENT NOT NULL,
    amount NUMERIC(5, 2) NOT NULL,
    is_used TINYINT(1) NOT NULL,
    expire_date DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

CREATE TABLE `order` (
    id INT AUTO_INCREMENT NOT NULL,
    original_amount NUMERIC(5, 2) NOT NULL,
    amount NUMERIC(5, 2) NOT NULL,
    voucher_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    INDEX voucher_idx (voucher_id),
    PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;