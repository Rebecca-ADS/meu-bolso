
-- 1. Criar o banco de dados
CREATE DATABASE IF NOT EXISTS meu_bolso;

-- 2. Selecionar o banco
USE meu_bolso;

-- 3. Tabela de usuários
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabela de categorias
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    tipo VARCHAR(10) NOT NULL,
    CONSTRAINT chk_categoria_tipo
        CHECK (tipo IN ('receita', 'despesa'))
);

-- 5. Tabela de transações
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
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario),

    CONSTRAINT fk_transacao_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
<<<<<<< HEAD
);
=======
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS redefinicoes_senha (
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
>>>>>>> c547271a0f35ca0e5f79fa680ab6064718e9c1f6
