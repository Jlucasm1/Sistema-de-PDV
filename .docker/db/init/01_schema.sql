-- ================================================================
-- PDV MASTER — Schema do Banco de Dados
-- Charset: utf8mb4 | Engine: InnoDB
-- ================================================================

CREATE DATABASE IF NOT EXISTS `pdvmaster`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `pdvmaster`;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------
-- clientes_segmentos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes_segmentos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- clientes_tipos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes_tipos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- empresas
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `empresas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `celular` VARCHAR(20) DEFAULT NULL,
  `id_segmento` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_empresa_segmento` (`id_segmento`),
  CONSTRAINT `fk_empresa_segmento`
    FOREIGN KEY (`id_segmento`) REFERENCES `clientes_segmentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- perfis
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfis` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- sexos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sexos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- usuarios
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL DEFAULT 1,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(60) DEFAULT NULL,
  `remember_expire_date` TIMESTAMP NULL DEFAULT NULL,
  `id_sexo` INT DEFAULT NULL,
  `id_perfil` INT DEFAULT NULL,
  `imagem` TEXT DEFAULT NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuario_email` (`email`),
  KEY `fk_usuario_empresa` (`id_empresa`),
  KEY `fk_usuario_sexo` (`id_sexo`),
  KEY `fk_usuario_perfil` (`id_perfil`),
  CONSTRAINT `fk_usuario_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_usuario_sexo`
    FOREIGN KEY (`id_sexo`) REFERENCES `sexos` (`id`),
  CONSTRAINT `fk_usuario_perfil`
    FOREIGN KEY (`id_perfil`) REFERENCES `perfis` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- produtos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `codigo` VARCHAR(100) DEFAULT NULL,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `preco_compra` DECIMAL(10,2) DEFAULT 0.00,
  `descricao` TEXT DEFAULT NULL,
  `imagem` TEXT DEFAULT NULL,
  `ativar_quantidade` TINYINT NOT NULL DEFAULT 0 COMMENT '1=Sim, 0=Não',
  `quantidade` INT DEFAULT 0,
  `mostrar_em_vendas` TINYINT DEFAULT 1 COMMENT '1=Sim, 0=Não',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_produto_empresa` (`id_empresa`),
  CONSTRAINT `fk_produto_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- meios_pagamentos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `meios_pagamentos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `legenda` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- vendas
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_meio_pagamento` INT NOT NULL,
  `id_empresa` INT NOT NULL,
  `id_produto` INT DEFAULT NULL,
  `preco` DECIMAL(10,2) DEFAULT 0.00,
  `quantidade` INT DEFAULT NULL,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valor_recebido` DECIMAL(10,2) DEFAULT NULL,
  `troco` DECIMAL(10,2) DEFAULT NULL,
  `data_compensacao` DATE DEFAULT NULL,
  `codigo_venda` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_venda_usuario` (`id_usuario`),
  KEY `fk_venda_pagamento` (`id_meio_pagamento`),
  KEY `fk_venda_empresa` (`id_empresa`),
  CONSTRAINT `fk_venda_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_venda_pagamento`
    FOREIGN KEY (`id_meio_pagamento`) REFERENCES `meios_pagamentos` (`id`),
  CONSTRAINT `fk_venda_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- clientes
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_cliente_tipo` INT NOT NULL,
  `id_cliente_segmento` INT DEFAULT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `cnpj` VARCHAR(20) DEFAULT NULL,
  `cpf` VARCHAR(15) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `celular` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cliente_empresa` (`id_empresa`),
  KEY `fk_cliente_tipo` (`id_cliente_tipo`),
  KEY `fk_cliente_segmento` (`id_cliente_segmento`),
  CONSTRAINT `fk_cliente_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_cliente_tipo`
    FOREIGN KEY (`id_cliente_tipo`) REFERENCES `clientes_tipos` (`id`),
  CONSTRAINT `fk_cliente_segmento`
    FOREIGN KEY (`id_cliente_segmento`) REFERENCES `clientes_segmentos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- clientes_enderecos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes_enderecos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_cliente` INT NOT NULL,
  `cep` VARCHAR(10) DEFAULT NULL,
  `endereco` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(60) NOT NULL,
  `cidade` VARCHAR(60) DEFAULT NULL,
  `estado` VARCHAR(2) DEFAULT NULL,
  `numero` INT DEFAULT NULL,
  `complemento` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_end_empresa` (`id_empresa`),
  KEY `fk_end_cliente` (`id_cliente`),
  CONSTRAINT `fk_end_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_end_cliente`
    FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- tipos_pdv
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipos_pdv` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- config_pdv
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `config_pdv` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_tipo_pdv` INT NOT NULL DEFAULT 2,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_config_empresa` (`id_empresa`),
  KEY `fk_config_tipo_pdv` (`id_tipo_pdv`),
  CONSTRAINT `fk_config_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_config_tipo_pdv`
    FOREIGN KEY (`id_tipo_pdv`) REFERENCES `tipos_pdv` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- log_acessos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_acessos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_empresa` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_usuario` (`id_usuario`),
  KEY `fk_log_empresa` (`id_empresa`),
  CONSTRAINT `fk_log_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_log_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- recuperacao_de_senha
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recuperacao_de_senha` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `hash` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- situacoes_pedidos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `situacoes_pedidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- pedidos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_cliente` INT DEFAULT NULL,
  `id_usuario` INT DEFAULT NULL,
  `id_endereco` INT DEFAULT NULL,
  `id_situacao` INT DEFAULT 1,
  `observacao` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pedido_empresa` (`id_empresa`),
  KEY `fk_pedido_situacao` (`id_situacao`),
  CONSTRAINT `fk_pedido_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_pedido_situacao`
    FOREIGN KEY (`id_situacao`) REFERENCES `situacoes_pedidos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- produtos_pedidos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos_pedidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pedido` INT NOT NULL,
  `id_produto` INT NOT NULL,
  `quantidade` INT NOT NULL DEFAULT 1,
  `preco` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pp_pedido` (`id_pedido`),
  KEY `fk_pp_produto` (`id_produto`),
  CONSTRAINT `fk_pp_pedido`
    FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`),
  CONSTRAINT `fk_pp_produto`
    FOREIGN KEY (`id_produto`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- categoria_fluxo_caixa
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categoria_fluxo_caixa` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(60) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- fluxo_caixa
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fluxo_caixa` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `id_categoria` INT DEFAULT NULL,
  `descricao` VARCHAR(100) DEFAULT NULL,
  `data` DATE DEFAULT NULL,
  `valor` DECIMAL(10,2) DEFAULT NULL,
  `tipo_movimento` TINYINT DEFAULT NULL COMMENT '0=Saída, 1=Entrada',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_fc_empresa` (`id_empresa`),
  KEY `fk_fc_categoria` (`id_categoria`),
  CONSTRAINT `fk_fc_empresa`
    FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id`),
  CONSTRAINT `fk_fc_categoria`
    FOREIGN KEY (`id_categoria`) REFERENCES `categoria_fluxo_caixa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- modulos
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `modulos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(60) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- produtos_categorias
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `produtos_categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_empresa` INT NOT NULL,
  `descricao` VARCHAR(60) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
