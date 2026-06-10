<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <style>
        @page {
            margin: 24px 22px;
        }

        :root {
            --sistema-primaria: #1da1f2;
            --sistema-primaria-escura: #0e7cc0;
            --sistema-sucesso: #83e6cd;
            --sistema-info: #9be6e6;
            --sistema-lilas: #dbb4dc;
            --sistema-areia: #cfa18c;
            --sistema-teal: #73b1a2;
            --texto-principal: #223042;
            --texto-secundario: #617289;
            --borda: #d7e1ea;
            --fundo-suave: #f4f9ff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--texto-principal);
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
        }

        .header {
            border: 1px solid var(--borda);
            border-left: 6px solid var(--sistema-primaria);
            border-radius: 6px;
            padding: 10px 12px;
            background: #fff;
        }

        .empresa {
            margin: 0;
            font-size: 18px;
            color: var(--sistema-primaria-escura);
        }

        .titulo {
            margin: 3px 0 2px;
            font-size: 12px;
            font-weight: 700;
        }

        .meta {
            margin: 0;
            color: var(--texto-secundario);
            font-size: 9px;
        }

        .total {
            margin-top: 8px;
            background: var(--fundo-suave);
            border: 1px solid #cce6ff;
            border-radius: 5px;
            padding: 8px 10px;
            color: var(--sistema-primaria-escura);
            font-weight: 700;
            font-size: 12px;
        }

        .cards {
            width: 100%;
            margin-top: 8px;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .card {
            border-radius: 4px;
            padding: 8px;
            text-align: center;
            font-size: 9px;
            font-weight: 700;
        }

        .card .value {
            margin-top: 2px;
            font-size: 12px;
            color: #203244;
        }

        .c1 { background: var(--sistema-sucesso); }
        .c2 { background: var(--sistema-info); }
        .c3 { background: var(--sistema-lilas); }
        .c4 { background: var(--sistema-areia); }

        .section-title {
            margin: 12px 0 5px;
            padding: 4px 8px;
            border-left: 4px solid var(--sistema-primaria);
            background: #f2f8ff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--sistema-primaria-escura);
        }

        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .chart-card {
            border: 1px solid var(--borda);
            border-radius: 5px;
            padding: 8px;
            vertical-align: top;
        }

        .chart-title {
            margin: 0 0 6px;
            font-size: 9px;
            color: var(--texto-secundario);
            text-transform: uppercase;
        }

        .bar-row {
            margin-bottom: 6px;
        }

        .bar-label {
            display: block;
            margin-bottom: 2px;
            font-size: 8px;
            color: var(--texto-secundario);
        }

        .bar-track {
            width: 100%;
            background: #ecf2f8;
            border-radius: 3px;
            height: 12px;
            border: 1px solid #dbe6f1;
        }

        .bar-fill {
            height: 100%;
            background: var(--sistema-primaria);
            border-radius: 2px;
        }

        .bar-fill.alt { background: var(--sistema-teal); }

        .bar-value {
            display: block;
            margin-top: 2px;
            text-align: right;
            font-size: 8px;
            font-weight: 700;
            color: #223042;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid var(--borda);
            font-size: 9px;
        }

        thead {
            display: table-header-group;
        }

        thead tr {
            background: #eaf4ff;
        }

        th {
            border: 1px solid var(--borda);
            padding: 6px 4px;
            text-transform: uppercase;
            font-size: 8px;
            color: #2b4b6a;
        }

        td {
            border: 1px solid #e3edf7;
            padding: 5px 4px;
            text-align: center;
            word-break: break-word;
        }

        tbody tr:nth-child(even) {
            background: #f9fcff;
        }

        .spec-table td:nth-child(1),
        .spec-table td:nth-child(2),
        .spec-table td:nth-child(3) {
            text-align: left;
        }

        .footer {
            margin-top: 8px;
            text-align: right;
            color: #95a5b7;
            font-size: 8px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1 class="empresa"><?php echo $this->getEmpresa()->nome; ?></h1>
    <p class="titulo"><?php echo $titulo; ?></p>
    <p class="meta">Gerado em: <?php echo date('d/m/Y'); ?> as <?php echo date('H:i'); ?></p>
</div>

<div class="total">
    Total vendido no periodo: R$ <?php echo real($this->getTotalVendas()); ?>
</div>

<table class="cards">
    <tr>
        <td class="card c1">Vendas<div class="value"><?php echo (int) ($resumo['quantidadeVendas'] ?? 0); ?></div></td>
        <td class="card c2">Itens vendidos<div class="value"><?php echo (int) ($resumo['quantidadeItens'] ?? 0); ?></div></td>
        <td class="card c3">Produtos unicos<div class="value"><?php echo (int) ($resumo['produtosUnicos'] ?? 0); ?></div></td>
        <td class="card c4">Ticket medio<div class="value">R$ <?php echo number_format((float) ($resumo['ticketMedio'] ?? 0), 2, ',', '.'); ?></div></td>
    </tr>
</table>

<p class="section-title">Graficos</p>
<table class="grid">
    <tr>
        <td class="chart-card" style="width:50%">
            <p class="chart-title">Top produtos por faturamento</p>
            <?php if (!empty($graficos['topProdutos'])): ?>
                <?php foreach ($graficos['topProdutos'] as $item): ?>
                    <div class="bar-row">
                        <span class="bar-label"><?php echo $item['legenda']; ?></span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?php echo max((float) $item['percentual'], 2); ?>%"></div>
                        </div>
                        <span class="bar-value"><?php echo $item['valorFormatado']; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <small>Nenhum dado para exibicao.</small>
            <?php endif; ?>
        </td>
        <td class="chart-card" style="width:50%">
            <p class="chart-title">Meios de pagamento por faturamento</p>
            <?php if (!empty($graficos['meiosPagamento'])): ?>
                <?php foreach ($graficos['meiosPagamento'] as $item): ?>
                    <div class="bar-row">
                        <span class="bar-label"><?php echo $item['legenda']; ?></span>
                        <div class="bar-track">
                            <div class="bar-fill alt" style="width: <?php echo max((float) $item['percentual'], 2); ?>%"></div>
                        </div>
                        <span class="bar-value"><?php echo $item['valorFormatado']; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <small>Nenhum dado para exibicao.</small>
            <?php endif; ?>
        </td>
    </tr>
</table>

<p class="section-title">Especificacoes dos Produtos</p>
<table class="spec-table">
    <thead>
    <tr>
        <th>Produto</th>
        <th>Codigo</th>
        <th>Descricao</th>
        <th>Preco base</th>
        <th>Qtd vendida</th>
        <th>Total vendido</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($produtosEspecificacoes)): ?>
        <?php foreach ($produtosEspecificacoes as $item): ?>
            <tr>
                <td><?php echo stringAbreviation($item['nome'] ?: 'Nao informado', 36, '...'); ?></td>
                <td><?php echo !empty($item['codigo']) ? $item['codigo'] : 'Nao consta'; ?></td>
                <td><?php echo !empty($item['descricao']) ? stringAbreviation($item['descricao'], 60, '...') : 'Nao consta'; ?></td>
                <td>R$ <?php echo number_format((float) $item['precoBase'], 2, ',', '.'); ?></td>
                <td><?php echo number_format((float) $item['quantidadeVendida'], 0, ',', '.'); ?></td>
                <td>R$ <?php echo number_format((float) $item['valorTotal'], 2, ',', '.'); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">Nenhum produto encontrado no periodo.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<p class="section-title">Detalhamento das Vendas</p>
<table>
    <thead>
    <tr>
        <?php
        foreach ($head as $value) {
            echo "<th>{$value}</th>";
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($dados as $values): ?>
        <tr>
            <?php foreach ($values as $value): ?>
                <td><?php echo $value; ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p class="footer">Documento gerado automaticamente pelo sistema.</p>
</body>
</html>
