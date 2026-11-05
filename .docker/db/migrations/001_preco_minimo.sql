ALTER TABLE `produtos`
  ADD COLUMN `preco_compra` DECIMAL(10,2) DEFAULT 0.00 AFTER `preco`,
  ADD COLUMN `preco_minimo` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `preco_compra`,
  ADD KEY `idx_produto_nome` (`nome`),
  ADD KEY `idx_produto_codigo` (`codigo`);
