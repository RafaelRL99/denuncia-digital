
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS denuncia_digital
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE denuncia_digital;

CREATE TABLE IF NOT EXISTS denuncias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(80) NOT NULL,
  plataforma VARCHAR(80) NOT NULL,
  descricao TEXT NOT NULL,
  link_referencia VARCHAR(255) NULL,
  consentimento TINYINT(1) NOT NULL DEFAULT 0,
  data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO denuncias (tipo, plataforma, descricao, link_referencia, consentimento) VALUES
('Discriminação', 'Rede social', 'Publicação com conteúdo discriminatório contra grupo vulnerável.', 'https://exemplo.com/publicacao-1', 1),
('Ameaça', 'Aplicativo de mensagem', 'Mensagem com intimidação e ameaça em grupo digital.', NULL, 1),
('Exposição indevida', 'Site', 'Divulgação de imagem sem consentimento da pessoa envolvida.', 'https://exemplo.com/publicacao-2', 1),
('Discurso de ódio', 'Rede social', 'Comentários ofensivos direcionados a comunidade específica.', NULL, 1);
