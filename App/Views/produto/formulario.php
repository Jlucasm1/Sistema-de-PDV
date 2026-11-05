<style>
    .configuracao_produto { padding-top:10px;margin-bottom:15px;border-bottom:1px solid #eee; }
    .toggle-wrap { display:flex;align-items:center;gap:8px;margin:4px 0; }
    .toggle-wrap input[type=checkbox] { width:16px;height:16px;cursor:pointer; }
    .qtd-off { opacity:0.4;pointer-events:none; }
</style>

<?php if (isset($produto->id) && !empty($produto->codigo)): ?>
<div class="text-center" style="background:#fffcf5;padding:8px;margin-bottom:10px">
    <?php echo codigoDeBarrasParaSvg($produto->codigo); ?><br>
    <small><?php echo $produto->codigo; ?></small>
</div>
<?php endif; ?>

<form method="post"
      action="<?php echo isset($produto->id) ? BASEURL.'/produto/update' : BASEURL.'/produto/save'; ?>"
      enctype="multipart/form-data"
      id="form-produto">

    <input type="hidden" name="_token" value="<?php echo TOKEN; ?>">
    <?php if (isset($produto->id)): ?>
        <input type="hidden" name="id" value="<?php echo $produto->id; ?>">
    <?php endif; ?>

    <div class="configuracao_produto">
        <div class="row">
            <div class="col-6">
                <div class="toggle-wrap">
                    <input type="checkbox" id="chk_vendas" name="mostrar_em_vendas"
                        <?php echo (!isset($produto->mostrar_em_vendas) || $produto->mostrar_em_vendas == 1) ? 'checked' : ''; ?>>
                    <label for="chk_vendas"><small>Mostrar no PDV</small></label>
                </div>
            </div>
            <div class="col-6">
                <div class="toggle-wrap">
                    <input type="checkbox" id="chk_estoque" name="ativar_quantidade"
                        <?php echo (!isset($produto->ativar_quantidade) || $produto->ativar_quantidade == 1) ? 'checked' : ''; ?>>
                    <label for="chk_estoque"><small>Controlar estoque</small></label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Nome *</label>
                <input type="text" class="form-control" name="nome" id="nome"
                       placeholder="Ex: Coca-Cola Lata"
                       value="<?php echo isset($produto->nome) ? e($produto->nome) : ''; ?>">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Preço *</label>
                <input type="text" class="form-control" name="preco" id="preco"
                       placeholder="0,00" oninput="atualizarMargemProduto()"
                       value="<?php echo isset($produto->preco) ? number_format((float)$produto->preco, 2, ',', '.') : ''; ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Custo (compra)</label>
                <input type="text" class="form-control" name="preco_compra" id="preco_compra"
                       placeholder="0,00" oninput="atualizarMargemProduto()"
                       value="<?php echo isset($produto->preco_compra) ? number_format((float)$produto->preco_compra, 2, ',', '.') : ''; ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Preço mínimo de venda</label>
                <input type="text" class="form-control" name="preco_minimo" id="preco_minimo"
                       placeholder="0,00" oninput="atualizarMargemProduto()"
                       value="<?php echo isset($produto->preco_minimo) ? number_format((float)$produto->preco_minimo, 2, ',', '.') : ''; ?>">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Margem</label>
                <div id="info-margem" style="padding-top:8px;font-size:13px">-</div>
            </div>
        </div>
        <div class="col-md-3" id="div-qtd">
            <div class="form-group">
                <label>Qtd. Estoque</label>
                <input type="number" class="form-control" name="quantidade" id="quantidade"
                       min="0" placeholder="0"
                       value="<?php echo isset($produto->quantidade) ? (int)$produto->quantidade : 0; ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Código de Barras</label>
                <input type="text" class="form-control" name="codigo" id="codigo"
                       placeholder="Deixe vazio para gerar automaticamente"
                       value="<?php echo isset($produto->codigo) ? e($produto->codigo) : ''; ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Imagem</label>
                <input type="file" class="form-control" name="imagem" accept="image/*" id="inp-imagem">
                <div style="margin-top:6px">
                    <?php if (!empty($produto->imagem)): ?>
                        <img src="<?php echo BASEURL.'/'.$produto->imagem; ?>" id="img-preview"
                             style="width:55px;height:55px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
                    <?php else: ?>
                        <img src="" id="img-preview" style="display:none;width:55px;height:55px;object-fit:cover;border-radius:6px;border:1px solid #ddd">
                        <i class="fas fa-box-open" id="img-icon" style="font-size:36px;color:#ccc"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>Descrição</label>
                <textarea class="form-control" name="descricao" rows="2"
                          placeholder="Descrição opcional"><?php echo isset($produto->descricao) ? e(strip_tags($produto->descricao)) : ''; ?></textarea>
            </div>
        </div>
    </div>

    <div style="text-align:right;margin-top:5px">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="margin-right:6px">
            Cancelar
        </button>
        <button type="button" class="btn btn-success" onclick="salvarProduto()">
            <i class="fas fa-save"></i> Salvar
        </button>
    </div>
</form>

<script>
(function(){
    // Controle do campo quantidade
    function syncQtd() {
        var on = document.getElementById('chk_estoque').checked;
        document.getElementById('div-qtd').className = 'col-md-3' + (on ? '' : ' qtd-off');
        document.getElementById('quantidade').readOnly = !on;
    }
    syncQtd();
    document.getElementById('chk_estoque').addEventListener('change', syncQtd);

    // Preview imagem
    document.getElementById('inp-imagem').addEventListener('change', function(){
        var f = this.files[0]; if (!f) return;
        var r = new FileReader();
        r.onload = function(e){
            var p = document.getElementById('img-preview');
            var i = document.getElementById('img-icon');
            p.src = e.target.result; p.style.display = 'inline-block';
            if (i) i.style.display = 'none';
        };
        r.readAsDataURL(f);
    });

    // Submit
    window.salvarProduto = function(){
        var nome  = document.getElementById('nome').value.trim();
        var preco = document.getElementById('preco').value.trim();
        if (!nome)  { modalValidacao('Validação', 'Informe o <b>Nome</b> do produto.'); return; }
        if (!preco) { modalValidacao('Validação', 'Informe o <b>Preço</b> do produto.'); return; }

        // Normaliza preço: "9,90" → "9.90"
        var precoNorm = preco.replace(/[^\d,]/g, '').replace(',', '.');
        document.getElementById('preco').value = precoNorm;

        var form = document.getElementById('form-produto');
        if (typeof ensureTabHiddenField === 'function') {
            ensureTabHiddenField(form, typeof getCurrentTabId === 'function' ? getCurrentTabId() : null);
        }
        var formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Tab-Id': typeof getCurrentTabId === 'function' ? getCurrentTabId() : ''
            }
        })
            .then(function(resp){ return resp.json(); })
            .then(function(retorno){
                if (retorno && retorno.status) {
                    fecharModalERecarregar();
                    return;
                }
                modalValidacao('Erro', retorno && retorno.message ? retorno.message : 'Não foi possível salvar o produto.');
            })
            .catch(function(){
                modalValidacao('Erro', 'Não foi possível salvar o produto.');
            });
    };
})();
</script>

<script>
function parseMoedaBR(v) {
    v = String(v || '').replace(/[^\d,]/g, '').replace(',', '.');
    return parseFloat(v) || 0;
}
function atualizarMargemProduto() {
    var preco = parseMoedaBR($('#preco').val());
    var custo = parseMoedaBR($('#preco_compra').val());
    var minimo = parseMoedaBR($('#preco_minimo').val());
    var el = $('#info-margem');
    if (!preco || !custo) { el.text('-'); return; }
    var mg = (preco - custo) / preco * 100;
    var t = 'Tabela: ' + mg.toFixed(1) + '%';
    if (minimo) {
        var mm = (minimo - custo) / minimo * 100;
        t += '<br>No mínimo: ' + mm.toFixed(1) + '%';
        el.css('color', mm < 0 ? '#cc0000' : '#2d8a34');
    }
    el.html(t);
}
atualizarMargemProduto();
</script>
