ALTER TABLE `produtos`
  ADD COLUMN `codigo_interno` VARCHAR(50) DEFAULT NULL AFTER `codigo`,
  ADD KEY `idx_produto_empresa_codigo_interno` (`id_empresa`, `codigo_interno`);

CREATE TABLE IF NOT EXISTS `catalogo_permissoes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `permissao` VARCHAR(60) NOT NULL DEFAULT 'gerenciar_catalogo_produtos',
  `concedido_por` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_catalogo_permissao_empresa` (`id_empresa`, `permissao`),
  KEY `idx_catalogo_permissao_usuario` (`id_usuario`),
  CONSTRAINT `fk_catalogo_permissao_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `produtos_historico` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_produto` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `operacao` VARCHAR(20) NOT NULL,
  `campo` VARCHAR(40) DEFAULT NULL,
  `valor_anterior` TEXT DEFAULT NULL,
  `valor_novo` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hist_empresa_produto` (`id_empresa`, `id_produto`),
  KEY `idx_hist_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
