function pdvBuildUrl(path) {
    var base = getDomain() + path;
    if (typeof appendTabToUrl === 'function') {
        return appendTabToUrl(base, window.PDV_TAB_ID || null);
    }
    return base;
}

$(document).ready(function () {
    obterProdutosDaMesa();
    obterValorTotalDosProdutosNaMesa();
    handleAoMudarMeioDePagamento();
});

function colocarProdutosNaMesa(id, item, seProdutoEsgotado) {
    if (seProdutoEsgotado == 'esgotado') {
        modalValidacao('Validação', 'Produto esgotado.<br>Reabasteça o estoque.');
        return false;
    }
    var rota = pdvBuildUrl("/pdvDiferencial/colocarProdutosNaMesa/" + id);
    modalValidacao('Adicionando', 'Aguarde...');
    $.get(rota, function (data) {
        var resposta = parseJsonSeguro(data);
        if (!resposta || resposta.status === false || !itemDaMesaValido(resposta.item)) {
            obterProdutosDaMesa();
            modalValidacao('Erro', (resposta && resposta.message) ? resposta.message : 'Não foi possível adicionar o produto.');
            return;
        }
        renderizarOuAtualizarProdutoNaMesa(resposta.item);
        obterValorTotalDosProdutosNaMesa();
        calcularTroco();
        setTimeout(modalValidacaoClose, 600);
    });
}

function obterProdutosDaMesa() {
    var rota = pdvBuildUrl("/pdvDiferencial/obterProdutosDaMesa");
    $.get(rota, function (data) {
        var produtos = parseJsonSeguro(data) || [];
        var t = "";
        if (produtos.length != 0) {
            $.each(produtos, function (i, v) {
                if (itemDaMesaValido(v)) {
                    t += montarLinhaProduto(v);
                }
            });
        }
        verificaSeTemProdutosNaMesa(t);
        $(".tabela-de-produto tbody").html(t);
    });
}

function obterOultimoProdutoColocadoNaMesa() {
    var rota = pdvBuildUrl("/pdvDiferencial/obterProdutosDaMesa/ultimo");
    $.get(rota, function (data) {
        var value = parseJsonSeguro(data);
        renderizarOuAtualizarProdutoNaMesa(value);
        obterValorTotalDosProdutosNaMesa();
    });
}

function montarLinhaProduto(value) {
    var t = "<tr id='id-tr-" + value.id + "'>";
    if (!value.imagem || value.imagem == '') {
        t += "<td><i class='fas fa-box-open' style='font-size:20px'></i></td>";
    } else {
        t += "<td><img class='img-produto-seleionado' src='" + getDomain() + "/" + value.imagem + "'></td>";
    }
    t += "<td>" + value.produto + "</td>";
    t += "<td class='hidden-when-mobile'>" + real(value.preco) + "</td>";
    t += "<td><input type='number' class='campo-quantidade' value='" + value.quantidade + "' onchange=\"alterarAquantidadeDeUmProdutoNaMesa(" + value.id + ", this.value, $(this))\"></td>";
    t += "<td class='pegarTotal'>" + real(value.total) + "</td>";
    t += "<td><button class='btn-sm btn-link' onclick='retirarProdutoDaMesa(" + value.id + ", this)'><i class='fas fa-times' style='color:#cc0000;font-size:18px'></i></button></td>";
    t += "</tr>";
    return t;
}

function itemDaMesaValido(value) {
    return !!(
        value &&
        typeof value.id !== 'undefined' &&
        typeof value.produto !== 'undefined' &&
        typeof value.preco !== 'undefined' &&
        typeof value.quantidade !== 'undefined' &&
        typeof value.total !== 'undefined'
    );
}

function parseJsonSeguro(data) {
    if (typeof data === 'object') {
        return data;
    }
    try {
        return JSON.parse(data);
    } catch (e) {
        return null;
    }
}

function renderizarOuAtualizarProdutoNaMesa(value) {
    if (!itemDaMesaValido(value)) {
        return;
    }

    var t = montarLinhaProduto(value);
    var tbody = $(".tabela-de-produto tbody");
    var tr = $("#id-tr-" + value.id);

    verificaSeTemProdutosNaMesa(t);

    if (tr.length) {
        tr.replaceWith(t);
    } else {
        if (tbody.find("td[colspan='6']").length) {
            tbody.html("");
        }
        tbody.append(t);
    }
}

function alterarAquantidadeDeUmProdutoNaMesa(id, quantidade, element) {
    quantidade = Number(quantidade);
    if (quantidade <= 0) { element.val(1); return; }
    modalValidacao('Aplicando', 'Aguarde...');
    var rota = pdvBuildUrl("/pdvDiferencial/alterarAquantidadeDeUmProdutoNaMesa/" + id + "/" + quantidade);
    $.get(rota, function (data) {
        var obj = JSON.parse(data);
        if (obj.quantidade_insuficiente == true) {
            var leg = (obj.unidades > 1) ? 'unidades' : 'unidade';
            modalValidacao('Estoque', 'Este produto tem apenas ' + obj.unidades + ' ' + leg + ' em estoque.');
            element.val(obj.unidades);
            return;
        }
        $(".tabela-de-produto tbody").empty();
        obterProdutosDaMesa();
        obterValorTotalDosProdutosNaMesa();
        calcularTroco();
        setTimeout(modalValidacaoClose, 600);
    });
}

function retirarProdutoDaMesa(id, item) {
    var rota = pdvBuildUrl("/pdvDiferencial/retirarProdutoDaMesa/" + id);
    $.get(rota, function () {
        var tr = $(item).closest('tr');
        tr.fadeOut(300, function () {
            tr.remove();
            obterValorTotalDosProdutosNaMesa();
            verificaSeTemProdutosNaMesa($(".tabela-de-produto tbody tr").length);
            calcularTroco();
        });
    });
}

function obterValorTotalDosProdutosNaMesa() {
    var rota = pdvBuildUrl("/pdvDiferencial/obterValorTotalDosProdutosNaMesa/");
    $.get(rota, function (data) {
        var total = JSON.parse(data);
        $("#b-mostra-valor-total").val(real(total.total));
    });
}

function saveVendasViaSession(token) {
    var meioPagamento   = $('#id_meio_pagamento').val();
    var dataCompensacao = $('#data_compensacao_boleto').val();
    var valorRecebido   = $("#valor_recebido").val();
    var trocoVal        = $("#input_troco").val();

    if (meioPagamento == "selecione") {
        modalValidacao('Atenção', 'Selecione um meio de pagamento.');
        return false;
    }
    if (meioPagamento == 4 && dataCompensacao == "") {
        modalValidacao('Atenção', 'Informe a data de compensação do boleto.');
        return;
    }

    $("#button-confirmar-venda").prop('disabled', true);
    modalValidacao('Salvando', 'Processando...');

    function normalizaMoeda(v) {
        return (v || '0').replace(/[^\d,]/g, '').replace(',', '.') || '0';
    }

    var payload = {
        'id_meio_pagamento': meioPagamento,
        'data_compensacao':  dataCompensacao,
        'valor_recebido':    normalizaMoeda(valorRecebido),
        'troco':             normalizaMoeda(trocoVal),
        '_token':            token
    };

    var rota = pdvBuildUrl("/pdvDiferencial/saveVendasViaSession");

    $.post(rota, payload, function (result) {
        var resp = null;
        try { resp = result ? JSON.parse(result) : null; } catch(e) {}

        if (resp && resp.status) {
            modalValidacaoClose();
            if (resp.nota_fiscal) {
                abrirModalCupomFiscal(resp.nota_fiscal);
            } else {
                modalValidacao('Sucesso', 'Venda registrada com sucesso!');
                setTimeout(function () { window.location.reload(); }, 1200);
            }
        } else {
            var msg = (resp && resp.error) ? resp.error
                    : (resp && resp.msg)   ? resp.msg
                    : 'Verifique o carrinho e tente novamente.';
            modalValidacao('Erro ao salvar', msg);
            $("#button-confirmar-venda").prop('disabled', false);
        }
    }).fail(function (xhr) {
        modalValidacao('Erro', 'Falha: HTTP ' + xhr.status);
        $("#button-confirmar-venda").prop('disabled', false);
    });
}

function abrirModalCupomFiscal(nota) {
    window.__cupomFiscalAtual = nota;
    var html = montarHtmlCupomFiscal(nota);
    $("#cupom-fiscal-body").html(html);
    $("#modal-cupom-fiscal").modal({ backdrop: 'static', keyboard: false });
    $("#modal-cupom-fiscal").off('hidden.bs.modal.cupom').on('hidden.bs.modal.cupom', function () {
        window.location.reload();
    });
}

function montarHtmlCupomFiscal(nota) {
    var totais = nota && nota.totais ? nota.totais : {};
    var impostos = nota && nota.impostos && nota.impostos.valores ? nota.impostos.valores : {};
    var aliquotas = nota && nota.impostos && nota.impostos.aliquotas ? nota.impostos.aliquotas : {};
    var itens = nota && nota.itens ? nota.itens : [];
    var emitente = nota && nota.emitente ? nota.emitente : {};
    var consumidor = nota && nota.consumidor ? nota.consumidor : {};

    var html = "";
    html += "<div id='printable-cupom-fiscal' style='font-family:Arial,sans-serif;font-size:12px;color:#222;max-width:760px;margin:0 auto'>";
    html += "<div style='border:1px solid #222;padding:10px'>";
    html += "<div style='text-align:center;border-bottom:1px solid #222;padding-bottom:8px;margin-bottom:8px'>";
    html += "<div style='font-size:16px;font-weight:bold;text-transform:uppercase'>" + nfEscape(emitente.nome || 'Empresa não informada') + "</div>";
    html += "<div>" + nfEscape(emitente.municipio || 'Goiânia') + " - " + nfEscape(emitente.uf || 'GO') + (emitente.telefone ? " | Tel: " + nfEscape(emitente.telefone) : "") + "</div>";
    if (emitente.email) html += "<div>" + nfEscape(emitente.email) + "</div>";
    html += "</div>";
    html += "<div style='text-align:center;margin-bottom:8px'>";
    html += "<div style='font-weight:bold;font-size:14px'>DOCUMENTO AUXILIAR DA NOTA FISCAL DE CONSUMIDOR ELETRÔNICA</div>";
    html += "<div style='font-size:11px'>Emissão em ambiente de homologação - sem valor fiscal</div>";
    html += "</div>";
    html += "<div style='display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:8px'>";
    html += "<div style='border:1px solid #999;padding:6px'><b>Modelo</b><br>" + nfEscape(nota.modelo || 'NFC-e') + "</div>";
    html += "<div style='border:1px solid #999;padding:6px'><b>Série</b><br>" + nfEscape(nota.serie || '-') + "</div>";
    html += "<div style='border:1px solid #999;padding:6px'><b>Número</b><br>" + nfEscape(nota.numero_nf || '-') + "</div>";
    html += "</div>";
    html += "<div style='border:1px solid #999;padding:6px;margin-bottom:8px'><b>Chave de acesso</b><br><span style='font-size:11px;word-break:break-word'>" + nfFormatarChave(nota.chave_acesso || '') + "</span></div>";
    html += "<div style='border:1px solid #999;padding:6px;margin-bottom:8px'><b>Consumidor</b><br>" + nfEscape(consumidor.nome || 'Consumidor não identificado') + "</div>";
    html += "<table style='width:100%;border-collapse:collapse;margin-bottom:8px' border='1' cellpadding='5'>";
    html += "<thead><tr style='background:#f1f3f5'><th style='text-align:left'>Descrição</th><th style='width:70px'>Qtd</th><th style='width:100px;text-align:right'>Valor</th></tr></thead><tbody>";
    for (var i = 0; i < itens.length; i++) {
        var item = itens[i] || {};
        html += "<tr>";
        html += "<td>" + nfEscape(item.produto || '-') + "</td>";
        html += "<td style='text-align:center'>" + nfEscape(item.quantidade || 0) + "</td>";
        html += "<td style='text-align:right'>R$ " + formatarMoedaCupom(item.total || 0) + "</td>";
        html += "</tr>";
    }
    html += "</tbody></table>";
    html += "<div style='display:grid;grid-template-columns:1fr 1fr;gap:8px'>";
    html += "<div style='border:1px solid #999;padding:8px'><b>Tributos incidentes</b><br>ICMS (" + nfEscape(aliquotas.icms || 0) + "%): R$ " + formatarMoedaCupom(impostos.icms || 0) + "<br>PIS (" + nfEscape(aliquotas.pis || 0) + "%): R$ " + formatarMoedaCupom(impostos.pis || 0) + "<br>COFINS (" + nfEscape(aliquotas.cofins || 0) + "%): R$ " + formatarMoedaCupom(impostos.cofins || 0) + "</div>";
    html += "<div style='border:1px solid #999;padding:8px'><b>Totais</b><br>Produtos: R$ " + formatarMoedaCupom(totais.total_produtos || 0) + "<br>Tributos: R$ " + formatarMoedaCupom(totais.total_impostos || 0) + "<br><span style='font-size:14px'><b>Total da NF: R$ " + formatarMoedaCupom(totais.total_nota || 0) + "</b></span></div>";
    html += "</div>";
    html += "<div style='text-align:center;margin-top:10px;font-size:11px'>Consulta não disponível para documento simulado. Goiânia - GO | " + nfEscape(nota.emitida_em || '-') + "</div>";
    html += "</div></div>";

    return html;
}

function imprimirCupomFiscalAtual() {
    var cupom = window.__cupomFiscalAtual;
    if (!cupom) return;
    var conteudo = montarHtmlCupomFiscal(cupom);
    var janela = window.open('', '_blank');
    if (!janela) return;
    janela.document.write("<html><head><title>Nota fiscal</title></head><body>" + conteudo + "</body></html>");
    janela.document.close();
    janela.focus();
    janela.print();
}

function formatarMoedaCupom(valor) {
    var numero = Number(valor || 0);
    return numero.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function nfEscape(valor) {
    return String(valor == null ? '' : valor).replace(/[&<>"']/g, function (s) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[s];
    });
}

function nfFormatarChave(chave) {
    chave = String(chave || '').replace(/\D/g, '');
    return chave ? nfEscape(chave.replace(/(.{4})/g, '$1 ').trim()) : '-';
}

function verificaSeTemProdutosNaMesa(t) {
    if (t.length == 0 || t == 1) {
        var vazio = "<tr><td colspan='6' style='text-align:center;padding:20px;color:#999'>Nenhum produto selecionado!</td></tr>";
        $(".tabela-de-produto tbody").html(vazio);
        reiniciaElementosDePagamento();
        $("#id_meio_pagamento").prop('disabled', true);
        $("#button-confirmar-venda").prop('disabled', true);
    } else {
        $("#id_meio_pagamento").prop('disabled', false);
        $("#button-confirmar-venda").prop('disabled', false);
    }
}

function handleAoMudarMeioDePagamento() {
    var elMeio = document.querySelector("#id_meio_pagamento");
    var elData = document.querySelector("#data-compensacao");
    if (!elMeio) return;
    var meio = parseInt(elMeio.value);
    if (meio == 1) {
        $("#div-valor-recebido").show('fast');
        $("#div-troco").show('fast');
        calcularTroco();
    } else {
        $("#div-valor-recebido").hide('fast');
        $("#div-troco").hide('fast');
        $("#button-confirmar-venda").prop('disabled', false);
    }
    if (meio !== 4) {
        elData.classList.remove("visivel");
        elData.value = "";
    } else {
        elData.classList.add("visivel");
    }
}

function calcularTroco() {
    var valorRecebido = $("#valor_recebido").val();
    var meioPagamento = $("#id_meio_pagamento").val();
    if (meioPagamento != 'selecione' && meioPagamento == 1) {
        $("#div-troco").show('fast');
        $("#input_troco").val('...');
        var rota = pdvBuildUrl("/pdvDiferencial/calcularTroco/" + in64(valorRecebido));
        $.get(rota, function (data) {
            var troco = JSON.parse(data);
            if (troco.message != false) {
                $("#button-confirmar-venda").prop('disabled', true);
                $("#valor_recebido").css("background", '#f6e1e1');
                $("#label-valor-pago").html("<br><b style='color:#c34747'>" + troco.message + "</b>");
            } else {
                $("#button-confirmar-venda").prop('disabled', false);
                $("#valor_recebido").css("background", 'transparent');
                $("#label-valor-pago").text("Valor pago");
            }
            $("#input_troco").val(troco.valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
        });
    }
}

function reiniciaElementosDePagamento() {
    $("#id_meio_pagamento").prop('selectedIndex', 0);
    $("#valor_recebido").val('');
    $("#input_troco").val('');
    $("#div-troco").hide('fast');
    $("#div-valor-recebido").hide('fast');
    $("#valor_recebido").css("background", 'transparent');
    $("#label-valor-pago").text("Valor pago");
}
