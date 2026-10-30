CREATE DATABASE IF NOT EXISTS meu_bolso
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE meu_bolso;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    CONSTRAINT chk_categoria_tipo
        CHECK (tipo IN ('receita', 'despesa'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transacoes (
    id_transacao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    descricao VARCHAR(150) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_transacao DATE NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_transacao_tipo
        CHECK (tipo IN ('receita', 'despesa')),
    CONSTRAINT chk_transacao_valor
        CHECK (valor > 0),
    CONSTRAINT fk_transacao_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_transacao_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE redefinicoes_senha (
    id_redefinicao INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expira_em DATETIME NOT NULL,
    usado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_redef_token (token_hash),
    CONSTRAINT fk_redef_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nome, tipo) VALUES
('Salário', 'receita'), ('Freelance', 'receita'),
('Investimentos', 'receita'), ('Outras receitas', 'receita'),
('Alimentação', 'despesa'), ('Moradia', 'despesa'),
('Transporte', 'despesa'), ('Saúde', 'despesa'),
('Educação', 'despesa'), ('Lazer', 'despesa'),
('Outras despesas', 'despesa');