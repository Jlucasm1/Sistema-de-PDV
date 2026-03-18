-- ================================================================
-- PDV MASTER — Dados Iniciais (Seed)
-- ================================================================

USE `pdvmaster`;

-- Segmentos
INSERT IGNORE INTO `clientes_segmentos` (`id`, `descricao`) VALUES
(1, 'Restaurante'), (2, 'Hamburgueria'), (3, 'Pizzaria'),
(4, 'Loja de Roupas'), (5, 'Farmácia'), (6, 'Supermercado'),
(7, 'Informática'), (8, 'Serviços Gerais'), (9, 'Pet Shop'),
(10, 'Padaria'), (11, 'Açougue'), (12, 'Outros');

-- Tipos de cliente
INSERT IGNORE INTO `clientes_tipos` (`id`, `descricao`) VALUES
(1, 'Pessoa Física'), (2, 'Pessoa Jurídica');

-- Empresa padrão
INSERT IGNORE INTO `empresas` (`id`, `nome`, `email`, `telefone`, `celular`, `id_segmento`) VALUES
(1, 'PDV MASTER', 'admin@pdvmaster.com.br', '(00) 0000-0000', '(00) 00000-0000', 8);

-- Perfis
INSERT IGNORE INTO `perfis` (`id`, `descricao`) VALUES
(1, 'Super Admin'), (2, 'Administrador'), (3, 'Operador'), (4, 'Vendedor'), (5, 'Gerente');

-- Sexos
INSERT IGNORE INTO `sexos` (`id`, `descricao`) VALUES
(1, 'Masculino'), (2, 'Feminino'), (3, 'Prefiro não informar');

-- Meios de pagamento
INSERT IGNORE INTO `meios_pagamentos` (`id`, `legenda`) VALUES
(1, 'Dinheiro'), (2, 'Cartão de Crédito'),
(3, 'Cartão de Débito'), (4, 'Boleto'), (5, 'Pix');

-- Tipos PDV
INSERT IGNORE INTO `tipos_pdv` (`id`, `descricao`) VALUES
(1, 'PDV Padrão'), (2, 'PDV Diferencial');

-- Config PDV padrão
INSERT IGNORE INTO `config_pdv` (`id`, `id_empresa`, `id_tipo_pdv`) VALUES
(1, 1, 2);

-- Situações de pedidos
INSERT IGNORE INTO `situacoes_pedidos` (`id`, `descricao`) VALUES
(1, 'Aguardando'), (2, 'Em Preparo'), (3, 'Saiu para Entrega'),
(4, 'Entregue'), (5, 'Cancelado');

-- Categoria padrão fluxo de caixa
INSERT IGNORE INTO `categoria_fluxo_caixa` (`id`, `descricao`) VALUES
(1, 'Operacional'), (2, 'Financeiro'), (3, 'Outros');

-- ================================================================
-- USUÁRIO ADMINISTRADOR PADRÃO
-- Senha: admin123
-- Hash: sha1("admin123" + salt configurado no .env)
-- IMPORTANTE: Troque a senha após o primeiro login!
-- ================================================================
INSERT IGNORE INTO `usuarios`
  (`id`, `id_empresa`, `nome`, `email`, `password`, `id_sexo`, `id_perfil`, `status`)
VALUES (
  1, 1, 'Administrador', 'admin@pdvmaster.com.br',
  SHA1(CONCAT('admin123', 'pdvmaster@2024#secure_salt_key', REVERSE('pdvmaster@2024#secure_salt_key'))),
  1, 1, 1
);
