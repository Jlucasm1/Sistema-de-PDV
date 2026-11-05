<?php
$descritivos = [
    1 => 'Venda por valor total, meio de pagamento e vendedor. Melhor para operação direta no balcão.',
    2 => 'Venda por itens com busca, carrinho, quantidade, troco e fechamento completo da compra.',
];
$configPdv = $configPdv ?? (object) [];
$idConfigPdv = $configPdv->idConfigPdv ?? '';
$idTipoPdvSelecionado = isset($configPdv->id_tipo_pdv) ? (int) $configPdv->id_tipo_pdv : 1;
?>
<style type="text/css">
    .pdv-tipo-card {
        border: 1px solid #d7e4f3;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
        background: #f8fbff;
        cursor: pointer;
        transition: .2s ease;
        display: block;
    }
    .pdv-tipo-card:hover {
        border-color: #90caf9;
        box-shadow: 0 4px 16px rgba(21, 101, 192, 0.10);
    }
    .pdv-tipo-card.ativo {
        border-color: #1976d2;
        background: #eef6ff;
        box-shadow: 0 4px 16px rgba(21, 101, 192, 0.16);
    }
    .pdv-tipo-card h6 {
        margin-bottom: 6px;
        color: #0d47a1;
    }
    .pdv-tipo-card p {
        margin-bottom: 0;
        opacity: .8;
    }
</style>

<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-cogs"></i> Configurações</h5>
            <div class="alert alert-info" style="margin-top:14px;">
                <strong>Modo ativo:</strong>
                <?php echo $configPdv->descricao ?? 'PDV Padrão'; ?>
            </div>

            <div class="row">
                <?php foreach ($tiposPdv as $tipoPdv): ?>
                    <?php $ativo = ((int) $tipoPdv->id === $idTipoPdvSelecionado); ?>
                    <div class="col-lg-6">
                        <label for="id_<?php echo $tipoPdv->id; ?>"
                               class="pdv-tipo-card <?php echo $ativo ? 'ativo' : ''; ?>"
                               onclick="alterarConfigPdv('<?php echo $idConfigPdv; ?>','<?php echo $tipoPdv->id; ?>')">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                                <div>
                                    <h6><?php echo $tipoPdv->descricao; ?></h6>
                                    <p><?php echo $descritivos[(int) $tipoPdv->id] ?? 'Modo de operação do PDV.'; ?></p>
                                </div>
                                <input type="radio" name="id_tipo_pdv" id="id_<?php echo $tipoPdv->id; ?>"
                                    <?php echo $ativo ? 'checked' : '' ?>>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASEURL; ?>/assets/js/core/jquery.min.js"></script>
<script>
    function alterarConfigPdv(idConfigPdv, idTipoPdv) {
        var rota = getDomain() + "/configuracao/alterarConfigPdv";
        modalValidacao('Salvando', 'Alterando PDV! <small>Aguarde...</small>');
        $(".close").hide();

        $.post(rota, {
            '_token': '<?php echo TOKEN; ?>',
            'idConfigPdv': idConfigPdv,
            'idTipoPdv': idTipoPdv
        }, function (result) {
            tipoPdv = JSON.parse(result);
            if (tipoPdv.status == true) {
                window.location.href = getDomain() + "/configuracao";
            }
        });
    }
</script>
