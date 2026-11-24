-- =====================================================================
-- Meu Bolso - script de criação do banco de dados
-- Banco: MySQL / MariaDB (testado no MariaDB 10.4, XAMPP)
-- Como usar: phpMyAdmin > Importar > escolher este arquivo > Executar
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `meu_bolso`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `meu_bolso`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `transacoes`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `imagens`;
DROP TABLE IF EXISTS `usuarios`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- usuarios: nivel 1 = usuario, 2 = gerente, 3 = administrador
-- senha_hash = SHA-256( SHA-256(login) + senha ), salt baseado no login
-- ---------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `senha_hash` CHAR(64) NOT NULL,
  `nivel` TINYINT(4) NOT NULL DEFAULT 1,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- imagens: biblioteca de imagens gerenciada pelo administrador (CRUD)
-- ---------------------------------------------------------------------
CREATE TABLE `imagens` (
  `id_imagem` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `id_transacao` INT(11) DEFAULT NULL,
  `titulo` VARCHAR(100) NOT NULL,
  `arquivo` VARCHAR(120) NOT NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_imagem`),
  KEY `fk_imagem_usuario` (`id_usuario`),
  KEY `fk_imagem_transacao` (`id_transacao`),
  CONSTRAINT `fk_imagem_usuario` FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- categorias: cada categoria pode ter uma imagem da biblioteca
-- ---------------------------------------------------------------------
CREATE TABLE `categorias` (
  `id_categoria` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `tipo` VARCHAR(10) NOT NULL,
  `id_imagem` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  KEY `fk_categoria_imagem` (`id_imagem`),
  CONSTRAINT `fk_categoria_imagem` FOREIGN KEY (`id_imagem`)
    REFERENCES `imagens` (`id_imagem`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- transacoes: lancamentos financeiros (somente do usuario nivel 1)
-- ---------------------------------------------------------------------
CREATE TABLE `transacoes` (
  `id_transacao` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `id_categoria` INT(11) NOT NULL,
  `tipo` VARCHAR(10) NOT NULL,
  `descricao` VARCHAR(150) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_transacao` DATE NOT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transacao`),
  KEY `fk_transacao_usuario` (`id_usuario`),
  KEY `fk_transacao_categoria` (`id_categoria`),
  CONSTRAINT `fk_transacao_usuario` FOREIGN KEY (`id_usuario`)
    REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `fk_transacao_categoria` FOREIGN KEY (`id_categoria`)
    REFERENCES `categorias` (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Biblioteca do administrador = imagens com id_transacao NULL
ALTER TABLE `imagens`
  ADD CONSTRAINT `fk_imagem_transacao` FOREIGN KEY (`id_transacao`)
    REFERENCES `transacoes` (`id_transacao`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- Dados iniciais: categorias
-- ---------------------------------------------------------------------
INSERT INTO `categorias` (`nome`, `tipo`) VALUES
  ('Salário', 'receita'),
  ('Freelance', 'receita'),
  ('Investimentos', 'receita'),
  ('Outras receitas', 'receita'),
  ('Alimentação', 'despesa'),
  ('Moradia', 'despesa'),
  ('Transporte', 'despesa'),
  ('Saúde', 'despesa'),
  ('Educação', 'despesa'),
  ('Lazer', 'despesa'),
  ('Outras despesas', 'despesa');

-- ---------------------------------------------------------------------
-- Dados iniciais: contas do primeiro acesso (senhas ja em hash)
-- As senhas de teste constam no README e no relatorio.
-- ---------------------------------------------------------------------
INSERT INTO `usuarios` (`nome`, `email`, `senha_hash`, `nivel`) VALUES
  ('Administrador', 'admin@meubolso.com', '88282f1b0ec32f34059359256cbb7955f1e7b86445981bc66a0493b55acdc2d2', 3),
  ('Gerente', 'gerente@meubolso.com', '57f08ccc66a99e54e605ea8f9ff5a7adaf00066bba04b7181beb053559a7727a', 2),
  ('Usuário Exemplo', 'usuario@meubolso.com', '7f6f7ad3273643f27bb392c79012013eee412e1bdedaf1bc9ea4bf4245fcc891', 1);