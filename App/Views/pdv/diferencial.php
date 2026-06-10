<!--Usando o Html Components-->
<?php

use App\Models\ConfigPdv;
use System\HtmlComponents\Modal\Modal;

?>
<?php $GLOBALS['pdv_diferencial'] = true; ?>
<style type="text/css">
    .imagem-perfil {
        width: 30px;
        height: 30px;
        object-fit: cover;
        object-position: center;
        border-radius: 50%;
    }
    @media only screen and (min-width: 600px) {
        #salvar-venda {
            margin-top: 25px;
        }
    }
    @media only screen and (max-width: 600px) {
        .card-produtos {
            width:50%;
            padding-bottom:10px!important;
        }
        .div-realizar-pagamento {
            background:white!important;
            box-shadow:none;
            padding-right:0;
        }
        .div-card-body-realizar-pagamento {
            background:white;
            border-radius:none!important;
            box-shadow:none!important;
            border:none!important;
        }
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
    .card-produtos {
        margin-top: 10px;
        border-left: 1px solid #dddddd;
        padding: 0;
        float: left;
    }
    .card-produtos img:hover {
        cursor: pointer;
        border: 2px solid #7fe3ca;
        filter: brightness(95%);
    }
    .card-produtos img:active {
        cursor: pointer;
        border: 1px solid #7fe3ca;
        box-shadow: silver 1px 1px 3px;
    }
    .card-produtos img, .icone-produtos {
        width: 80px;
        height: 80px;
        object-fit: cover;
        object-position: center;
        margin: 0 auto;
        display: block;
        border-radius: 50%;
        border: 1px solid gray;
        padding: 3px;
        background: white;
    }
    .icone-produtos {
        padding-top:15px;
        padding-left:8px;
    }
    .icone-produtos:hover {
        cursor: pointer;
        border: 2px solid #7fe3ca;
        filter: brightness(95%);
    }
    .produto-titulo {
        font-size: 11px !important;
        text-align: center;
        display: block;
        margin-top: 3px;
    }
    .produto-valor {
        font-size: 13px !important;
        text-align: center;
        font-weight: bold;
    }
    .div-inter-produtos {
        background: #f4f3ef;
    }
    .img-produto-seleionado {
        width: 30px;
        height: 30px;
        object-fit: cover;
        object-position: center;
        border-radius: 50%;
        border: 1px solid #dee2e6;
    }
    .campo-quantidade {
        border: 1px solid #dee2e6;
        width: 50px;
        text-align: center;
    }
    .div-inter-produtos {
        overflow-y: scroll;
        height: 160px;
        padding-bottom: 10px;
    }
    .div-inter-produtos::-webkit-scrollbar-track {
        background-color: white;
    }
    .div-inter-produtos::-webkit-scrollbar {
        width: 5px;
        background: #252422;
    }
    .div-inter-produtos::-webkit-scrollbar-thumb {
        background: #252422;
    }
    .div-inter-produtos::-webkit-input-placeholder {
        color: #8198ac;
    }
    .div-inter-produtos {
        height: 300px !important;
    }
    #data-compensacao {
        transition: opacity 1s ease-out;
        opacity: 0;
        height: 0;
        overflow: hidden;
    }
    #data-compensacao.visivel {
        opacity: 1;
        height: auto;
    }
    .div-realizar-pagamento {
        background:transparent;
        box-shadow:none;
        padding-right:0;
    }
    .div-card-body-realizar-pagamento {
        background:white;
        border-radius:10px;
        box-shadow:#deddd9 1px 2px 10px;
    }
</style>

<!--Caixa de pesquisa-->
<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
                <div>
                    <h5 class="card-title" style="margin-bottom:6px;">
                        <i class="<?php echo ConfigPdv::iconeTipo(ConfigPdv::TIPO_DIFERENCIAL); ?>"></i>
                        <?php echo ConfigPdv::nomeTipo(ConfigPdv::TIPO_DIFERENCIAL); ?>
                    </h5>
                    <small style="opacity:.75;">
                        <?php echo ConfigPdv::descricaoTipo(ConfigPdv::TIPO_DIFERENCIAL); ?>
                    </small>
                </div>
                <span class="badge badge-primary" style="padding:8px 12px;font-size:12px;">Catalogo e carrinho</span>
            </div>
        </div>
    </div>

    <div class="card col-lg-12 content-div">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Pesquise por nome..."
                    onkeyup="pesquisarProdutoPorNome($(this).val())">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Pesquise por código de barras..."
                    onkeyup="pesquisarProdutoPorCodigoDeBarras($(this).val())"
                    onkeypress="pesquisarProdutoPorCodigoDeBarras($(this).val())">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Carrega os produtos-->
<div class="row">
    <div class="card col-lg-8 content-div">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-box-open"></i> Produtos</h5>
            <div id="carregar-produtos"></div>
        </div>
    </div>

    <div class="card col-lg-4 content-div div-realizar-pagamento">
        <div class="card-body div-card-body-realizar-pagamento">

            <span>Total</span> <br>
            <input type="text" class="form-control" id="b-mostra-valor-total" readonly placeholder="R$ 00,00">

            <hr>

            <div class="form-group">
                <label for="id_meio_pagamento">Meios de pagamento *</label>
                <select class="form-control" name="id_meio_pagamento" id="id_meio_pagamento" onchange="handleAoMudarMeioDePagamento()">
                    <option value="selecione">Selecione</option>
                    <?php foreach ($meiosPagamentos as $pagamento): ?>
                        <option value="<?php echo $pagamento->id; ?>">
                            <?php echo $pagamento->legenda; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="div-valor-recebido" style="display:none">
                <label for="valor_recebido" id="label-valor-pago">Valor pago</label>
                <input type="text" class="form-control campo-moeda" id="valor_recebido" name="valor_recebido" placeholder="R$ 00,00"
                onblur="calcularTroco();">
            </div>

            <div id="div-troco" style="display:none">
                Troco <br>
                <input type="text" class="form-control campo-moeda" id="input_troco" readonly placeholder="R$ 00,00">
            </div>

            <div class="form-group" id="data-compensacao">
                <label for="id_meio_pagamento">Data de compensacao</label>
                <input type="date" class="form-control" id="data_compensacao_boleto" name="data_compensacao_boleto">
            </div>

            <button class="btn btn-sm btn-success btn-block" id="button-confirmar-venda" onclick="saveVendasViaSession('<?php echo TOKEN; ?>')">
                <i class="fas fa-save"></i> Confirmar
            </button>

        </div>
    </div>
</div>

<div class="row">
    <div class="card col-lg-12 content-div">
        <div class="card-body" style="overflow-x:auto!important;">
            <h5 class="card-title">
                <i class="fas fa-cart-arrow-down"></i>
                Itens selecionados
            </h5>
            <table class="table tabela-ajustada tabela-de-produto" style="width:100%;">
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
                <tfoot></tfoot>
            </table>
        </div>
    </div>
</div>

<div id="modal-cupom-fiscal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo-cupom-fiscal" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titulo-cupom-fiscal"><i class="fas fa-file-invoice"></i> Nota fiscal emitida (mock)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="cupom-fiscal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="imprimirCupomFiscalAtual()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>



