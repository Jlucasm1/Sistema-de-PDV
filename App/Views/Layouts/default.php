<?php
use App\Config\ConfigPerfil;
use App\Models\ConfigPdv;
use System\Session\Session;

$idEmpresaSessao = Session::get('idEmpresa');
$configPdv = !empty($idEmpresaSessao) ? (new ConfigPdv())->configPdv($idEmpresaSessao) : (object) [];
$appName   = $_ENV['APPLICATION_NAME'] ?? 'PDV MASTER';
$tabUrl = static fn(string $rota): string => Session::appendTabToUrl(BASEURL . '/' . ltrim($rota, '/'));
$assetVersion = '20260518b';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"/>
    <base href="<?= BASEURL ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"/>
    <title><?= htmlspecialchars($appName) ?></title>
    <link rel="shortcut icon" href="<?= BASEURL ?>/img/favicon.png"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;400;600;700&display=swap" rel="stylesheet"/>

    <!-- Bootstrap & Paper Dashboard -->
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/paper-dashboard.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">

    <!-- CSS locais -->
    <link rel="stylesheet" href="<?= BASEURL ?>/css/select2.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/css/w3cform.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/css/confirm.css">

    <style>
        :root {
            --pdv-primary:      #2196F3;
            --pdv-primary-dark: #1565C0;
            --pdv-primary-light:#E3F2FD;
            --pdv-accent:       #0288D1;
            --pdv-sidebar-bg:   #1565C0;
            --pdv-sidebar-dark: #0D47A1;
            --pdv-text-light:   rgba(255,255,255,0.90);
            --pdv-text-muted:   rgba(255,255,255,0.60);
        }

        /*  Body & fundo  */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #EEF4FB !important;
        }

        /*  Sidebar azul claro  */
        .sidebar {
            background: linear-gradient(180deg, var(--pdv-sidebar-bg) 0%, var(--pdv-sidebar-dark) 100%) !important;
            box-shadow: 4px 0 18px rgba(21,101,192,0.25);
        }
        .sidebar .logo {
            border-bottom: 1px solid rgba(255,255,255,0.15) !important;
            background: rgba(0,0,0,0.12);
        }
        .sidebar .logo .logo-normal {
            color: #ffffff !important;
            font-weight: 700;
            font-size: 17px;
            letter-spacing: 0.5px;
        }
        .sidebar .logo .logo-normal i { color: #90CAF9 !important; }

        /* links do menu */
        .sidebar-wrapper .nav li > a {
            color: var(--pdv-text-muted) !important;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all .2s ease;
        }
        .sidebar-wrapper .nav li > a:hover {
            color: #ffffff !important;
            background: rgba(255,255,255,0.15) !important;
        }
        .sidebar-wrapper .nav li > a.disabled {
            color: #ffe082 !important;
            background: rgba(255, 193, 7, 0.14) !important;
            border-left: 3px solid #ffd54f !important;
            opacity: 1 !important;
            cursor: not-allowed;
        }
        .sidebar-wrapper .nav li > a.disabled i,
        .sidebar-wrapper .nav li > a.disabled p,
        .sidebar-wrapper .nav li > a.disabled small {
            color: #ffe082 !important;
        }
        .sidebar-wrapper .nav li.active > a,
        .sidebar-wrapper .nav li > a.currentRouteFromMenu {
            color: #ffffff !important;
            background: rgba(255,255,255,0.20) !important;
            border-left: 3px solid #90CAF9 !important;
        }

        /*  Navbar superior  */
        .navbar.navbar-transparent {
            background: rgba(255,255,255,0.95) !important;
            border-bottom: 1px solid #BBDEFB;
            box-shadow: 0 2px 10px rgba(33,150,243,0.12);
        }
        .navbar .navbar-brand, .navbar .navbar-brand small { color: #1565C0 !important; }
        .badge-perfil {
            font-size: 11px;
            background: #E3F2FD;
            border-radius: 8px;
            border: 1px solid #90CAF9;
            padding: 3px 8px;
            color: #1565C0;
        }

        /*  Cards  */
        .card {
            border-radius: 12px !important;
            box-shadow: 0 2px 12px rgba(33,150,243,0.10) !important;
            border: 1px solid #BBDEFB !important;
        }
        .card-header {
            background: linear-gradient(90deg, #1976D2, #42A5F5) !important;
            color: #fff !important;
            border-radius: 12px 12px 0 0 !important;
        }
        .card-header h5, .card-header h4, .card-header .card-title {
            color: #fff !important;
        }

        /*  Botoes  */
        .btn-primary, .btn-info {
            background: linear-gradient(135deg, #1976D2, #42A5F5) !important;
            border-color: #1976D2 !important;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-info:hover {
            background: linear-gradient(135deg, #1565C0, #2196F3) !important;
            opacity: 0.95;
        }

        /*  Tabelas  */
        .table thead th {
            background: #E3F2FD !important;
            color: #1565C0 !important;
            border-bottom: 2px solid #90CAF9 !important;
            font-weight: 600;
        }
        .table tbody tr:hover { background: #F3F8FF !important; }
        .tabela-ajustada tr td { padding: 4px 8px !important; font-size: 12px !important; }
        .tabela-ajustada th { font-size: 13px !important; }

        /*  Inputs  */
        .form-control:focus {
            border-color: #42A5F5 !important;
            box-shadow: 0 0 0 3px rgba(33,150,243,0.18) !important;
        }

        /*  Badges/Pills  */
        .badge-primary { background: #1976D2 !important; }

        /*  Misc  */
        .avatar-perfil { border-radius: 50%; width: 38px; height: 38px; object-fit: cover; border: 2px solid #90CAF9; }
        select { height: 40px !important; }
        @media (max-width: 600px) { .hidden-mobile { display: none; } }
        #scroll-wrap { max-height: 70vh; overflow-y: auto; }
        .navbar-brand small { font-size: 11px; }

        /*  Scrollbar estilizada  */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #E3F2FD; }
        ::-webkit-scrollbar-thumb { background: #90CAF9; border-radius: 3px; }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar" data-color="blue" data-active-color="primary">
        <div class="logo">
            <a href="<?= $tabUrl('home') ?>" class="simple-text logo-normal">
                <i class="fas fa-cash-register" style="color:#90CAF9"></i>
                &nbsp;<?= htmlspecialchars($appName) ?>
            </a>
        </div>
        <?php require_once 'menuLeft.php'; ?>
    </div>
    <!-- /Sidebar -->

    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <div class="navbar-toggle">
                        <button type="button" class="navbar-toggler">
                            <span class="navbar-toggler-bar bar1"></span>
                            <span class="navbar-toggler-bar bar2"></span>
                            <span class="navbar-toggler-bar bar3"></span>
                        </button>
                    </div>
                    <a class="navbar-brand" href="<?= $tabUrl('usuario') ?>">
                        <?php $imgPerfil = Session::get('imagem'); ?>
                        <?php if ($imgPerfil): ?>
                            <img class="avatar-perfil" src="<?= BASEURL . '/' . $imgPerfil ?>">
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size:28px;"></i>
                        <?php endif; ?>
                        <small title="<?= e(Session::get('nomeUsuario') ?? '') ?>">
                            <?= e(stringAbreviation(ucfirst(normalizaTexto(Session::get('nomeUsuario') ?? '')), 12, '...')) ?>
                        </small>
                        <small class="badge-perfil">
                            <i class="fas fa-circle" style="color:var(--pdv-success);font-size:7px"></i>
                            <?= e(Session::get('legendaPerfil') ?? '') ?>
                        </small>
                        <?php if (Session::get('nomeCaixaAtual')): ?>
                            <small class="badge-perfil">
                                <i class="fas fa-cash-register"></i>
                                <?= e(Session::get('nomeCaixaAtual')) ?>
                            </small>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="collapse navbar-collapse justify-content-end" id="navigation">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                                <i class="nc-icon nc-settings-gear-65"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?= $tabUrl('usuario') ?>">
                                    <i class="fas fa-users"></i> <?= (int) Session::get('idPerfil') === ConfigPerfil::vendedor() ? 'Meu Perfil' : 'Usuários' ?>
                                </a>
                                <?php if (ConfigPerfil::isSuperAdmin((int) Session::get('idPerfil'))): ?>
                                    <a class="dropdown-item" href="<?= $tabUrl('empresa') ?>">
                                        <i class="fas fa-store"></i> Empresas
                                    </a>
                                <?php endif; ?>
                                <?php if (in_array((int) Session::get('idPerfil'), ConfigPerfil::perfisAdministrativos(), true)): ?>
                                    <a class="dropdown-item" href="<?= $tabUrl('configuracao') ?>">
                                        <i class="fas fa-cogs"></i> Configurações PDV
                                    </a>
                                    <a class="dropdown-item" href="<?= $tabUrl('logs') ?>">
                                        <i class="fas fa-file-alt"></i> Logs de Acesso
                                    </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="<?= $tabUrl('login/logout') ?>">
                                    <i class="fas fa-sign-out-alt"></i> Sair do Sistema
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- /Navbar -->

        <div class="content">
            <?php $this->viewRender(); ?>
        </div>
    </div>
</div>

<!-- Modal de validacao global -->
<div id="modal-validacao" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modal-body-content"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="<?= BASEURL ?>/assets/js/core/jquery.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/assets/js/core/popper.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/assets/js/core/bootstrap.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/assets/js/plugins/perfect-scrollbar.jquery.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/js/helpers.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/js/mask.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/assets/js/paper-dashboard.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/js/select2.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/js/confirm.js?v=<?= $assetVersion ?>"></script>
<script>
$(function () {
    $('.campo-moeda').maskMoney({ prefix: 'R$ ', allowNegative: false, thousands: '.', decimal: ',', affixesStay: false });

    <?php if (Session::get('idCaixaSessao')): ?>
    function pingCaixa() {
        $.get('<?= BASEURL ?>/caixa/ping');
    }
    pingCaixa();
    setInterval(pingCaixa, 60000);
    <?php endif; ?>
});
</script>
<script>
window.PDV_TAB_ID = <?= json_encode(Session::currentTabId(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php if (!empty($GLOBALS['pdv_diferencial'])): ?>
<script src="<?= BASEURL ?>/js/venda/funcoesPdvAvancado.js?v=<?= $assetVersion ?>"></script>
<script>
function pesquisarProdutoPorNome(nome) {
    $("#carregar-produtos").html("<center><h3>Carregando...</h3></center>");
    let base = $('base').attr('href') || '<?= BASEURL ?>';
    let url = base.replace(/\/$/, '') + "/pesquisarProdutoPorNome";
    url += nome ? ("/" + in64(nome)) : "";
    $("#carregar-produtos").load(url);
}

function pesquisarProdutoPorCodigoDeBarras(codigo) {
    $("#carregar-produtos").html("<center><h3>Carregando...</h3></center>");
    let base = $('base').attr('href') || '<?= BASEURL ?>';
    let url = base.replace(/\/$/, '') + "/pesquisarProdutoPorCodigoDeBarra";
    url += codigo ? ("/" + in64(codigo)) : "";
    $("#carregar-produtos").load(url);
}

$(function () {
    if ($("#carregar-produtos").length) {
        pesquisarProdutoPorNome(false);
    }
});
</script>
<?php endif; ?>
</body>
</html>





