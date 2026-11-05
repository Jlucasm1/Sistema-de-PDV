<!--Usando o Html Components-->
<?php

use App\Models\ConfigPdv;
use System\Session\Session;

$totalVendasNoDia = (float) ($totalVendasNoDia ?? 0);
$totalVendaNoDiaAnterior = $totalVendaNoDiaAnterior ?? null;
$totalValorVendaPorMeioDePagamentoNoDia = $totalValorVendaPorMeioDePagamentoNoDia ?? [];
$vendasGeralDoDia = $vendasGeralDoDia ?? [];
$usuarios = $usuarios ?? [];
$meiosPagamentos = $meiosPagamentos ?? [];
$produtos = $produtos ?? [];
$pdvPadraoRoutes = [
    'adicionar' => BASEURL . '/pdvPadrao/colocarProdutosNaMesa',
    'mesa' => BASEURL . '/pdvPadrao/obterProdutosDaMesa',
    'quantidade' => BASEURL . '/pdvPadrao/alterarAquantidadeDeUmProdutoNaMesa',
    'retirar' => BASEURL . '/pdvPadrao/retirarProdutoDaMesa',
    'total' => BASEURL . '/pdvPadrao/obterValorTotalDosProdutosNaMesa',
    'troco' => BASEURL . '/pdvPadrao/calcularTroco',
    'save' => Session::appendTabToUrl(BASEURL . '/pdvPadrao/save'),
];
$ultimaNotaFiscalMock = Session::get('ultima_nota_fiscal_mock');
Session::delete('ultima_nota_fiscal_mock');

?>
<style type="text/css">
    .imagem-perfil {
        width: 30px;
        height: 30px;
        object-fit: cover;
        object-position: center;
        border-radius: 50%;
    }

    .pdv-quick-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .pdv-quick-item {
        border: 1px solid #d8e2ef;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        transition: .2s ease;
        cursor: pointer;
        text-align: left;
    }

    .pdv-quick-item:hover {
        border-color: #42a5f5;
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.12);
    }

    .pdv-quick-item strong,
    .pdv-quick-item small {
        display: block;
    }

    .pdv-quick-toolbar {
        display: grid;
        grid-template-columns: 2fr 1fr auto;
        gap: 10px;
        align-items: end;
    }

    .card-two {
        margin-top: 10px;
        border-radius: 3px;
        box-shadow: none;
        border: 1px solid #dddddd;
        padding-left: 3px;
        padding-right: 3px;
    }

    .tabela-ajustada tr td {
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        font-size: 12px;
    }

    .tabela-ajustada th {
        font-size: 13px !important;
    }

    .campo-quantidade {
        border: 1px solid #dee2e6;
        width: 60px;
        text-align: center;
    }

    .pdv-total-box {
        border: 1px solid #d8e2ef;
        border-radius: 12px;
        padding: 14px;
        background: #f8fbff;
    }

    @media only screen and (max-width: 900px) {
        .pdv-quick-toolbar {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                <div>
                    <h5 class="card-title" style="margin-bottom:6px;">
                        <i class="<?php echo ConfigPdv::iconeTipo(ConfigPdv::TIPO_PADRAO); ?>"></i>
                        <?php echo ConfigPdv::nomeTipo(ConfigPdv::TIPO_PADRAO); ?>
                    </h5>
                    <small style="opacity:.75;">
                        Frente de caixa com passagem rápida de itens e fechamento direto no balcão.
                    </small>
                </div>
                <span class="badge badge-primary" style="padding:8px 12px;font-size:12px;">Venda rápida</span>
            </div>
        </div>
    </div>

    <div class="card col-lg-12 content-div">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-cash-register"></i> Passar Itens no Caixa</h5>

            <div class="pdv-quick-toolbar">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="busca_produto_padrao">Buscar produto</label>
                    <input type="text" class="form-control" id="busca_produto_padrao"
                           placeholder="Digite nome ou código de barras">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="produto_rapido_id">Produto</label>
                    <select class="form-control" id="produto_rapido_id">
                        <option value="">Selecione</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?php echo $produto->id; ?>"
                                    data-nome="<?php echo e(mb_strtolower(normalizaTexto($produto->nome), 'UTF-8')); ?>"
                                    data-codigo="<?php echo e(mb_strtolower(normalizaTexto((string) ($produto->codigo ?? '')), 'UTF-8')); ?>">
                                <?php echo e($produto->nome); ?> - R$ <?php echo real($produto->preco); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" class="btn btn-success" onclick="adicionarProdutoSelecionadoNoPadrao()">
                    <i class="fas fa-plus"></i> Adicionar
                </button>
            </div>

            <div class="pdv-quick-grid" id="atalhos-produtos-padrao">
                <?php foreach (array_slice($produtos, 0, 8) as $produto): ?>
                    <?php $esgotado = ((int) ($produto->ativar_quantidade ?? 0) === 1 && (int) ($produto->quantidade ?? 0) <= 0); ?>
                    <button type="button" class="pdv-quick-item"
                            onclick="colocarProdutosNoCaixaPadrao(<?php echo $produto->id; ?>, '<?php echo $esgotado ? 'esgotado' : 'ok'; ?>')">
                        <strong><?php echo e($produto->nome); ?></strong>
                        <small>R$ <?php echo real($produto->preco); ?></small>
                        <?php if ($esgotado): ?>
                            <small style="color:#c62828;">Sem estoque</small>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="card col-lg-8 content-div">
        <div class="card-body" style="overflow-x:auto!important;">
            <h5 class="card-title"><i class="fas fa-shopping-basket"></i> Itens da Venda</h5>
            <table class="table tabela-ajustada tabela-de-produto-padrao" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th class="hidden-when-mobile">Preço</th>
                        <th>QTD</th>
                        <th>Total</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card col-lg-4 content-div">
        <div class="card-body">
            <form method="post" action="<?php echo $pdvPadraoRoutes['save']; ?>" onsubmit="return validarVendaPadrao();">
                <input type="hidden" name="_token" value="<?php echo TOKEN; ?>"/>
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars(Session::currentTabId()); ?>">
                <input type="hidden" name="troco" id="troco_padrao_hidden" value="">

                <div class="pdv-total-box">
                    <label for="b-mostra-valor-total-padrao">Total da venda</label>
                    <input type="text" class="form-control" id="b-mostra-valor-total-padrao" readonly placeholder="R$ 0,00">
                </div>

                <div class="form-group" style="margin-top:12px;">
                    <label for="id_usuario_padrao">Vendedor *</label>
                    <select class="form-control" name="id_usuario" id="id_usuario_padrao">
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo $usuario->id; ?>" <?php echo $usuario->id == Session::get('idUsuario') ? 'selected' : ''; ?>>
                                <?php echo $usuario->nome; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_meio_pagamento_padrao">Meio de pagamento *</label>
                    <select class="form-control" name="id_meio_pagamento" id="id_meio_pagamento_padrao" onchange="handlePagamentoPadrao()">
                        <option value="selecione">Selecione</option>
                        <?php foreach ($meiosPagamentos as $pagamento): ?>
                            <option value="<?php echo $pagamento->id; ?>">
                                <?php echo $pagamento->legenda; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="div-valor-recebido-padrao" style="display:none;">
                    <label for="valor_recebido_padrao" id="label-valor-pago-padrao">Valor pago</label>
                    <input type="text" class="form-control campo-moeda" id="valor_recebido_padrao" name="valor_recebido"
                           placeholder="R$ 0,00" onblur="calcularTrocoPadrao();">
                </div>

                <div class="form-group" id="div-troco-padrao" style="display:none;">
                    <label for="input_troco_padrao">Troco</label>
                    <input type="text" class="form-control" id="input_troco_padrao" readonly placeholder="R$ 0,00">
                </div>

                <button type="submit" class="btn btn-success btn-block" id="button-confirmar-venda-padrao" disabled>
                    <i class="fas fa-save"></i> Finalizar venda rápida
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="card card-two col-lg-6 content-div">
        <div class="card-body">
            <h5 class="card-title" style="text-align:center">
                <i class="fas fa-cart-arrow-down" style="color:#00cc99"></i>
                Últimas 10 vendas no dia!
            </h5>

            <center><small>Hoje: <?php echo date('d/m'); ?></small></center>

            <?php if (count($vendasGeralDoDia) > 0): ?>
                <table class="table tabela-ajustada table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Valor</th>
                        <th>Pagamento</th>
                        <th>Hora</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vendasGeralDoDia as $venda): ?>
                        <tr>
                            <td><img class="imagem-perfil" src="<?php echo BASEURL . '/' . $venda->imagem; ?>"></td>
                            <td>R$ <?php echo number_format($venda->valor, 2, ',', '.'); ?></td>
                            <td><?php echo $venda->legenda; ?></td>
                            <td><?php echo $venda->data; ?>h</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <br><br><br>
                <center>
                    <i class="fas fa-sad-tear" style="font-size:40px;opacity:0.70"></i>
                    <br><br>
                    <h6 style="opacity:0.70">Não houve vendas hoje!</h6>
                </center>
            <?php endif; ?>
        </div>
    </div>

    <div class="card card-two col-lg-6 content-div">
        <div class="card-body">
            <h5 class="card-title" style="text-align:center">
                <i class="fas fa-cart-arrow-down" style="color:#00cc99;"></i>
                Vendido até o momento
            </h5>

            <center><small>Hoje: <?php echo date('d/m'); ?></small></center>

            <center style="margin-top:20px">
                <div>
                    <h3>R$ <?php echo number_format($totalVendasNoDia, 2, ',', '.'); ?></h3>
                    <?php foreach ($totalValorVendaPorMeioDePagamentoNoDia as $tipo): ?>
                        <span class="badge" style="background:#d9eefc;padding:5px;margin:2px;">
                            <?php echo $tipo->legenda; ?> R$ <?php echo real($tipo->totalVendas); ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <br><br>

                <?php if (is_null($totalVendaNoDiaAnterior)): ?>
                    <small>Nenhuma venda foi realizada ontem!</small>
                <?php else: ?>
                    <small>Vendido ontem: <b>R$ <?php echo real($totalVendaNoDiaAnterior); ?></b></small>
                <?php endif; ?>
            </center>
        </div>
    </div>
</div>

<div id="modal-cupom-fiscal-padrao" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo-cupom-fiscal-padrao" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titulo-cupom-fiscal-padrao"><i class="fas fa-file-invoice"></i> Nota fiscal emitida (mock)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cupom-fiscal-body-padrao"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="imprimirCupomFiscalPadrao()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASEURL; ?>/assets/js/core/jquery.min.js"></script>
<script type="text/javascript">
    const PDV_PADRAO_ROUTES = <?php echo json_encode($pdvPadraoRoutes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const ULTIMA_NOTA_FISCAL_MOCK = <?php echo json_encode($ultimaNotaFiscalMock, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    function pdvPadraoUrl(path) {
        if (typeof appendTabToUrl === 'function') {
            return appendTabToUrl(path, window.PDV_TAB_ID || null);
        }
        return path;
    }

    $(function () {
        obterProdutosNoCaixaPadrao();
        obterValorTotalDosProdutosNoCaixaPadrao();
        handlePagamentoPadrao();
        if (ULTIMA_NOTA_FISCAL_MOCK) {
            abrirModalCupomFiscalPadrao(ULTIMA_NOTA_FISCAL_MOCK);
        }

        $('#busca_produto_padrao').on('input', function () {
            const termo = ($(this).val() || '').toLowerCase();
            $('#produto_rapido_id option').each(function () {
                if (!this.value) return;
                const nome = (this.dataset.nome || '');
                const codigo = (this.dataset.codigo || '');
                $(this).prop('hidden', termo !== '' && nome.indexOf(termo) === -1 && codigo.indexOf(termo) === -1);
            });
        });
    });

    function adicionarProdutoSelecionadoNoPadrao() {
        var id = $("#produto_rapido_id").val();
        if (!id) {
            modalValidacao('Validação', 'Selecione um produto para adicionar.');
            return false;
        }
        colocarProdutosNoCaixaPadrao(id, 'ok');
    }

    function abrirModalCupomFiscalPadrao(nota) {
        window.__cupomFiscalPadraoAtual = nota;
        $("#cupom-fiscal-body-padrao").html(montarHtmlCupomPadrao(nota));
        $("#modal-cupom-fiscal-padrao").modal({ backdrop: 'static' });
    }

    function montarHtmlCupomPadrao(nota) {
        var totais = nota && nota.totais ? nota.totais : {};
        var impostos = nota && nota.impostos && nota.impostos.valores ? nota.impostos.valores : {};
        var aliquotas = nota && nota.impostos && nota.impostos.aliquotas ? nota.impostos.aliquotas : {};
        var itens = nota && nota.itens ? nota.itens : [];
        var emitente = nota && nota.emitente ? nota.emitente : {};
        var consumidor = nota && nota.consumidor ? nota.consumidor : {};
        var html = "<div id='printable-cupom-fiscal-padrao' style='font-family:Arial,sans-serif;font-size:12px;color:#222;max-width:760px;margin:0 auto'>";
        html += "<div style='border:1px solid #222;padding:10px'>";
        html += "<div style='text-align:center;border-bottom:1px solid #222;padding-bottom:8px;margin-bottom:8px'>";
        html += "<div style='font-size:16px;font-weight:bold;text-transform:uppercase'>" + nfEscapePadrao(emitente.nome || 'Empresa não informada') + "</div>";
        html += "<div>" + nfEscapePadrao(emitente.municipio || 'Goiânia') + " - " + nfEscapePadrao(emitente.uf || 'GO') + (emitente.telefone ? " | Tel: " + nfEscapePadrao(emitente.telefone) : "") + "</div>";
        if (emitente.email) html += "<div>" + nfEscapePadrao(emitente.email) + "</div>";
        html += "</div>";
        html += "<div style='text-align:center;margin-bottom:8px'>";
        html += "<div style='font-weight:bold;font-size:14px'>DOCUMENTO AUXILIAR DA NOTA FISCAL DE CONSUMIDOR ELETRÔNICA</div>";
        html += "<div style='font-size:11px'>Emissão em ambiente de homologação - sem valor fiscal</div>";
        html += "</div>";
        html += "<div style='display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:8px'>";
        html += "<div style='border:1px solid #999;padding:6px'><b>Modelo</b><br>" + nfEscapePadrao(nota.modelo || 'NFC-e') + "</div>";
        html += "<div style='border:1px solid #999;padding:6px'><b>Série</b><br>" + nfEscapePadrao(nota.serie || '-') + "</div>";
        html += "<div style='border:1px solid #999;padding:6px'><b>Número</b><br>" + nfEscapePadrao(nota.numero_nf || '-') + "</div>";
        html += "</div>";
        html += "<div style='border:1px solid #999;padding:6px;margin-bottom:8px'><b>Chave de acesso</b><br><span style='font-size:11px;word-break:break-word'>" + nfFormatarChavePadrao(nota.chave_acesso || '') + "</span></div>";
        html += "<div style='border:1px solid #999;padding:6px;margin-bottom:8px'><b>Consumidor</b><br>" + nfEscapePadrao(consumidor.nome || 'Consumidor não identificado') + "</div>";
        html += "<table style='width:100%;border-collapse:collapse;margin-bottom:8px' border='1' cellpadding='5'><thead><tr style='background:#f1f3f5'><th style='text-align:left'>Descrição</th><th style='width:70px'>Qtd</th><th style='width:100px;text-align:right'>Valor</th></tr></thead><tbody>";
        for (var i = 0; i < itens.length; i++) {
            var item = itens[i] || {};
            html += "<tr><td>" + nfEscapePadrao(item.produto || '-') + "</td><td style='text-align:center'>" + nfEscapePadrao(item.quantidade || 0) + "</td><td style='text-align:right'>R$ " + formatarMoedaCupomPadrao(item.total || 0) + "</td></tr>";
        }
        html += "</tbody></table>";
        html += "<div style='display:grid;grid-template-columns:1fr 1fr;gap:8px'>";
        html += "<div style='border:1px solid #999;padding:8px'><b>Tributos incidentes</b><br>ICMS (" + nfEscapePadrao(aliquotas.icms || 0) + "%): R$ " + formatarMoedaCupomPadrao(impostos.icms || 0) + "<br>PIS (" + nfEscapePadrao(aliquotas.pis || 0) + "%): R$ " + formatarMoedaCupomPadrao(impostos.pis || 0) + "<br>COFINS (" + nfEscapePadrao(aliquotas.cofins || 0) + "%): R$ " + formatarMoedaCupomPadrao(impostos.cofins || 0) + "</div>";
        html += "<div style='border:1px solid #999;padding:8px'><b>Totais</b><br>Produtos: R$ " + formatarMoedaCupomPadrao(totais.total_produtos || 0) + "<br>Tributos: R$ " + formatarMoedaCupomPadrao(totais.total_impostos || 0) + "<br><span style='font-size:14px'><b>Total da NF: R$ " + formatarMoedaCupomPadrao(totais.total_nota || 0) + "</b></span></div>";
        html += "</div>";
        html += "<div style='text-align:center;margin-top:10px;font-size:11px'>Consulta não disponível para documento simulado. Goiânia - GO | " + nfEscapePadrao(nota.emitida_em || '-') + "</div>";
        html += "</div></div>";
        return html;
    }

    function imprimirCupomFiscalPadrao() {
        var cupom = window.__cupomFiscalPadraoAtual;
        if (!cupom) return;
        var conteudo = montarHtmlCupomPadrao(cupom);
        var janela = window.open('', '_blank');
        if (!janela) return;
        janela.document.write("<html><head><title>Nota fiscal</title></head><body>" + conteudo + "</body></html>");
        janela.document.close();
        janela.focus();
        janela.print();
    }

    function formatarMoedaCupomPadrao(valor) {
        return Number(valor || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function nfEscapePadrao(valor) {
        return String(valor == null ? '' : valor).replace(/[&<>"']/g, function (s) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[s];
        });
    }

    function nfFormatarChavePadrao(chave) {
        chave = String(chave || '').replace(/\D/g, '');
        return chave ? nfEscapePadrao(chave.replace(/(.{4})/g, '$1 ').trim()) : '-';
    }

    function colocarProdutosNoCaixaPadrao(id, estoque) {
        if (estoque === 'esgotado') {
            modalValidacao('Validação', 'Produto sem estoque para venda.');
            return false;
        }

        $.get(pdvPadraoUrl(PDV_PADRAO_ROUTES.adicionar + "/" + id), function (data) {
            var resposta = parseJsonSeguroPadrao(data);
            if (!resposta || resposta.status === false) {
                modalValidacao('Erro', resposta && resposta.message ? resposta.message : 'Não foi possível adicionar o produto.');
                return;
            }
            renderizarOuAtualizarProdutoNoCaixaPadrao(resposta.item);
            obterValorTotalDosProdutosNoCaixaPadrao();
            calcularTrocoPadrao();
        });
    }

    function obterProdutosNoCaixaPadrao() {
        $.get(pdvPadraoUrl(PDV_PADRAO_ROUTES.mesa), function (data) {
            var produtos = parseJsonSeguroPadrao(data) || [];
            var html = "";
            if (produtos.length) {
                $.each(produtos, function (i, item) {
                    if (itemDaMesaValidoPadrao(item)) {
                        html += montarLinhaProdutoPadrao(item);
                    }
                });
            }
            verificaSeTemProdutosNoCaixaPadrao(html);
            $(".tabela-de-produto-padrao tbody").html(html);
        });
    }

    function montarLinhaProdutoPadrao(value) {
        var t = "<tr id='id-tr-padrao-" + value.id + "'>";
        if (!value.imagem || value.imagem == '') {
            t += "<td><i class='fas fa-box-open' style='font-size:20px'></i></td>";
        } else {
            t += "<td><img class='imagem-perfil' src='" + getDomain() + "/" + value.imagem + "'></td>";
        }
        t += "<td>" + value.produto + "</td>";
        t += "<td class='hidden-when-mobile'>" + real(value.preco) + "</td>";
        t += "<td><input type='number' class='campo-quantidade' value='" + value.quantidade + "' onchange=\"alterarQuantidadeNoCaixaPadrao(" + value.id + ", this.value, $(this))\"></td>";
        t += "<td>" + real(value.total) + "</td>";
        t += "<td><button type='button' class='btn-sm btn-link' onclick='retirarProdutoNoCaixaPadrao(" + value.id + ", this)'><i class='fas fa-times' style='color:#cc0000;font-size:18px'></i></button></td>";
        t += "</tr>";
        return t;
    }

    function itemDaMesaValidoPadrao(value) {
        return !!(value && typeof value.id !== 'undefined' && typeof value.produto !== 'undefined' && typeof value.total !== 'undefined');
    }

    function parseJsonSeguroPadrao(data) {
        if (typeof data === 'object') return data;
        if (typeof data === 'string' && /<(html|!DOCTYPE|body|form)/i.test(data)) {
            return { status: false, html_response: true, message: 'Sua sessão desta aba expirou ou perdeu o contexto. Recarregue a página e tente novamente.' };
        }
        try { return JSON.parse(data); } catch (e) { return null; }
    }

    function renderizarOuAtualizarProdutoNoCaixaPadrao(value) {
        if (!itemDaMesaValidoPadrao(value)) return;
        var tbody = $(".tabela-de-produto-padrao tbody");
        var tr = $("#id-tr-padrao-" + value.id);
        var linha = montarLinhaProdutoPadrao(value);

        verificaSeTemProdutosNoCaixaPadrao(linha);

        if (tr.length) {
            tr.replaceWith(linha);
        } else {
            if (tbody.find("td[colspan='6']").length) {
                tbody.html("");
            }
            tbody.append(linha);
        }
    }

    function alterarQuantidadeNoCaixaPadrao(id, quantidade, element) {
        quantidade = Number(quantidade);
        if (quantidade <= 0) {
            element.val(1);
            return;
        }

        $.get(pdvPadraoUrl(PDV_PADRAO_ROUTES.quantidade + "/" + id + "/" + quantidade), function (data) {
            var obj = parseJsonSeguroPadrao(data);
            if (obj && obj.quantidade_insuficiente == true) {
                modalValidacao('Estoque', 'Este produto tem apenas ' + obj.unidades + ' unidades em estoque.');
                element.val(obj.unidades);
                return;
            }
            obterProdutosNoCaixaPadrao();
            obterValorTotalDosProdutosNoCaixaPadrao();
            calcularTrocoPadrao();
        });
    }

    function retirarProdutoNoCaixaPadrao(id, item) {
        $.get(pdvPadraoUrl(PDV_PADRAO_ROUTES.retirar + "/" + id), function () {
            $(item).closest('tr').remove();
            obterValorTotalDosProdutosNoCaixaPadrao();
            verificaSeTemProdutosNoCaixaPadrao($(".tabela-de-produto-padrao tbody tr").length);
            calcularTrocoPadrao();
        });
    }

    function obterValorTotalDosProdutosNoCaixaPadrao() {
        $.get(pdvPadraoUrl(PDV_PADRAO_ROUTES.total), function (data) {
            var total = parseJsonSeguroPadrao(data) || { total: 0 };
            $("#b-mostra-valor-total-padrao").val(real(total.total || 0));
        });
    }

    function verificaSeTemProdutosNoCaixaPadrao(t) {
        if (t.length == 0 || t == 1) {
            $(".tabela-de-produto-padrao tbody").html("<tr><td colspan='6' style='text-align:center;padding:20px;color:#999'>Nenhum item passado no caixa.</td></tr>");
            $("#button-confirmar-venda-padrao").prop('disabled', true);
            $("#id_meio_pagamento_padrao").prop('disabled', true);
            reiniciaPagamentoPadrao();
        } else {
            $("#button-confirmar-venda-padrao").prop('disabled', false);
            $("#id_meio_pagamento_padrao").prop('disabled', false);
        }
    }

    function handlePagamentoPadrao() {
        var meio = parseInt($("#id_meio_pagamento_padrao").val(), 10);
        if (meio === 1) {
            $("#div-valor-recebido-padrao").show('fast');
            $("#div-troco-padrao").show('fast');
            calcularTrocoPadrao();
        } else {
            $("#div-valor-recebido-padrao").hide('fast');
            $("#div-troco-padrao").hide('fast');
            $("#troco_padrao_hidden").val('');
            $("#button-confirmar-venda-padrao").prop('disabled', $(".tabela-de-produto-padrao tbody tr").length === 0);
        }
    }

    function calcularTrocoPadrao() {
        var valorRecebido = $("#valor_recebido_padrao").val();
        var meioPagamento = $("#id_meio_pagamento_padrao").val();
        if (meioPagamento != 'selecione' && meioPagamento == 1) {
            $.get(pdvPadraoUrl(PDV_PADRAO_ROUTES.troco + "/" + in64(valorRecebido)), function (data) {
                var troco = parseJsonSeguroPadrao(data);
                if (troco && troco.message) {
                    $("#button-confirmar-venda-padrao").prop('disabled', true);
                    $("#valor_recebido_padrao").css("background", '#f6e1e1');
                    $("#label-valor-pago-padrao").html("<br><b style='color:#c34747'>" + troco.message + "</b>");
                } else {
                    $("#button-confirmar-venda-padrao").prop('disabled', false);
                    $("#valor_recebido_padrao").css("background", 'transparent');
                    $("#label-valor-pago-padrao").text("Valor pago");
                }
                var valorTroco = troco && typeof troco.valor !== 'undefined' ? troco.valor : 0;
                $("#input_troco_padrao").val(Number(valorTroco).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
                $("#troco_padrao_hidden").val(valorTroco);
            });
        }
    }

    function reiniciaPagamentoPadrao() {
        $("#id_meio_pagamento_padrao").prop('selectedIndex', 0);
        $("#valor_recebido_padrao").val('');
        $("#input_troco_padrao").val('');
        $("#troco_padrao_hidden").val('');
        $("#div-troco-padrao").hide('fast');
        $("#div-valor-recebido-padrao").hide('fast');
        $("#valor_recebido_padrao").css("background", 'transparent');
        $("#label-valor-pago-padrao").text("Valor pago");
    }

    function validarVendaPadrao() {
        if ($("#id_meio_pagamento_padrao").val() == 'selecione') {
            modalValidacao('Validação', 'Selecione um meio de pagamento.');
            return false;
        }
        if ($("#button-confirmar-venda-padrao").prop('disabled')) {
            modalValidacao('Validação', 'Adicione itens válidos antes de finalizar a venda.');
            return false;
        }
        modalValidacao('Salvando', 'Processando venda rápida...');
        $(".close").hide();
        return true;
    }
</script>
