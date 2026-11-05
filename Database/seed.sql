-- 
-- PDV MASTER — Seed: Dados iniciais obrigatórios para o sistema funcionar
-- 
USE pdv_master;

-- Perfis de acesso
INSERT IGNORE INTO `perfis` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', NOW(), NOW()),
(2, 'Administrador', NOW(), NOW()),
(3, 'Operador', NOW(), NOW()),
(4, 'Vendedor', NOW(), NOW()),
(5, 'Gerente', NOW(), NOW());

-- Sexos
INSERT IGNORE INTO `sexos` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Masculino', NOW(), NOW()),
(2, 'Feminino', NOW(), NOW()),
(3, 'Outro', NOW(), NOW());

-- Meios de pagamento
INSERT IGNORE INTO `meios_pagamentos` (`id`, `legenda`, `created_at`, `updated_at`) VALUES
(1, 'Dinheiro', NOW(), NOW()),
(2, 'Cartão de Crédito', NOW(), NOW()),
(3, 'Cartão de Débito', NOW(), NOW()),
(4, 'Boleto', NOW(), NOW()),
(5, 'Pix', NOW(), NOW());

-- Tipos de PDV
INSERT IGNORE INTO `tipos_pdv` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Padrão', NOW(), NOW()),
(2, 'Diferencial', NOW(), NOW());

-- Segmentos de clientes / empresas
INSERT IGNORE INTO `clientes_segmentos` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Varejo', NOW(), NOW()),
(2, 'Atacado', NOW(), NOW()),
(3, 'Serviços', NOW(), NOW()),
(4, 'Alimentação', NOW(), NOW()),
(5, 'Tecnologia', NOW(), NOW());

-- Tipos de clientes
INSERT IGNORE INTO `clientes_tipos` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Pessoa Física', NOW(), NOW()),
(2, 'Pessoa Jurídica', NOW(), NOW());

-- Categorias de fluxo de caixa
INSERT IGNORE INTO `categoria_fluxo_caixa` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Vendas', NOW(), NOW()),
(2, 'Despesas Operacionais', NOW(), NOW()),
(3, 'Outros', NOW(), NOW());

-- Situações de Pedido
INSERT IGNORE INTO `situacoes_pedidos` (`id`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'Aguardando', NOW(), NOW()),
(2, 'Em Produção', NOW(), NOW()),
(3, 'Enviado', NOW(), NOW()),
(4, 'Entregue', NOW(), NOW()),
(5, 'Cancelado', NOW(), NOW());

--  Empresa padrão 
INSERT IGNORE INTO `empresas` (`id`, `nome`, `email`, `telefone`, `celular`, `id_segmento`, `created_at`, `updated_at`) VALUES
(1, 'PDV MASTER DEMO', 'admin@pdvmaster.com.br', '(11) 9999-9999', '(11) 99999-9999', 1, NOW(), NOW());

--  Configuração PDV para a empresa padrão 
INSERT IGNORE INTO `config_pdv` (`id`, `id_empresa`, `id_tipo_pdv`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NOW(), NOW());

--  Usuário Admin padrão 
-- Senha: admin123 (hash SHA1 com salt padrão do .env)
-- ATENÇÃO: Altere a senha após o primeiro login!
INSERT IGNORE INTO `usuarios` (`id`, `id_empresa`, `nome`, `email`, `password`, `id_sexo`, `id_perfil`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Administrador pdv', 'admin@pdvmaster.com.br',
 SHA1(CONCAT('admin123', 'pdv_master_secret_key_mude_em_producao', REVERSE('pdv_master_secret_key_mude_em_producao'))),
 1, 1, 1, NOW(), NOW(), '0000-00-00 00:00:00');

--  Produtos de exemplo 
INSERT IGNORE INTO `produtos` (`id`, `id_empresa`, `nome`, `codigo`, `preco`, `descricao`, `ativar_quantidade`, `quantidade`, `mostrar_em_vendas`, `created_at`, `updated_at`) VALUES
(1, 1, 'Produto Exemplo A', 'PROD001', 10.50, 'Produto de demonstração A', 1, 100, 1, NOW(), NOW()),
(2, 1, 'Produto Exemplo B', 'PROD002', 25.00, 'Produto de demonstração B', 1, 50, 1, NOW(), NOW()),
(3, 1, 'Produto Exemplo C', 'PROD003', 5.99, 'Produto de demonstração C', 0, 0, 1, NOW(), NOW());
