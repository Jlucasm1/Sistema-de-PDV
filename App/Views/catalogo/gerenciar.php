<?php
use App\Services\Catalogo\PrecoCalculator;
use System\HtmlComponents\FlashMessage\FlashMessage;
use System\HtmlComponents\Modal\Modal;

$moeda = static fn($v): string => number_format((float) $v, 2, ',', '.');
?>
<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap">
            <h5 class="card-title" style="margin:0"><i class="fas fa-book"></i> Gerenciar catálogo de produtos</h5>
            <div>
                <a href="<?= BASEURL ?>/catalogo" class="btn btn-sm btn-default"><i class="fas fa-search"></i> Consulta</a>
                <a href="<?= BASEURL ?>/catalogo/historico" class="btn btn-sm btn-default"><i class="fas fa-history"></i> Histórico</a>
                <?php if (!empty($podeConcederAcesso)): ?>
                    <a href="<?= BASEURL ?>/catalogo/permissao" class="btn btn-sm btn-default"><i class="fas fa-user-lock"></i> Permissão</a>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-success" onclick="abrirFormulario(null)"><i class="fas fa-plus"></i> Novo produto</button>
            </div>
        </div>
        <?php FlashMessage::show(); ?>

        <form method="get" action="<?= BASEURL ?>/catalogo/gerenciar" style="padding:0 20px 10px">
            <input type="text" name="q" class="form-control" value="<?= e($termo ?? '') ?>" placeholder="Filtrar por nome, descrição ou código">
        </form>

        <div class="table-responsive" style="padding:0 10px">
            <table class="table table-striped table-hover">
                <thead><tr>
                    <th>Produto</th>
                    <th>Cód. interno</th>
                    <th>Cód. barras</th>
                    <th>Valor de venda</th>
                    <th>Valor mínimo</th>
                    <th>Valor de custo</th>
                    <th style="text-align:right">Ações</th>
                </tr></thead>
                <tbody>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><strong><?= e($p->nome) ?></strong><?php if (!empty($p->descricao)): ?><br><small class="text-muted"><?= e(mb_strimwidth(strip_tags($p->descricao), 0, 80, '...')) ?></small><?php endif; ?></td>
                        <td><?= e($p->codigo_interno ?? '-') ?></td>
                        <td><?= e($p->codigo ?? '-') ?></td>
                        <td><?= (float) $p->preco > 0 ? 'R$ ' . $moeda($p->preco) : '<span class="badge badge-warning">pendente</span>' ?></td>
                        <td><?= (float) $p->preco_minimo > 0 ? 'R$ ' . $moeda($p->preco_minimo) : '-' ?></td>
                        <td>
                            <?php
                            $custoCent  = (int) PrecoCalculator::paraCentavos((string) ($p->preco_compra ?? '0'));
                            $minimoCent = (int) PrecoCalculator::paraCentavos((string) $p->preco_minimo);
                            $vendaCent  = (int) PrecoCalculator::paraCentavos((string) $p->preco);
                            $refCent    = $minimoCent > 0 ? $minimoCent : $vendaCent;
                            $aval       = $refCent > 0 ? PrecoCalculator::avaliarCusto($refCent, $custoCent) : ['nivel' => PrecoCalculator::CUSTO_SEM];
                            ?>
                            <?php if ($custoCent > 0): ?>
                                R$ <?= $moeda($p->preco_compra) ?>
                                <?php if ($aval['nivel'] === PrecoCalculator::CUSTO_ABAIXO): ?>
                                    <br><span class="badge badge-danger" title="<?= $minimoCent > 0 ? 'O valor mínimo' : 'O valor de venda' ?> está abaixo do custo">abaixo do custo</span>
                                <?php elseif ($aval['nivel'] === PrecoCalculator::CUSTO_PERTO): ?>
                                    <br><span class="badge badge-warning" title="Margem de <?= e(str_replace('.', ',', $aval['margem_percentual'])) ?>% sobre o custo">perto do custo</span>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;white-space:nowrap">
                            <button class="btn btn-sm btn-primary" title="Editar" onclick="abrirFormulario(<?= (int) $p->id ?>)"><i class="fas fa-edit"></i></button>
                            <a class="btn btn-sm btn-default" title="Histórico" href="<?= BASEURL ?>/catalogo/historico/<?= (int) $p->id ?>"><i class="fas fa-history"></i></a>
                            <button class="btn btn-sm btn-danger" title="Excluir" onclick="excluirProduto(<?= (int) $p->id ?>, this)" data-nome="<?= e($p->nome) ?>"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$produtos): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px">Nenhum produto encontrado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($podePrecoOficial)): ?>
            <p class="text-muted" style="padding:0 20px 15px;font-size:12px">
                Você pode informar o preço oficial ao cadastrar um produto. Depois do cadastro, somente gerente ou administrador pode alterá-lo.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php Modal::start(['id' => 'modalCatalogo', 'width' => 'modal-lg', 'title' => 'Produto']); ?>
<div id="conteudo-modal-catalogo"></div>
<?php Modal::stop(); ?>

<script>
var CATALOGO_BASE = "<?= BASEURL ?>";
var CATALOGO_TOKEN = "<?= TOKEN ?>";

function catalogoFetch(url, options) {
    var tabId = typeof getCurrentTabId === 'function' ? getCurrentTabId() : '';
    var controller = new AbortController();
    var requestOptions = Object.assign({}, options || {});
    requestOptions.credentials = 'same-origin';
    requestOptions.headers = Object.assign({}, requestOptions.headers || {}, {
        'X-Requested-With': 'XMLHttpRequest',
        'X-Tab-Id': tabId
    });
    requestOptions.signal = controller.signal;

    if (tabId && typeof appendTabToUrl === 'function') {
        url = appendTabToUrl(url, tabId);
    }

    var timeout = window.setTimeout(function () { controller.abort(); }, 15000);
    return fetch(url, requestOptions).finally(function () { window.clearTimeout(timeout); });
}

function mostrarErroCatalogo(alvo, mensagem) {
    alvo.textContent = '';
    var erro = document.createElement('div');
    erro.className = 'alert alert-danger';
    erro.textContent = mensagem;
    alvo.appendChild(erro);
}

function abrirFormulario(id) {
    var alvo = document.getElementById('conteudo-modal-catalogo');
    alvo.textContent = 'Carregando...';
    document.querySelector('#modalCatalogo .modal-title').textContent = id ? 'Editar produto' : 'Novo produto';
    window.jQuery('#modalCatalogo').modal({backdrop: 'static'});
    catalogoFetch(CATALOGO_BASE + '/catalogo/formulario' + (id ? '/' + id : ''))
        .then(function (r) {
            return r.text().then(function (body) {
                if (!r.ok) {
                    try {
                        var resposta = JSON.parse(body);
                        throw new Error(resposta.message || 'Não foi possível carregar o formulário.');
                    } catch (erro) {
                        if (erro instanceof SyntaxError) {
                            throw new Error('Não foi possível carregar o formulário (HTTP ' + r.status + ').');
                        }
                        throw erro;
                    }
                }
                return body;
            });
        })
        .then(function (html) {
            alvo.innerHTML = html;
            var js = alvo.querySelector('script');
            if (js) { var s = document.createElement('script'); s.textContent = js.textContent; document.body.appendChild(s); }
        })
        .catch(function (erro) {
            mostrarErroCatalogo(
                alvo,
                erro.name === 'AbortError'
                    ? 'O servidor demorou para responder. Verifique a conexão com o banco de dados e tente novamente.'
                    : (erro.message || 'Falha de comunicação ao carregar o formulário.')
            );
        });
}

function excluirProduto(id, btn) {
    if (!confirm('Excluir o produto "' + btn.getAttribute('data-nome') + '"? O histórico, as vendas e os pedidos são preservados.')) return;
    var body = new URLSearchParams({_token: CATALOGO_TOKEN, id: id});
    btn.disabled = true;
    catalogoFetch(CATALOGO_BASE + '/catalogo/excluir', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()})
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.status) {
                location.reload();
                return;
            }
            alert(d.message || 'Erro ao excluir.');
            btn.disabled = false;
        })
        .catch(function (erro) {
            alert(erro.name === 'AbortError'
                ? 'O servidor demorou para responder. Tente novamente.'
                : 'Não foi possível excluir o produto. Verifique a conexão e tente novamente.');
            btn.disabled = false;
        });
}
</script>
