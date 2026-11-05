<?php
$moeda = static fn($v): string => number_format((float) $v, 2, ',', '.');
$editando = isset($produto->id);
$podeInformarPreco = !$editando || !empty($podePrecoOficial);
?>
<form id="form-catalogo" onsubmit="return false;">
    <input type="hidden" name="_token" value="<?= TOKEN ?>">
    <?php if ($editando): ?><input type="hidden" name="id" value="<?= (int) $produto->id ?>"><?php endif; ?>

    <div class="row">
        <div class="col-md-12 form-group">
            <label>Nome do produto *</label>
            <input type="text" name="nome" class="form-control" maxlength="100" required value="<?= e($produto->nome ?? '') ?>">
        </div>
        <div class="col-md-12 form-group">
            <label>Descrição</label>
            <textarea name="descricao" class="form-control" rows="2" maxlength="2000"><?= e(strip_tags(str_ireplace(['<br />', '<br>'], "\n", (string) ($produto->descricao ?? '')))) ?></textarea>
        </div>
        <div class="col-md-6 form-group">
            <label>Código interno</label>
            <input type="text" name="codigo_interno" class="form-control" maxlength="50" value="<?= e($produto->codigo_interno ?? '') ?>">
        </div>
        <div class="col-md-6 form-group">
            <label>Código de barras</label>
            <input type="text" name="codigo" class="form-control" maxlength="100" value="<?= e($produto->codigo ?? '') ?>">
        </div>
        <div class="col-md-4 form-group">
            <label>Valor de venda (preço oficial)</label>
            <?php if ($podeInformarPreco): ?>
                <input type="text" name="preco" class="form-control" inputmode="decimal" placeholder="0,00"
                       value="<?= $editando ? $moeda($produto->preco) : '' ?>">
            <?php else: ?>
                <input type="text" class="form-control" disabled
                       value="<?= $editando && (float) $produto->preco > 0 ? 'R$ ' . $moeda($produto->preco) : 'Pendente (definido pelo gerente/administrador)' ?>">
                <small class="text-muted">Somente gerente ou administrador altera o preço oficial de um produto cadastrado.</small>
            <?php endif; ?>
        </div>
        <div class="col-md-4 form-group">
            <label>Valor mínimo autorizado</label>
            <input type="text" name="preco_minimo" class="form-control" inputmode="decimal" placeholder="0,00"
                   value="<?= $editando ? $moeda($produto->preco_minimo) : '' ?>">
        </div>
        <div class="col-md-4 form-group">
            <label>Valor de custo</label>
            <input type="text" name="preco_compra" class="form-control" inputmode="decimal" placeholder="0,00"
                   value="<?= $editando && (float) ($produto->preco_compra ?? 0) > 0 ? $moeda($produto->preco_compra) : '' ?>">
            <small class="text-muted">Usado para alertar quando o preço negociado ficar abaixo ou perto do custo. Não aparece para vendedores sem permissão de catálogo.</small>
        </div>
    </div>
    <div id="catalogo-aviso-custo" class="alert alert-warning" style="display:none"></div>

    <div id="catalogo-erro" class="alert alert-danger" style="display:none"></div>
    <div style="text-align:right">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btn-salvar-catalogo"><i class="fas fa-save"></i> Salvar</button>
    </div>
</form>
<script>
(function () {
    var form = document.getElementById('form-catalogo');
    var aviso = document.getElementById('catalogo-aviso-custo');
    function num(nome) {
        var campo = form.querySelector('[name="' + nome + '"]');
        if (!campo || campo.disabled) return 0;
        var v = campo.value.replace(/\s|R\$/g, '');
        if (v.indexOf(',') >= 0) v = v.replace(/\./g, '').replace(',', '.');
        return Number(v) || 0;
    }
    function conferir() {
        var custo = num('preco_compra'), minimo = num('preco_minimo'), venda = num('preco');
        var msgs = [];
        if (custo > 0) {
            [['valor de venda', venda], ['valor mínimo', minimo]].forEach(function (par) {
                if (par[1] <= 0) return;
                if (par[1] < custo) msgs.push('O ' + par[0] + ' está abaixo do custo (prejuízo).');
                else if ((par[1] - custo) / custo < 0.10) msgs.push('O ' + par[0] + ' está perto do custo (margem abaixo de 10%).');
            });
        }
        aviso.textContent = msgs.join(' ');
        aviso.style.display = msgs.length ? 'block' : 'none';
    }
    ['preco', 'preco_minimo', 'preco_compra'].forEach(function (n) {
        var c = form.querySelector('[name="' + n + '"]');
        if (c) c.addEventListener('input', conferir);
    });
    conferir();
})();

document.getElementById('btn-salvar-catalogo').addEventListener('click', function () {
    var form = document.getElementById('form-catalogo');
    var erro = document.getElementById('catalogo-erro');
    var btn = this;
    btn.disabled = true;
    erro.style.display = 'none';
    catalogoFetch(CATALOGO_BASE + '/catalogo/salvar', {
        method: 'POST', credentials: 'same-origin',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(new FormData(form)).toString()
    }).then(function (r) { return r.json(); }).then(function (d) {
        if (d.status) { location.reload(); return; }
        erro.textContent = d.message || 'Não foi possível salvar.';
        erro.style.display = 'block';
        btn.disabled = false;
    }).catch(function (falha) {
        erro.textContent = falha.name === 'AbortError'
            ? 'O servidor demorou para responder. Verifique a conexão com o banco de dados e tente novamente.'
            : 'Falha de comunicação. Verifique a conexão e tente novamente.';
        erro.style.display = 'block';
        btn.disabled = false;
    });
});
</script>
