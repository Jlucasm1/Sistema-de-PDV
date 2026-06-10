<!--Usando o Html Components-->
<?php use System\HtmlComponents\Modal\Modal; ?>

<style>
    .fromPDV td{
        background:#e4fbe9!important;
        margin-top:5px!important;
        border:1px dotted #41da67;
    }
    #label_incluir_pdv {
        border:1px solid #f3f2ee;
        background:#f3f2ee;
        border-radius:5px;
        padding:2px;
        padding-left:4px;
        padding-right:4px;
        margin-left:10px;
    }
    .caixa-resumo-grid {
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
        gap:12px;
        margin-bottom:18px;
    }
    .caixa-resumo-card {
        border:1px solid #eceae4;
        border-radius:8px;
        padding:12px;
        background:#fff;
    }
    .caixa-resumo-card small {
        display:block;
        opacity:.70;
        margin-bottom:6px;
    }
    .caixa-resumo-card strong {
        display:block;
        font-size:20px;
    }
    .status-online {
        color:#1a9c50;
        font-weight:bold;
    }
    .status-offline {
        color:#b66b00;
        font-weight:bold;
    }
</style>

<div class="card-body">

    <small style="opacity:0.70">
        Fluxo de Caixa:
        <b><?php echo mesesPorExtensoEnumeroDoMes(date('m'));?> de <?php echo date('Y');?></b>
    </small>

    <label for="checkbox_incluir_pdv" id="label_incluir_pdv">
        Ocultar PDV <input type="checkbox" id="checkbox_incluir_pdv" onclick="return incluirPDV($(this))"
        <?php echo ($retirarPDV == 1) ? 'checked' : false;?>>
    </label>
    <hr>

    <div class="caixa-resumo-grid">
        <div class="caixa-resumo-card">
            <small>Caixas online agora</small>
            <strong><?php echo (int) ($resumoCaixas->caixas_online ?? 0); ?></strong>
            <span style="font-size:12px;opacity:.70">Acesso nos últimos 15 minutos</span>
        </div>

        <div class="caixa-resumo-card">
            <small>Caixas com movimento</small>
            <strong><?php echo (int) ($resumoCaixas->caixas_ativos ?? 0); ?></strong>
            <span style="font-size:12px;opacity:.70">Operadores com vendas no período</span>
        </div>

        <div class="caixa-resumo-card">
            <small>Faturamento do período</small>
            <strong>R$ <?php echo real($resumoCaixas->faturamento_total ?? 0); ?></strong>
            <span style="font-size:12px;opacity:.70"><?php echo (int) ($resumoCaixas->quantidade_vendas ?? 0); ?> vendas registradas</span>
        </div>

        <div class="caixa-resumo-card">
            <small>Usuários com movimentação</small>
            <strong style="font-size:15px">
                <?php echo !empty($resumoCaixas->usuarios_conectados) ? $resumoCaixas->usuarios_conectados : 'Nenhum no período'; ?>
            </strong>
            <span style="font-size:12px;opacity:.70">
                Última movimentação:
                <?php echo !empty($resumoCaixas->ultima_movimentacao) ? date('d/m/Y H:i', strtotime($resumoCaixas->ultima_movimentacao)) : 'Não encontrada'; ?>
            </span>
        </div>
    </div>

    <table class="table tabela-ajustada table-striped">
        <thead>
            <tr>
                <th>Caixa</th>
                <th>Usuário</th>
                <th>Status</th>
                <th>Faturado</th>
                <th>Vendas</th>
                <th>Última venda</th>
                <th>Último acesso</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($caixasDetalhados as $indice => $caixa): ?>
                <tr>
                    <td><?php echo 'Caixa ' . ($indice + 1); ?></td>
                    <td><?php echo $caixa->nome; ?></td>
                    <td>
                        <?php if ((int) $caixa->online === 1): ?>
                            <span class="status-online">Online</span>
                        <?php else: ?>
                            <span class="status-offline">Offline</span>
                        <?php endif; ?>
                    </td>
                    <td>R$ <?php echo real($caixa->faturado); ?></td>
                    <td><?php echo (int) $caixa->quantidade_vendas; ?></td>
                    <td><?php echo !empty($caixa->ultima_venda) ? date('d/m/Y H:i', strtotime($caixa->ultima_venda)) : 'Sem vendas'; ?></td>
                    <td><?php echo !empty($caixa->ultimo_acesso) ? date('d/m/Y H:i', strtotime($caixa->ultimo_acesso)) : 'Sem acesso'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="table tabela-ajustada table-stripeds">
        <thead>
            <tr>
                <th style="text-align:center">Entradas</th>
                <th style="text-align:center">Saídas</th>
                <th style="text-align:center">Saldo</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td style="background:#bad7c2;text-align:center">R$ <?php echo real($fluxo->entradas);?></td>
                <td style="background:#f6d6d6;text-align:center">R$ <?php echo real($fluxo->saidas);?></td>
                <td style="background:#c4d9e4;text-align:center">R$ <?php echo real($fluxo->restante);?></td>
            </tr>
        </tbody>
    </table>

    <table class="table tabela-ajustada table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Lançamento</th>
                <th>Vencimento</th>
                <th style="text-align:right;padding-right:0">
                    <button onclick="modalRegistrarMovimentacao('<?php echo BASEURL?>/fluxoDeCaixa/modalRegistrarMovimentacao', false);" class="btn btn-sm btn-success" title="Registrar Movimentações!">
                        <i class="fas fa-plus"></i>
                    </button>
                </th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($fluxoDetalhadoPorMes as $fluxoCaixa):?>
                <tr <?php echo isset($fluxoCaixa->fromPDV) ? 'class="fromPDV"' : false;?>>
                    <td><?php echo ($fluxoCaixa->tipo_movimento == 1) ? '<i class="fas fa-arrow-right" style="color:#41da67" title="Entrada"></i>' : '<i class="fas fa-arrow-left" style="color:#e96969" title="Saída"></i>';?></td>
                    <td><?php echo $fluxoCaixa->descricao;?></td>
                    <td>R$ <?php echo real($fluxoCaixa->valor);?></td>
                    <td><?php echo date('d/m/Y', strtotime($fluxoCaixa->created_at));?></td>
                    <td><?php echo date('d/m/Y', strtotime($fluxoCaixa->data));?></td>

                    <td style="text-align:right;padding-right:0">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-secondary dropdown-toggle"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo (isset($fluxoCaixa->fromPDV) ? 'disabled' : false)?>>
                            <i class="fas fa-cogs"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <button class="dropdown-item" href="#"
                            onclick="modalRegistrarMovimentacao('<?php echo BASEURL?>/fluxoDeCaixa/modalRegistrarMovimentacao', '<?php echo $fluxoCaixa->id;?>')">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>

    <?php if (
        count($caixasDetalhados) == 0
        && ($resumoCaixas->faturamento_total ?? 0) == 0
        &&
        $fluxo->entradas == 0
        && $fluxo->saidas == 0
        && $fluxo->restante == 0
        && count($fluxoDetalhadoPorMes) == 0
    ):?>

    <div class="text-center">
        <i class="fas fa-sad-tear" style="font-size:40px;opacity:0.70"></i>
        <br><br>
        <h6 style="opacity:0.70">Nenhum dado encontrado!</h6>
    </div>

    <?php endif;?>
</div>

<?php Modal::start([
    'id' => 'modalRegistrarMovimentacao',
    'width' => 'modal-lg',
    'title' => 'Registrar Movimentações'
]); ?>

<div id="formulario"></div>

<?php Modal::stop(); ?>

<script>
    function modalRegistrarMovimentacao(rota, id) {
        var url = "";

        if (id) {
            url = rota + "/" + id;
        } else {
            url = rota;
        }

        $("#formulario").html("<center><h3>Carregando...</h3></center>");
        $("#modalRegistrarMovimentacao").modal({backdrop: 'static'});
        $("#formulario").load(url);
    }
</script>
