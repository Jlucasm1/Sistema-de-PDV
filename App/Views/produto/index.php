<?php
use System\HtmlComponents\FlashMessage\FlashMessage;
use System\HtmlComponents\Modal\Modal;
?>
<style>
    .badge-sim  { background:#d4edda;color:#155724;border-radius:10px;padding:2px 8px;font-size:11px; }
    .badge-nao  { background:#e2e3e5;color:#383d41;border-radius:10px;padding:2px 8px;font-size:11px; }
    .badge-ok   { background:#d4edda;color:#155724;border-radius:10px;padding:2px 8px;font-size:11px; }
    .badge-low  { background:#fff3cd;color:#856404;border-radius:10px;padding:2px 8px;font-size:11px; }
    .badge-zero { background:#f8d7da;color:#721c24;border-radius:10px;padding:2px 8px;font-size:11px; }
    .img-prod   { width:36px;height:36px;object-fit:cover;border-radius:50%;border:1px solid #dee2e6;cursor:pointer; }
</style>

<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-box-open"></i> Produtos</h5>
        </div>
        <?php FlashMessage::show(); ?>
        <div style="padding:0 20px 10px;color:#999;font-size:12px">
            Mais caro: <b>R$ <?php echo real($informacoes->maisCaro ?? 0); ?></b>
            &nbsp;|&nbsp;
            Mais barato: <b>R$ <?php echo real($informacoes->maisBarato ?? 0); ?></b>
        </div>

        <?php $rota = BASEURL . '/produto/modalFormulario'; ?>

        <?php if (count($produtos) > 0): ?>
        <table class="table tabela-ajustada table-striped table-hover" style="width:100%">
            <thead><tr>
                <th style="width:44px"></th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Em Vendas</th>
                <th>Estoque</th>
                <th style="text-align:right;padding-right:12px">
                    <button onclick="abrirModal(false)" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Novo
                    </button>
                </th>
            </tr></thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td>
                        <?php if (!empty($produto->imagem)): ?>
                            <img src="<?php echo BASEURL.'/'.$produto->imagem; ?>"
                                 class="img-prod"
                                 title="<?php echo e($produto->nome); ?>">
                        <?php else: ?>
                            <i class="fas fa-box-open" style="font-size:24px;color:#bbb"></i>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo e($produto->nome); ?></strong></td>
                    <td><strong>R$ <?php echo real($produto->preco); ?></strong></td>
                    <td>
                        <?php if ($produto->mostrar_em_vendas): ?>
                            <span class="badge-sim"><i class="fas fa-check"></i> Sim</span>
                        <?php else: ?>
                            <span class="badge-nao"><i class="fas fa-times"></i> Não</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($produto->ativar_quantidade): ?>
                            <?php if ($produto->quantidade == 0): ?>
                                <span class="badge-zero"><i class="fas fa-exclamation-triangle"></i> Esgotado</span>
                            <?php elseif ($produto->quantidade <= 5): ?>
                                <span class="badge-low"><?php echo $produto->quantidade; ?> un</span>
                            <?php else: ?>
                                <span class="badge-ok"><?php echo $produto->quantidade; ?> un</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#bbb;font-size:11px">sem controle</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right">
                        <button class="btn btn-sm btn-primary"
                                onclick="abrirModal('<?php echo $produto->id; ?>')"
                                title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger"
                                onclick="excluirProduto('<?php echo $produto->id; ?>', <?php echo json_encode(normalizaTexto($produto->nome), JSON_UNESCAPED_UNICODE); ?>)"
                                title="Excluir">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-center" style="padding:50px 0">
                <i class="fas fa-box-open" style="font-size:50px;opacity:0.25"></i><br><br>
                <h6 style="opacity:0.5">Nenhum produto cadastrado!</h6><br>
                <button onclick="abrirModal(false)" class="btn btn-success">
                    <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
                </button>
            </div>
        <?php endif; ?>
        <br>
    </div>
</div>

<?php Modal::start(['id' => 'modalProduto', 'width' => 'modal-lg', 'title' => 'Produto']); ?>
<div id="conteudo-modal-produto"></div>
<?php Modal::stop(); ?>

<script>
var BASE = "<?php echo BASEURL; ?>";

function abrirModal(id) {
    var url = BASE + "/produto/modalFormulario" + (id ? "/" + id : "");
    $("#modalProduto .modal-title").text(id ? "Editar Produto" : "Novo Produto");
    $("#conteudo-modal-produto").html("<div style='text-align:center;padding:40px'><i class='fas fa-spinner fa-spin fa-2x'></i></div>");
    $("#modalProduto").modal({backdrop: 'static'});
    $("#conteudo-modal-produto").load(url);
}

function excluirProduto(id, nome) {
    if (!confirm('Excluir o produto "' + nome + '"?')) return false;
    $.get(BASE + "/produto/excluirProduto/" + id, function (data) {
        var obj = JSON.parse(data);
        if (obj.deletado) location.reload();
        else alert('Erro ao excluir: ' + (obj.error || 'tente novamente'));
    });
    return false;
}

// chamado pelo formulário após salvar com sucesso
function fecharModalERecarregar() {
    $("#modalProduto").modal('hide');
    location.reload();
}
</script>
