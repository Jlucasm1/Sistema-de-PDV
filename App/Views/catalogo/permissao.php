<?php
use System\HtmlComponents\FlashMessage\FlashMessage;
?>
<div class="row">
    <div class="card col-lg-8 content-div">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-user-lock"></i> Permissão: gerenciar_catalogo_produtos</h5>
            <p class="text-muted">Apenas um usuário por empresa pode ter esta permissão. Ao conceder a outro usuário, o anterior perde o acesso.</p>
        </div>
        <?php FlashMessage::show(); ?>
        <div style="padding:0 20px 20px">
            <p>
                Titular atual:
                <?php if ($titular): ?>
                    <strong><?= e($titular->nome) ?></strong> (<?= e($titular->email) ?>)
                <?php else: ?>
                    <em>nenhum</em>
                <?php endif; ?>
            </p>
            <div class="form-group">
                <label>Conceder a</label>
                <select id="perm-usuario" class="form-control">
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= (int) $u->id ?>" <?= $titular && (int) $titular->id_usuario === (int) $u->id ? 'selected' : '' ?>>
                            <?= e($u->nome) ?> (<?= e($u->email) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-success" id="perm-conceder"><i class="fas fa-check"></i> Conceder</button>
            <?php if ($titular): ?>
                <button class="btn btn-danger" id="perm-revogar"><i class="fas fa-ban"></i> Revogar</button>
            <?php endif; ?>
            <a href="<?= BASEURL ?>/catalogo/gerenciar" class="btn btn-default">Voltar</a>
        </div>
    </div>
</div>
<script>
(function () {
    var BASE = "<?= BASEURL ?>", TOKEN = "<?= TOKEN ?>";
    function enviar(rota, extra) {
        var body = new URLSearchParams(Object.assign({_token: TOKEN}, extra || {}));
        fetch(BASE + rota, {method: 'POST', credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()})
            .then(function (r) { return r.json(); })
            .then(function (d) { alert(d.message || ''); if (d.status) location.reload(); });
    }
    document.getElementById('perm-conceder').addEventListener('click', function () {
        enviar('/catalogo/conceder', {id_usuario: document.getElementById('perm-usuario').value});
    });
    var rev = document.getElementById('perm-revogar');
    if (rev) rev.addEventListener('click', function () { if (confirm('Revogar a permissão?')) enviar('/catalogo/revogar'); });
})();
</script>
