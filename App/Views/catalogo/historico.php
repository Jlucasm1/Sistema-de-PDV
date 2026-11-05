<?php
use System\HtmlComponents\FlashMessage\FlashMessage;

$rotulos = [
    'nome' => 'Nome', 'descricao' => 'Descrição', 'codigo_interno' => 'Código interno',
    'codigo' => 'Código de barras', 'preco' => 'Valor de venda', 'preco_minimo' => 'Valor mínimo', 'preco_compra' => 'Valor de custo',
];
$operacoes = ['cadastro' => 'Cadastro', 'edicao' => 'Edição', 'exclusao' => 'Exclusão'];
?>
<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body" style="display:flex;justify-content:space-between;align-items:center">
            <h5 class="card-title" style="margin:0"><i class="fas fa-history"></i> Histórico de alterações<?= $idProduto ? ' do produto #' . (int) $idProduto : '' ?></h5>
            <a href="<?= BASEURL ?>/catalogo/gerenciar" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
        <?php FlashMessage::show(); ?>
        <div class="table-responsive" style="padding:0 10px 15px">
            <table class="table table-striped">
                <thead><tr><th>Data</th><th>Usuário</th><th>Produto</th><th>Operação</th><th>Campo</th><th>Anterior</th><th>Novo</th></tr></thead>
                <tbody>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= e(date('d/m/Y H:i', strtotime($r->created_at))) ?></td>
                        <td><?= e($r->usuario ?? '-') ?></td>
                        <td><?= e($r->produto ?? ('#' . $r->id_produto)) ?></td>
                        <td><?= e($operacoes[$r->operacao] ?? $r->operacao) ?></td>
                        <td><?= e($rotulos[$r->campo] ?? ($r->campo ?? '-')) ?></td>
                        <td><?= e($r->valor_anterior ?? '-') ?></td>
                        <td><?= e($r->valor_novo ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$registros): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px">Nenhum registro.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
