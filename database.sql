CREATE DATABASE IF NOT EXISTS residential_tedesco;
USE residential_tedesco;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL, -- Ex: "Sobrado 01"
    cpf VARCHAR(20) NOT NULL UNIQUE, -- Pode guardar CPF ou CNPJ ou identificador
    senha_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'normal') DEFAULT 'normal',
    unidade VARCHAR(50), -- Opcional, para "01", "02", etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Quem pagou (Sobrado)
    descricao VARCHAR(255) NOT NULL, -- Pode ser "Condomínio Jan/2026"
    valor DECIMAL(10, 2) NOT NULL,
    obs VARCHAR(255), -- Adicionado para observações (ex: "NP", "Parcial")
    categoria VARCHAR(100),
    data DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Quem registrou (Admin)
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    obs VARCHAR(255), -- Adicionado para observações
    categoria VARCHAR(100),
    data DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Padrao
INSERT INTO users (nome, cpf, senha_hash, role) VALUES 
('Administrador', 'admin', '$2y$10$8.X/././././././././././././././././././././././', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Sobrados Exemplo (Opcional, mas útil para teste imediato)
-- Senha padrão para todos: 123456 (hash: $2y$10$3...)
-- INSERT INTO users (nome, cpf, senha_hash, role, unidade) VALUES 
-- ('Sobrado 01', 'sobrado01', '$2y$10$3Li...', 'normal', '01');
