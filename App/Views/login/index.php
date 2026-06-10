<?php
use System\Session\Session;
$error = Session::getFlash('error');
?>
<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASEURL ?>/login/logar">
    <input type="hidden" name="tab" value="<?= htmlspecialchars(Session::currentTabId()) ?>">
    <div class="form-group">
        <label for="email"><i class="fas fa-envelope"></i> E-mail</label>
        <input type="email" class="form-control" id="email" name="email"
               placeholder="seu@email.com" required autofocus>
    </div>
    <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Senha</label>
        <input type="password" class="form-control" id="password" name="password"
               placeholder="********" required>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember" style="font-size:13px;">Lembrar-me</label>
    </div>
    <small style="display:block;margin-bottom:14px;color:#1565C0;font-size:12px;">
        Vendedores escolhem e abrem o caixa após entrar no sistema.
    </small>
    <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i> Entrar no Sistema
    </button>
</form>
