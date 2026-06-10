<?php
use App\Config\ConfigPerfil;
use System\HtmlComponents\FlashMessage\FlashMessage;
use System\Session\Session;

$perfilAtual = (int) Session::get('idPerfil');
$isVendedor = $perfilAtual === ConfigPerfil::vendedor();
$isSuperAdmin = $perfilAtual === ConfigPerfil::superAdmin();
?>
<style>
    .alerta-caixa-pendente {
        border: 2px solid #f4b400;
        border-radius: 14px;
        background: linear-gradient(135deg, #fff8db 0%, #fff3bf 100%);
        box-shadow: 0 10px 24px rgba(244, 180, 0, 0.16);
        margin-bottom: 18px;
    }
    .alerta-caixa-pendente .card-body {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    .alerta-caixa-pendente h4 {
        margin-bottom: 6px;
        color: #8a5a00;
    }
    .alerta-caixa-pendente p {
        margin-bottom: 0;
        color: #6d4c00;
    }
    .caixa-online-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }
    .caixa-online-card {
        border: 1px solid #d6e6fb;
        border-radius: 12px;
        padding: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #f6fbff 100%);
    }
    .caixa-online-card h6 {
        margin-bottom: 8px;
        color: #1565C0;
    }
    .caixa-kpi {
        font-size: 24px;
        font-weight: 700;
        color: #0D47A1;
    }
    .caixa-status-online {
        color: #138a4b;
        font-weight: 700;
    }
    .caixa-status-offline {
        color: #b66b00;
        font-weight: 700;
    }
    .caixa-home-card .card-body p:last-child {
        margin-bottom: 0;
    }
</style>

<div class="content">
    <?php FlashMessage::show(); ?>

    <?php if ($isVendedor && empty($sessaoCaixaAtual)): ?>
    <div class="card alerta-caixa-pendente">
        <div class="card-body">
            <div>
                <h4><i class="fas fa-exclamation-triangle"></i> Caixa ainda não iniciado</h4>
                <p>Abra um caixa para começar a operar no PDV e registrar suas vendas.</p>
            </div>
            <?php if (!empty($caixas)): ?>
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalAbrirCaixa">
                    <i class="fas fa-lock-open"></i> Abrir Caixa Agora
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isSuperAdmin): ?>
    <div class="row">
        <div class="col-lg-4">
            <div class="card caixa-home-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-cash-register"></i> Novo Caixa</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= BASEURL ?>/caixa/save">
                        <input type="hidden" name="_token" value="<?= TOKEN ?>"/>
                        <div class="form-group">
                            <label for="nome_caixa">Nome do caixa</label>
                            <input type="text" class="form-control" id="nome_caixa" name="nome" placeholder="Ex: Caixa 1" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao_caixa">Descrição</label>
                            <input type="text" class="form-control" id="descricao_caixa" name="descricao" placeholder="Frente da loja, delivery, balcão...">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-plus"></i> Cadastrar Caixa
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card caixa-home-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-broadcast-tower"></i> Caixas Online e Logados</h5>
                </div>
                <div class="card-body">
                    <div class="caixa-online-grid">
                        <?php foreach ($caixasOnline ?? [] as $caixa): ?>
                            <div class="caixa-online-card">
                                <h6><?= htmlspecialchars($caixa->nome) ?></h6>
                                <p>
                                    Status:
                                    <?php if ((int) $caixa->online === 1): ?>
                                        <span class="caixa-status-online">Online</span>
                                    <?php elseif (!empty($caixa->id_caixa_sessao)): ?>
                                        <span class="caixa-status-offline">Aberto sem atividade recente</span>
                                    <?php else: ?>
                                        <span class="text-muted">Livre</span>
                                    <?php endif; ?>
                                </p>
                                <p>Usuário: <strong><?= !empty($caixa->usuario_nome) ? htmlspecialchars($caixa->usuario_nome) : 'Nenhum' ?></strong></p>
                                <p>Abertura: <strong><?= !empty($caixa->aberto_em) ? date('d/m/Y H:i', strtotime($caixa->aberto_em)) : 'Sem sessão' ?></strong></p>
                                <p>Última atividade: <strong><?= !empty($caixa->ultima_atividade_em) ? date('d/m/Y H:i', strtotime($caixa->ultima_atividade_em)) : 'Sem atividade' ?></strong></p>
                                <p>Vendas: <strong><?= (int) ($caixa->quantidade_vendas ?? 0) ?></strong></p>
                                <p>Faturamento: <strong>R$ <?= real($caixa->faturamento ?? 0) ?></strong></p>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($caixasOnline)): ?>
                            <div class="caixa-online-card">
                                <h6>Nenhum caixa cadastrado</h6>
                                <p class="text-muted">Cadastre os caixas para que os vendedores consigam selecioná-los no início do expediente.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isVendedor): ?>
    <div class="row">
        <div class="col-12">
            <div class="card caixa-home-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user-clock"></i> Meu Caixa</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($sessaoCaixaAtual)): ?>
                        <div class="row">
                            <div class="col-md-8">
                                <p>Caixa atual: <strong><?= htmlspecialchars($sessaoCaixaAtual->caixa_nome) ?></strong></p>
                                <p>Aberto em: <strong><?= date('d/m/Y H:i', strtotime($sessaoCaixaAtual->aberto_em)) ?></strong></p>
                                <p>Valor de abertura: <strong>R$ <?= real($sessaoCaixaAtual->valor_abertura ?? 0) ?></strong></p>
                                <p>Vendas no caixa: <strong><?= (int) ($sessaoCaixaAtual->quantidade_vendas ?? 0) ?></strong></p>
                                <p>Faturamento acumulado: <span class="caixa-kpi">R$ <?= real($sessaoCaixaAtual->faturamento ?? 0) ?></span></p>
                            </div>
                            <div class="col-md-4">
                                <form method="post" action="<?= BASEURL ?>/caixa/fechar">
                                    <input type="hidden" name="_token" value="<?= TOKEN ?>"/>
                                    <div class="form-group">
                                        <label for="valor_fechamento">Valor no fechamento</label>
                                        <input type="text" class="form-control campo-moeda" id="valor_fechamento" name="valor_fechamento" placeholder="R$ 0,00">
                                    </div>
                                    <div class="form-group">
                                        <label for="observacao_fechamento">Observação</label>
                                        <textarea class="form-control" id="observacao_fechamento" name="observacao_fechamento" rows="3" placeholder="Resumo do fechamento"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-door-closed"></i> Fechar Caixa
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>Você precisa abrir um caixa para começar a vender.</p>
                        <?php if (!empty($caixas)): ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAbrirCaixa">
                                <i class="fas fa-lock-open"></i> Abrir Caixa
                            </button>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">
                                Nenhum caixa foi cadastrado ainda. Solicite ao SuperAdmin o cadastro dos caixas.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big icon-success text-center">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Faturamento Hoje</p>
                                <p class="card-title">R$ <?= real($faturamentoDia ?? 0) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats"><i class="fas fa-calendar-day"></i> Ontem: R$ <?= real($faturamentoDiaAnterior ?? 0) ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big icon-warning text-center">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Faturamento Mensal</p>
                                <p class="card-title">R$ <?= real($faturamentoMes ?? 0) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats"><i class="fas fa-calendar-alt"></i> Mês anterior: R$ <?= real($faturamentoMesAnterior ?? 0) ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big icon-danger text-center">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Clientes Ativos</p>
                                <p class="card-title"><?= $clientesCadastrados->ativos ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats"><i class="fas fa-user-slash"></i> Inativos: <?= $clientesCadastrados->inativos ?? 0 ?></div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big icon-info text-center">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Produtos Ativos</p>
                                <p class="card-title"><?= $produtosCadastrados->ativos ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats"><i class="fas fa-archive"></i> Inativos: <?= $produtosCadastrados->inativos ?? 0 ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-chart-bar text-success"></i> Vendas por Dia (últimos 30 dias)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartVendasDia" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-chart">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-chart-line text-primary"></i> Faturamento Mensal <?= date('Y') ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="chartMensal" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (!$isVendedor): ?>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-trophy text-warning"></i> Produtos Mais Vendidos (Este Mês)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table tabela-ajustada table-hover">
                        <thead>
                        <tr><th>Produto</th><th>Quantidade</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($produtosMaisVendidosNoMes ?? [] as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p->imagem && file_exists($p->imagem)): ?>
                                        <img src="<?= BASEURL ?>/<?= $p->imagem ?>" style="width:30px;height:30px;object-fit:cover;border-radius:4px;margin-right:6px">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($p->nome) ?>
                                </td>
                                <td><?= $p->quantidade ?></td>
                                <td>R$ <?= real($p->total) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($produtosMaisVendidosNoMes)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Nenhuma venda neste mês</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="<?= $isVendedor ? 'col-md-12' : 'col-md-4' ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-credit-card text-info"></i> Meios de Pagamento</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartMeios" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isVendedor): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-medal text-warning"></i> Vendedores do Mês - <?= date('m/Y') ?></h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table tabela-ajustada table-hover">
                        <thead>
                        <tr><th>Vendedor</th><th>Total</th><th>Dinheiro</th><th>Crédito</th><th>Débito</th><th>Boleto</th><th>Pix</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($totalVendasPorUsuariosNoMes ?? [] as $v): ?>
                            <tr>
                                <td>
                                    <?php if ($v->imagem && file_exists($v->imagem)): ?>
                                        <img src="<?= BASEURL ?>/<?= $v->imagem ?>" style="width:28px;height:28px;object-fit:cover;border-radius:50%">
                                    <?php endif; ?>
                                    <?= htmlspecialchars($v->nome) ?>
                                </td>
                                <td><strong>R$ <?= real($v->valor ?? 0) ?></strong></td>
                                <td>R$ <?= real($v->Dinheiro ?? 0) ?></td>
                                <td>R$ <?= real($v->Credito ?? 0) ?></td>
                                <td>R$ <?= real($v->Debito ?? 0) ?></td>
                                <td>R$ <?= real($v->Boleto ?? 0) ?></td>
                                <td>R$ <?= real($v->Pix ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($totalVendasPorUsuariosNoMes)): ?>
                            <tr><td colspan="7" class="text-center text-muted">Sem vendas neste mês</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($isVendedor && empty($sessaoCaixaAtual)): ?>
<div class="modal fade" id="modalAbrirCaixa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-lock-open"></i> Abrir Caixa</h5>
            </div>
            <div class="modal-body">
                <?php if (!empty($caixas)): ?>
                    <form method="post" action="<?= BASEURL ?>/caixa/abrir" id="form-abrir-caixa">
                        <input type="hidden" name="_token" value="<?= TOKEN ?>"/>
                        <div class="form-group">
                            <label for="id_caixa">Selecione o caixa</label>
                            <select class="form-control" name="id_caixa" id="id_caixa" required>
                                <option value="">Selecione</option>
                                <?php foreach ($caixas as $caixa): ?>
                                    <option value="<?= $caixa->id ?>"><?= htmlspecialchars($caixa->nome) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="valor_abertura">Valor de abertura</label>
                            <input type="text" class="form-control campo-moeda" id="valor_abertura" name="valor_abertura" placeholder="R$ 0,00">
                        </div>
                        <div class="form-group">
                            <label for="observacao_abertura">Observação</label>
                            <textarea class="form-control" id="observacao_abertura" name="observacao_abertura" rows="3" placeholder="Observações da abertura"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-play"></i> Confirmar Abertura
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        Nenhum caixa foi cadastrado ainda. Solicite ao SuperAdmin o cadastro dos caixas antes de iniciar o atendimento.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="<?= BASEURL ?>/assets/js/plugins/chartjs.min.js"></script>
<script>
const vendasDia = <?= json_encode(array_reverse((array)($quantidadeVendasPorDia ?? []))) ?>;
new Chart(document.getElementById('chartVendasDia'), {
    type: 'bar',
    data: {
        labels: vendasDia.map(v => v.data),
        datasets: [{
            label: 'Vendas',
            data: vendasDia.map(v => v.quantidade),
            backgroundColor: 'rgba(26,115,232,0.7)',
            borderRadius: 4
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

const meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
const mensal = <?= json_encode((array)($vendasPorMesNoAno ?? [])) ?>;
new Chart(document.getElementById('chartMensal'), {
    type: 'line',
    data: {
        labels: mensal.map(m => meses[(m.mes - 1)]),
        datasets: [{
            label: 'Faturamento R$',
            data: mensal.map(m => m.total),
            borderColor: '#00c853',
            backgroundColor: 'rgba(0,200,83,0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: { responsive: true }
});

const meios = <?= json_encode((array)($percentualMeiosPagamento->legendas ?? [])) ?>;
const medias = <?= json_encode((array)($percentualMeiosPagamento->medias ?? [])) ?>;
if (meios.length) {
    new Chart(document.getElementById('chartMeios'), {
        type: 'doughnut',
        data: {
            labels: meios,
            datasets: [{ data: medias, backgroundColor: ['#1a73e8','#00c853','#e94560','#f4b400','#7c4dff'] }]
        },
        options: { responsive: true }
    });
}

<?php if ($isVendedor && empty($sessaoCaixaAtual) && !empty($caixas)): ?>
$(function () {
    $('#modalAbrirCaixa').modal({ backdrop: 'static', keyboard: false });
});
<?php endif; ?>
</script>
