<?php
use System\HtmlComponents\FlashMessage\FlashMessage;
?>
<style>
    .cat-busca { font-size:18px;height:46px; }
    .cat-lista { max-height:520px;overflow-y:auto;margin-top:10px; }
    .cat-item { display:block;width:100%;text-align:left;border:1px solid #e3e3e3;background:#fff;border-radius:6px;padding:8px 10px;margin-bottom:6px;cursor:pointer; }
    .cat-item:hover, .cat-item.ativo { border-color:#51cbce;background:#f0fbfb; }
    .cat-item .nome { font-weight:600;display:block; }
    .cat-item .meta { font-size:12px;color:#888; }
    .cat-item .preco { float:right;font-weight:600; }
    .cat-info dt { font-size:12px;color:#888;font-weight:normal;margin-top:10px; }
    .cat-info dd { margin:0;font-size:16px;font-weight:600; }
    .cat-destaque { font-size:26px; }
    .cat-status { border-radius:8px;padding:12px 14px;margin-top:14px;font-weight:600; }
    .cat-status.ok { background:#d4edda;color:#155724; }
    .cat-status.bloqueado { background:#f8d7da;color:#721c24; }
    .cat-status.aviso { background:#fff3cd;color:#856404; }
    .cat-total { font-size:28px;font-weight:700; }
    .cat-vazio { color:#aaa;text-align:center;padding:30px 10px; }
    .cat-carrinho-item td { vertical-align:middle !important; }
    .cat-carrinho-total { font-size:24px;font-weight:700; }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="card content-div">
            <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap">
                <h5 class="card-title" style="margin:0"><i class="fas fa-tags"></i> Consulta de Preços e Simulador</h5>
                <?php if (!empty($podeGerenciar)): ?>
                    <a href="<?= BASEURL ?>/catalogo/gerenciar" class="btn btn-sm btn-info">
                        <i class="fas fa-cog"></i> Gerenciar catálogo
                    </a>
                <?php endif; ?>
            </div>
            <?php FlashMessage::show(); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card content-div">
            <div class="card-body">
                <h6><i class="fas fa-search"></i> A. Pesquisa</h6>
                <input type="text" id="cat-busca" class="form-control cat-busca" autocomplete="off" autofocus
                       placeholder="Nome, descrição, código interno ou de barras">
                <div id="cat-lista" class="cat-lista"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card content-div">
            <div class="card-body">
                <h6><i class="fas fa-info-circle"></i> B. Informações comerciais</h6>
                <div id="cat-ficha"><div class="cat-vazio">Selecione um produto para ver a ficha comercial.</div></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card content-div">
            <div class="card-body">
                <h6><i class="fas fa-calculator"></i> C. Simulador</h6>
                <div id="cat-sim-vazio" class="cat-vazio">Selecione um produto para simular.</div>
                <div id="cat-sim" style="display:none">
                    <div class="row">
                        <div class="col-6">
                            <label>Quantidade</label>
                            <input type="number" min="1" step="1" id="sim-qtd" class="form-control" value="1">
                        </div>
                        <div class="col-6">
                            <label>Preço final (un.)</label>
                            <input type="text" inputmode="decimal" id="sim-final" class="form-control" placeholder="0,00">
                        </div>
                        <div class="col-6" style="margin-top:8px">
                            <label>Desconto (%)</label>
                            <input type="text" inputmode="decimal" id="sim-pct" class="form-control" placeholder="0,00">
                        </div>
                        <div class="col-6" style="margin-top:8px">
                            <label>Desconto (R$)</label>
                            <input type="text" inputmode="decimal" id="sim-reais" class="form-control" placeholder="0,00">
                        </div>
                    </div>
                    <div style="margin-top:16px">
                        <small class="text-muted">Valor total</small><br>
                        <span class="cat-total" id="sim-total">-</span>
                    </div>
                    <div id="sim-status" class="cat-status" style="display:none"></div>
                    <div id="sim-custo" class="cat-status" style="display:none"></div>
                    <button type="button" class="btn btn-sm btn-default" id="sim-limpar" style="margin-top:12px">
                        <i class="fas fa-undo"></i> Limpar desconto
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="sim-adicionar" style="margin-top:12px" disabled>
                        <i class="fas fa-cart-plus"></i> Adicionar à compra
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card content-div">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap">
                    <h6 style="margin:0"><i class="fas fa-shopping-cart"></i> Produtos da compra</h6>
                    <div><small class="text-muted" id="cat-carrinho-contagem">Nenhum produto adicionado</small></div>
                </div>
                <div id="cat-carrinho-vazio" class="cat-vazio">Selecione e simule cada produto, depois clique em “Adicionar à compra”.</div>
                <div id="cat-carrinho-conteudo" class="table-responsive" style="display:none;margin-top:12px">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço final (un.)</th>
                                <th>Subtotal</th>
                                <th>Situação</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cat-carrinho-itens"></tbody>
                    </table>
                    <div id="cat-carrinho-alerta" class="cat-status aviso" style="display:none"></div>
                    <div style="text-align:right">
                        <small class="text-muted">Total da compra</small><br>
                        <span class="cat-carrinho-total" id="cat-carrinho-total">R$ 0,00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var BASE = "<?= BASEURL ?>";
    var TOKEN = "<?= TOKEN ?>";
    var brl = new Intl.NumberFormat('pt-BR', {style: 'currency', currency: 'BRL'});

    var busca = document.getElementById('cat-busca');
    var lista = document.getElementById('cat-lista');
    var ficha = document.getElementById('cat-ficha');
    var simBox = document.getElementById('cat-sim');
    var simVazio = document.getElementById('cat-sim-vazio');
    var inQtd = document.getElementById('sim-qtd');
    var inFinal = document.getElementById('sim-final');
    var inPct = document.getElementById('sim-pct');
    var inReais = document.getElementById('sim-reais');
    var outTotal = document.getElementById('sim-total');
    var outStatus = document.getElementById('sim-status');
    var outCusto = document.getElementById('sim-custo');
    var addBtn = document.getElementById('sim-adicionar');
    var cartEmpty = document.getElementById('cat-carrinho-vazio');
    var cartContent = document.getElementById('cat-carrinho-conteudo');
    var cartItems = document.getElementById('cat-carrinho-itens');
    var cartCount = document.getElementById('cat-carrinho-contagem');
    var cartTotal = document.getElementById('cat-carrinho-total');
    var cartAlert = document.getElementById('cat-carrinho-alerta');

    var produtos = [];
    var carrinho = [];
    var atual = null;
    var ultimaSimulacao = null;
    var modo = 'nenhum';
    var seqBusca = 0, seqSim = 0, tBusca = null, tSim = null;

    function money(v) { return brl.format(Number(v)); }
    function br(v) { return String(v).replace('.', ','); }
    function el(tag, cls, text) {
        var n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined && text !== null) n.textContent = text;
        return n;
    }

    function pesquisar(selecionarPrimeiro) {
        var n = ++seqBusca;
        fetch(BASE + '/catalogo/buscar?q=' + encodeURIComponent(busca.value), {
            credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (n !== seqBusca) return;
            produtos = d.produtos || [];
            renderLista();
            if (selecionarPrimeiro === true && produtos.length) selecionar(produtos[0]);
        }).catch(function () {
            lista.replaceChildren(el('div', 'cat-vazio', 'Não foi possível pesquisar. Recarregue a página.'));
        });
    }

    function renderLista() {
        lista.replaceChildren();
        if (!produtos.length) {
            lista.appendChild(el('div', 'cat-vazio', 'Nenhum produto encontrado.'));
            return;
        }
        produtos.forEach(function (p) {
            var b = el('button', 'cat-item');
            b.type = 'button';
            if (atual && atual.id === p.id) b.classList.add('ativo');
            var preco = el('span', 'preco', p.sem_preco_oficial ? 'sem preço' : money(p.preco_oficial));
            b.appendChild(preco);
            b.appendChild(el('span', 'nome', p.nome));
            var cod = [p.codigo_interno, p.codigo_barras].filter(Boolean).join(' · ');
            b.appendChild(el('span', 'meta', cod || 'sem código'));
            b.addEventListener('click', function () { selecionar(p); });
            lista.appendChild(b);
        });
    }

    function linha(dl, rotulo, valor, cls) {
        dl.appendChild(el('dt', null, rotulo));
        dl.appendChild(el('dd', cls || null, valor));
    }

    function selecionar(p) {
        atual = p;
        ultimaSimulacao = null;
        addBtn.disabled = true;
        clearTimeout(tSim);
        seqSim++;
        modo = 'nenhum';
        renderLista();

        var dl = el('dl', 'cat-info');
        linha(dl, 'Produto', p.nome);
        linha(dl, 'Código', [p.codigo_interno ? 'Interno: ' + p.codigo_interno : '', p.codigo_barras ? 'Barras: ' + p.codigo_barras : ''].filter(Boolean).join(' | ') || '-');
        if (p.descricao) linha(dl, 'Descrição', p.descricao);
        linha(dl, 'Preço oficial', p.sem_preco_oficial ? 'Não definido' : money(p.preco_oficial), 'cat-destaque');
        linha(dl, 'Preço mínimo (menor preço autorizado)', p.sem_preco_minimo ? 'Não cadastrado' : money(p.preco_minimo));
        linha(dl, 'Desconto máximo (R$)', money(p.desconto_maximo_reais));
        linha(dl, 'Desconto máximo (%)', br(p.desconto_maximo_percentual) + '%');
        if ('custo' in p) {
            linha(dl, 'Valor de custo', p.custo ? money(p.custo) : 'Não cadastrado');
            if (p.custo) {
                linha(dl, 'Margem no preço oficial', br(p.margem_oficial) + '%');
                if (p.margem_minimo !== null) linha(dl, 'Margem no preço mínimo', br(p.margem_minimo) + '%');
            }
        }
        ficha.replaceChildren(dl);
        if (p.minimo_custo_nivel === 'abaixo_custo' || p.minimo_custo_nivel === 'perto_custo') {
            ficha.appendChild(el('div', 'cat-status ' + (p.minimo_custo_nivel === 'abaixo_custo' ? 'bloqueado' : 'aviso'),
                p.minimo_custo_nivel === 'abaixo_custo'
                    ? 'Atenção: o preço mínimo deste produto está abaixo do custo.'
                    : 'Atenção: o preço mínimo deste produto está perto do custo. Evite descontos próximos do limite.'));
        }

        if (p.sem_preco_oficial) {
            simBox.style.display = 'none';
            simVazio.style.display = 'block';
            simVazio.textContent = 'Este produto ainda não tem preço oficial. Peça ao gerente ou administrador para defini-lo.';
            addBtn.disabled = true;
            return;
        }

        simVazio.style.display = 'none';
        simBox.style.display = 'block';
        inQtd.value = 1;
        inFinal.value = inPct.value = inReais.value = '';
        addBtn.disabled = true;
        simular();
    }

    function valorDoModo() {
        if (modo === 'percentual') return inPct.value;
        if (modo === 'reais') return inReais.value;
        if (modo === 'preco_final') return inFinal.value;
        return '';
    }

    function simular() {
        if (!atual) return;
        var n = ++seqSim;
        var produtoId = atual.id;
        ultimaSimulacao = null;
        addBtn.disabled = true;
        var body = new URLSearchParams();
        body.set('_token', TOKEN);
        body.set('produto_id', atual.id);
        body.set('quantidade', inQtd.value);
        body.set('modo', (modo !== 'nenhum' && valorDoModo().trim() === '') ? 'nenhum' : modo);
        body.set('valor', valorDoModo());

        fetch(BASE + '/catalogo/simular', {
            method: 'POST', credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'},
            body: body.toString()
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (n !== seqSim) return;
            if (!d.status) { mostrarErro(d.message); return; }
            mostrar(d.simulacao, produtoId);
        }).catch(function () { mostrarErro('Falha na comunicação. Tente novamente.'); });
    }

    function mostrarCusto(s) {
        if (!s || !s.custo_alerta) {
            outCusto.style.display = 'none';
            outCusto.textContent = '';
            return;
        }
        outCusto.className = 'cat-status ' + (s.custo_nivel === 'abaixo_custo' ? 'bloqueado' : 'aviso');
        var texto = s.custo_alerta;
        if (s.margem_percentual !== undefined && s.margem_percentual !== null) {
            texto += ' Margem: ' + br(s.margem_percentual) + '% (' + money(s.lucro_unitario) + ' por unidade).';
        }
        outCusto.textContent = texto;
        outCusto.style.display = 'block';
    }

    function mostrarErro(msg) {
        outTotal.textContent = '-';
        mostrarCusto(null);
        addBtn.disabled = true;
        outStatus.className = 'cat-status aviso';
        outStatus.style.display = 'block';
        outStatus.textContent = msg;
    }

    function mostrar(s, produtoId) {
        ultimaSimulacao = {produtoId: produtoId, resultado: s};
        addBtn.disabled = false;
        if (modo !== 'preco_final') inFinal.value = br(s.preco_final);
        if (modo !== 'percentual') inPct.value = br(s.desconto_percentual);
        if (modo !== 'reais') inReais.value = br(s.desconto_reais);
        outTotal.textContent = money(s.valor_total);

        outStatus.style.display = 'block';
        outStatus.replaceChildren();
        if (s.status === 'autorizado') {
            outStatus.className = 'cat-status ok';
            outStatus.textContent = 'Negociação autorizada: ' + s.mensagem;
        } else if (s.status === 'bloqueado') {
            outStatus.className = 'cat-status bloqueado';
            outStatus.appendChild(el('div', null, 'Negociação bloqueada: ' + s.mensagem));
            outStatus.appendChild(el('div', null, 'Excedeu o limite em ' + money(s.excesso_unitario) + ' por unidade (' + money(s.excesso_total) + ' no total).'));
        } else {
            outStatus.className = 'cat-status aviso';
            outStatus.textContent = s.mensagem;
        }
        mostrarCusto(s);
    }

    function renderCarrinho() {
        cartItems.replaceChildren();
        cartEmpty.style.display = carrinho.length ? 'none' : 'block';
        cartContent.style.display = carrinho.length ? 'block' : 'none';
        cartCount.textContent = carrinho.length
            ? carrinho.length + (carrinho.length === 1 ? ' produto adicionado' : ' produtos adicionados')
            : 'Nenhum produto adicionado';

        var totalCentavos = 0;
        var temPendencia = false;
        var temCusto = false;
        carrinho.forEach(function (item, indice) {
            totalCentavos += Math.round(Number(item.valor_total) * 100);
            if (item.status !== 'autorizado') temPendencia = true;
            if (item.custo_nivel === 'abaixo_custo' || item.custo_nivel === 'perto_custo') temCusto = true;

            var tr = el('tr', 'cat-carrinho-item');
            var produto = el('td');
            produto.appendChild(el('strong', null, item.nome));
            tr.appendChild(produto);
            tr.appendChild(el('td', null, String(item.quantidade)));
            tr.appendChild(el('td', null, money(item.preco_final)));
            tr.appendChild(el('td', null, money(item.valor_total)));

            var status = item.status === 'autorizado' ? 'Autorizado'
                : (item.status === 'bloqueado' ? 'Abaixo do mínimo' : 'Sem preço mínimo');
            var statusCell = el('td', null, status);
            statusCell.title = item.mensagem;
            if (item.custo_nivel === 'abaixo_custo') {
                statusCell.appendChild(document.createElement('br'));
                statusCell.appendChild(el('span', 'badge badge-danger', 'abaixo do custo'));
            } else if (item.custo_nivel === 'perto_custo') {
                statusCell.appendChild(document.createElement('br'));
                statusCell.appendChild(el('span', 'badge badge-warning', 'perto do custo'));
            }
            tr.appendChild(statusCell);

            var acao = el('td');
            acao.style.textAlign = 'right';
            var remover = el('button', 'btn btn-sm btn-danger');
            remover.type = 'button';
            remover.title = 'Remover ' + item.nome + ' da compra';
            remover.setAttribute('aria-label', remover.title);
            remover.appendChild(el('i', 'fas fa-times'));
            remover.addEventListener('click', function () {
                carrinho.splice(indice, 1);
                renderCarrinho();
            });
            acao.appendChild(remover);
            tr.appendChild(acao);
            cartItems.appendChild(tr);
        });

        cartTotal.textContent = money(String(totalCentavos / 100));
        var alertas = [];
        if (temPendencia) alertas.push('A compra contém produto abaixo do preço mínimo ou sem preço mínimo cadastrado. Consulte o gerente ou administrador.');
        if (temCusto) alertas.push('A compra contém produto vendido abaixo ou perto do custo.');
        cartAlert.style.display = alertas.length ? 'block' : 'none';
        cartAlert.textContent = alertas.join(' ');
    }

    addBtn.addEventListener('click', function () {
        if (!atual || !ultimaSimulacao || ultimaSimulacao.produtoId !== atual.id) return;
        var s = ultimaSimulacao.resultado;
        carrinho.push({
            nome: atual.nome,
            quantidade: s.quantidade,
            preco_final: s.preco_final,
            valor_total: s.valor_total,
            status: s.status,
            mensagem: s.mensagem,
            custo_nivel: s.custo_nivel
        });
        renderCarrinho();
    });

    function agendarSimulacao(novoModo) {
        if (novoModo) modo = novoModo;
        ultimaSimulacao = null;
        addBtn.disabled = true;
        seqSim++;
        clearTimeout(tSim);
        tSim = setTimeout(simular, 150);
    }

    busca.addEventListener('input', function () { clearTimeout(tBusca); tBusca = setTimeout(function () { pesquisar(false); }, 200); });
    busca.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(tBusca);
            pesquisar(true);
        }
    });
    inQtd.addEventListener('input', function () { agendarSimulacao(null); });
    inPct.addEventListener('input', function () { agendarSimulacao('percentual'); });
    inReais.addEventListener('input', function () { agendarSimulacao('reais'); });
    inFinal.addEventListener('input', function () { agendarSimulacao('preco_final'); });
    document.getElementById('sim-limpar').addEventListener('click', function () {
        modo = 'nenhum';
        inFinal.value = inPct.value = inReais.value = '';
        agendarSimulacao(null);
    });

    pesquisar(false);
})();
</script>
