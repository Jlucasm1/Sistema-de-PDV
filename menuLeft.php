<?php
use App\Config\ConfigPerfil;
use System\Session\Session;
?>
<div class="sidebar-wrapper">
    <ul class="nav">

        <li>
            <a href="<?= BASEURL ?>/home" class="<?php currentRouteFromMenu('home', 'inicioBorder') ?>">
                <i class="fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <!-- PDV -->
        <li>
            <?php if ((int)($configPdv->id_tipo_pdv ?? 1) === 1): ?>
                <a href="<?= BASEURL ?>/pdvPadrao" class="<?php currentRouteFromMenu('pdvPadrao', 'pdvBorder') ?>">
                    <i class="fas fa-cash-register"></i>
                    <p>PDV <small style="float:right;opacity:.5">Padrão</small></p>
                </a>
            <?php else: ?>
                <a href="<?= BASEURL ?>/pdvDiferencial" class="<?php currentRouteFromMenu('pdvDiferencial', 'pdvBorder') ?>">
                    <i class="fas fa-cash-register"></i>
                    <p>PDV <small style="float:right;opacity:.5">Diferencial</small></p>
                </a>
            <?php endif; ?>
        </li>

        <!-- Produtos - apenas não-vendedor -->
        <?php if ((int)Session::get('idPerfil') !== ConfigPerfil::vendedor()): ?>
        <li>
            <a href="<?= BASEURL ?>/produto" class="<?php currentRouteFromMenu('produto', 'produtoBorder') ?>">
                <i class="fas fa-box-open"></i>
                <p>Produtos</p>
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="<?= BASEURL ?>/cliente" class="<?php currentRouteFromMenu('cliente', 'clienteBorder') ?>">
                <i class="fas fa-user-tie"></i>
                <p>Clientes</p>
            </a>
        </li>

        <!-- Relatórios - apenas não-vendedor -->
        <?php if ((int)Session::get('idPerfil') !== ConfigPerfil::vendedor()): ?>
        <li>
            <a href="<?= BASEURL ?>/relatorio/vendasPorPeriodo" class="<?php currentRouteFromMenu('relatorio', 'relatorioBorder') ?>">
                <i class="fas fa-chart-bar"></i>
                <p>Relatórios</p>
            </a>
        </li>
        <?php endif; ?>

        <!-- Fluxo de Caixa -->
        <li>
            <a href="<?= BASEURL ?>/fluxoDeCaixa/index" class="<?php currentRouteFromMenu('fluxoDeCaixa', 'fluxoBorder') ?>">
                <i class="fas fa-money-bill-wave"></i>
                <p>Caixa</p>
            </a>
        </li>

        <!-- Pedidos -->
        <li>
            <a href="<?= BASEURL ?>/pedido" class="<?php currentRouteFromMenu('pedido', 'pedidoBorder') ?>">
                <i class="fas fa-shopping-basket"></i>
                <p>Pedidos</p>
            </a>
        </li>

    </ul>
</div>
