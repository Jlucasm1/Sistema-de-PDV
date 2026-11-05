<?php $rota = BASEURL . '/produto/modalFormulario'; ?>

<style>
    .badge-estoque-ok   { background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:600; }
    .badge-estoque-low  { background:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:600; }
    .badge-estoque-zero { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:600; }
    .badge-sim  { background:#d4edda; color:#155724; border-radius:12px; padding:2px 10px; font-size:11px; }
    .badge-nao  { background:#e2e3e5; color:#383d41; border-radius:12px; padding:2px 10px; font-size:11px; }
    .imagem-produto { width:38px; height:38px; object-fit:cover; border-radius:50%; border:1px solid #dee2e6; cursor:pointer; }
    .imagem-produto:hover { border-color:#009966; }
</style>

<?php if (count($produtos) > 0): ?>
    <table class="table tabela-ajustada table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th style="width:50px"></th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Mínimo</th>
                <th>Em Vendas</th>
                <th>Estoque</th>
                <th style="text-align:right;padding-right:8px">
                    <button onclick="modalFormularioProdutos('<?php echo $rota; ?>', false);"
                            class="btn btn-sm btn-success" title="Novo Produto">
                        <i class="fas fa-plus"></i> Novo
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td>
                        <?php if (!is_null($produto->imagem) && $produto->imagem != ''): ?>
                            <?php $imagem = BASEURL . '/' . $produto->imagem; ?>
                            <img src="<?php echo $imagem; ?>" class="imagem-produto"
                                 title="<?php echo e($produto->nome); ?>"
                                 onclick="modalImagemDoProduto('<?php echo $imagem; ?>', <?php echo json_encode(normalizaTexto($produto->nome), JSON_UNESCAPED_UNICODE); ?>)">
                        <?php else: ?>
                            <i class="fas fa-box-open" style="font-size:28px;color:#aaa"></i>
                        <?php endif; ?>
                    </td>

                    <td>
                        <strong><?php echo e($produto->nome); ?></strong>
                        <?php if (!empty($produto->descricao)): ?>
                            <br><small style="color:#999"><?php echo strip_tags(substr($produto->descricao, 0, 60)); ?>...</small>
                        <?php endif; ?>
                    </td>

                    <td><strong>R$ <?php echo real($produto->preco); ?></strong></td>
                    <td><?php echo ((float)($produto->preco_minimo ?? 0)) > 0 ? 'R$ '.real($produto->preco_minimo) : '-'; ?></td>

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
                                <span class="badge-estoque-zero"><i class="fas fa-exclamation-triangle"></i> Esgotado</span>
                            <?php elseif ($produto->quantidade <= 5): ?>
                                <span class="badge-estoque-low"><i class="fas fa-exclamation-circle"></i> <?php echo $produto->quantidade; ?> un</span>
                            <?php else: ?>
                                <span class="badge-estoque-ok"><i class="fas fa-check-circle"></i> <?php echo $produto->quantidade; ?> un</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#aaa;font-size:12px">— sem controle</span>
                        <?php endif; ?>
                    </td>

                    <td style="text-align:right">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cogs"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <button class="dropdown-item"
                                        onclick="modalFormularioProdutos('<?php echo $rota; ?>', '<?php echo $produto->id; ?>')">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                   onclick="return excluirProduto('<?php echo $produto->id; ?>')">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot></tfoot>
    </table>

<?php else: ?>
    <div class="text-center" style="padding:40px 0">
        <i class="fas fa-box-open" style="font-size:50px;opacity:0.3"></i>
        <br><br>
        <h6 style="opacity:0.6">Nenhum produto cadastrado ainda!</h6>
        <br>
        <button onclick="modalFormularioProdutos('<?php echo $rota; ?>', false);"
                class="btn btn-success">
            <i class="fas fa-plus"></i> Cadastrar Primeiro Produto
        </button>
    </div>
<?php endif; ?>
