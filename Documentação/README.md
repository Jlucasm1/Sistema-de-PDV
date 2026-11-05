# Documentacao do PDV UniGoiás

Esta pasta centraliza a documentacao tecnica produzida para acompanhamento, validacao e rastreabilidade do sistema PDV UniGoiás.

O sistema esta em producao em `https://pdv.marianoprime.com`, hospedado na HostGator (cPanel) com DNS, proxy e HTTPS pela Cloudflare. O Docker e usado para desenvolvimento local.

## Arquivos

| Arquivo | Finalidade |
|---|---|
| `DOCUMENTACAO_TECNICA.md` | Visao tecnica do sistema, arquitetura, ambientes, modulos, seguranca e manutencao. |
| `IMPLANTACAO_HOSTGATOR.md` | Como o sistema esta publicado em producao, configuracoes obrigatorias, banco, publicacao de atualizacoes e diagnostico. |
| `MATRIZ_RASTREABILIDADE.md` | Relacao entre requisitos, funcionalidades implementadas, rotas/modulos e testes correspondentes. |
| `CASOS_DE_TESTE.md` | Casos de teste funcionais e tecnicos com pre-condicoes, passos, dados e resultados esperados. |

## Escopo documentado

- Autenticacao e recuperacao de senha.
- Controle de acesso por perfis (vendedor restrito a consulta de precos e catalogo).
- Operacoes de PDV padrao e diferencial.
- Consulta de precos, simulador de negociacao com varios produtos e catalogo de produtos.
- Emissao mock de cupom fiscal e nota fiscal.
- Cadastros de produtos, clientes, empresas e usuarios.
- Pedidos, caixa, fluxo de caixa, relatorios e logs.
- Ambiente de producao HostGator + Cloudflare.
- Testes automatizados existentes e cenarios manuais recomendados.
