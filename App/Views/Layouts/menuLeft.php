<?php
use App\Config\ConfigPerfil;
use System\Session\Session;

$perfilAtual = (int) Session::get('idPerfil');
$isVendedor = $perfilAtual === ConfigPerfil::vendedor();
$caixaAberto = (bool) Session::get('idCaixaSessao');
$pdvBloqueadoParaVendedor = $isVendedor && !$caixaAberto;
$tabUrl = static fn(string $rota): string => Session::appendTabToUrl(BASEURL . '/' . ltrim($rota, '/'));
?>
<div class="sidebar-wrapper">
    <ul class="nav">

        <li>
            <a href="<?= $tabUrl('home') ?>" class="<?php currentRouteFromMenu('home', 'inicioBorder') ?>">
                <i class="fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li>
            <?php if ((int)($configPdv->id_tipo_pdv ?? 1) === 1): ?>
                <a href="<?= $pdvBloqueadoParaVendedor ? '#' : $tabUrl('pdvPadrao') ?>"
                   class="<?php currentRouteFromMenu('pdvPadrao', 'pdvBorder') ?> <?= $pdvBloqueadoParaVendedor ? 'disabled' : '' ?>"
                   <?= $pdvBloqueadoParaVendedor ? 'onclick="return false;"' : '' ?>
                   title="<?= $pdvBloqueadoParaVendedor ? 'Abra um caixa para acessar o PDV.' : '' ?>">
                    <i class="fas fa-cash-register"></i>
                    <p>
                        PDV <small style="float:right;opacity:.5">Padrão</small>
                        <?php if ($pdvBloqueadoParaVendedor): ?>
                            <small style="display:block;float:none;opacity:.75;margin-top:4px;">Bloqueado até abrir caixa</small>
                        <?php endif; ?>
                    </p>
                </a>
            <?php else: ?>
                <a href="<?= $pdvBloqueadoParaVendedor ? '#' : $tabUrl('pdvDiferencial') ?>"
                   class="<?php currentRouteFromMenu('pdvDiferencial', 'pdvBorder') ?> <?= $pdvBloqueadoParaVendedor ? 'disabled' : '' ?>"
                   <?= $pdvBloqueadoParaVendedor ? 'onclick="return false;"' : '' ?>
                   title="<?= $pdvBloqueadoParaVendedor ? 'Abra um caixa para acessar o PDV.' : '' ?>">
                    <i class="fas fa-cash-register"></i>
                    <p>
                        PDV <small style="float:right;opacity:.5">Diferencial</small>
                        <?php if ($pdvBloqueadoParaVendedor): ?>
                            <small style="display:block;float:none;opacity:.75;margin-top:4px;">Bloqueado até abrir caixa</small>
                        <?php endif; ?>
                    </p>
                </a>
            <?php endif; ?>
        </li>

        <?php if (in_array((int) Session::get('idPerfil'), ConfigPerfil::perfisComAcessoGerencial(), true)): ?>
        <li>
            <a href="<?= $tabUrl('produto') ?>" class="<?php currentRouteFromMenu('produto', 'produtoBorder') ?>">
                <i class="fas fa-box-open"></i>
                <p>Produtos</p>
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?= $tabUrl('cliente') ?>" class="<?php currentRouteFromMenu('cliente', 'clienteBorder') ?>">
                <i class="fas fa-user-tie"></i>
                <p>Clientes</p>
            </a>
        </li>

        <?php if (in_array((int) Session::get('idPerfil'), ConfigPerfil::perfisComAcessoGerencial(), true)): ?>
        <li>
            <a href="<?= $tabUrl('relatorio/vendasPorPeriodo') ?>" class="<?php currentRouteFromMenu('relatorio', 'relatorioBorder') ?>">
                <i class="fas fa-chart-bar"></i>
                <p>Relatórios</p>
            </a>
        </li>
        <?php endif; ?>

        <?php if (in_array((int) Session::get('idPerfil'), ConfigPerfil::perfisComAcessoGerencial(), true)): ?>
        <li>
            <a href="<?= $tabUrl('fluxoDeCaixa/index') ?>" class="<?php currentRouteFromMenu('fluxoDeCaixa', 'fluxoBorder') ?>">
                <i class="fas fa-money-bill-wave"></i>
                <p>Caixa</p>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>
