<style type="text/css">
    small {

    #small-mensagem {
        font-size: 8px !important;
        opacity: 0.60;
    }

    }
</style>

<form method="post" id="form-cliente-endereco"
      action="<?php echo isset($clienteEndereco->id) ? BASEURL . '/clienteEndereco/update' : BASEURL . '/clienteEndereco/save'; ?>"
      enctype='multipart/form-data'>
    <div class="row">

        <input type="hidden" name="_token" value="<?php echo TOKEN; ?>"/>

        <?php if (isset($clienteEndereco->id)): ?>
            <input type="hidden" name="id" value="<?php echo $clienteEndereco->id; ?>">
        <?php endif; ?>

        <input type="hidden" name="id_cliente" value="<?php echo $idCliente; ?>">

        <div class="col-md-4">
            <div class="form-group">
                <label for="cep">CEP*</label>
                <input type="text" class="form-control" name="cep" id="cep" placeholder="Digite o CEP!"
                       value="<?php echo isset($clienteEndereco->id) ? $clienteEndereco->cep : '' ?>"
                       onchange="return buscarEnderecoViaCep(this)">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="endereco">Endereço *</label>
                <input type="text" class="form-control" name="endereco" id="endereco" placeholder="Digite o endereço!"
                       value="<?php echo isset($clienteEndereco->id) ? $clienteEndereco->endereco : '' ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="bairro">Bairro *</label>
                <input type="text" class="form-control" name="bairro" id="bairro" placeholder="Digite o bairro!"
                       value="<?php echo isset($clienteEndereco->id) ? $clienteEndereco->bairro : '' ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="numero">Número *</label>
                <input type="number" class="form-control" name="numero" id="numero" placeholder="Exemplo: 14"
                       min="1" step="1" inputmode="numeric"
                       value="<?php echo isset($clienteEndereco->id) ? $clienteEndereco->numero : '' ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="estado">Estado</label>
                <input type="text" class="form-control" name="estado" id="estado" placeholder="Estado!"
                       value="<?php echo isset($clienteEndereco->id) ? $clienteEndereco->estado : '' ?>">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" class="form-control" name="cidade" id="cidade" placeholder="Cidade!"
                       value="<?php echo isset($clienteEndereco->id) ? $clienteEndereco->cidade : '' ?>">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label for="complemento">Complemento</label>
                <textarea class="form-control" name="complemento" id="complemento"
                          placeholder="Complemento..."
                ><?php echo isset($clienteEndereco->id) ? $clienteEndereco->complemento : '' ?></textarea>
            </div>
        </div>

    </div><!--end row-->

    <button type="submit" class="btn btn-success btn-sm" style="float:right"
            onclick="return salvarEndereco()">
        <i class="fas fa-save"></i> Salvar
    </button>

</form>

<script src="<?php echo BASEURL; ?>/js/maskedInput.js"></script>
<script>
    anulaDuploClick($('#form-cliente-endereco'));

    function salvarEndereco() {
        if ($('#cep').val() == '') {
            modalValidacao('Validação', 'Campo (CEP) deve ser preenchido!');
            return false;

        } else if ($('#endereco').val() == '') {
            modalValidacao('Validação', 'Campo (Endereço) deve ser preenchido!');
            return false;

        } else if ($('#bairro').val() == '') {
            modalValidacao('Validação', 'Campo (Bairro) deve ser preenchido!');
            return false;

        } else if ($('#numero').val() == '') {
            modalValidacao('Validação', 'Campo (Número) deve ser preenchido!');
            return false;

        } else if (!/^\d+$/.test($('#numero').val())) {
            modalValidacao('Validação', 'Campo (Número) deve conter apenas dígitos.');
            return false;
        }

        return true;
    }

    jQuery(function ($) {
        jQuery("#cep").mask("99.999-999");
    });

    function buscarEnderecoViaCep(cep) {
        modalValidacao('Validação', 'Buscando CEP...');

        var rota = getDomain() + "/clienteEndereco/buscarEnderecoViaCep/" + in64(cep.value);
        $.get(rota, function (data) {
            var dados = JSON.parse(data);

            if (dados.status == true) {
                modalValidacaoClose();
                $("#endereco").val(dados.conteudo.logradouro);
                $("#bairro").val(dados.conteudo.bairro);
                $("#cidade").val(dados.conteudo.localidade);
                $("#estado").val(dados.conteudo.uf);
            } else {
                modalValidacao('Validação', dados.mensagem + '<br><small id="small-mensagem">Preencha o endereço manualmente!</small>');
            }
        });
    }

    $('#form-cliente-endereco').on('submit', function (event) {
        if (!$('#modalEnderecoPedido').length) {
            return true;
        }

        event.preventDefault();

        if (!salvarEndereco()) {
            return false;
        }

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (resultado) {
            var retorno = null;
            try { retorno = JSON.parse(resultado); } catch (e) {}

            if (retorno && retorno.status) {
                modalValidacao('Sucesso', 'Endereço salvo com sucesso!');
                if (typeof enderecoDoPedidoSalvo === 'function') {
                    enderecoDoPedidoSalvo($('#form-cliente-endereco input[name="id_cliente"]').val());
                }
                setTimeout(modalValidacaoClose, 600);
            } else {
                modalValidacao('Ops', (retorno && retorno.message) ? retorno.message : 'Não foi possível salvar o endereço.');
            }
        }).fail(function (xhr) {
            modalValidacao('Ops', 'Falha ao salvar o endereço. HTTP ' + xhr.status);
        });

        return false;
    });
</script>
