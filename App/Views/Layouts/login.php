<?php
$appName = $_ENV['APPLICATION_NAME'] ?? 'PDV MASTER';
$assetVersion = '20261104b';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName) ?> — Login</title>
    <base href="<?= BASEURL ?>">
    <link rel="shortcut icon" href="<?= BASEURL ?>/img/favicon.png"/>
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/bootstrap.min.css?v=<?= $assetVersion ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #1565C0 0%, #1976D2 50%, #42A5F5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Bolhas decorativas */
        body::before {
            content: '';
            position: fixed;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
            top: -80px; right: -80px;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -60px; left: -60px;
            pointer-events: none;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(13,71,161,0.35);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1565C0, #42A5F5);
            padding: 36px 24px;
            text-align: center;
        }
        .login-header .icon-wrap {
            width: 68px; height: 68px;
            background: rgba(255,255,255,0.20);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            border: 2px solid rgba(255,255,255,0.35);
        }
        .login-header h2 {
            color: #ffffff;
            font-weight: 700;
            font-size: 24px;
            margin: 0 0 4px;
            letter-spacing: 1px;
        }
        .login-header p { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }
        .login-body { padding: 32px 28px; background: #fff; }
        .form-group label { font-weight: 600; font-size: 13px; color: #1565C0; }
        .form-control {
            border-radius: 8px;
            border: 1.5px solid #BBDEFB;
            height: 46px;
            font-size: 14px;
            transition: border .2s, box-shadow .2s;
            background: #F8FBFF;
        }
        .form-control:focus {
            border-color: #42A5F5;
            box-shadow: 0 0 0 3px rgba(33,150,243,0.18);
            background: #fff;
        }
        .btn-login {
            background: linear-gradient(135deg, #1565C0, #42A5F5);
            color: #fff;
            border: none;
            border-radius: 10px;
            width: 100%;
            height: 48px;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.5px;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 4px 15px rgba(21,101,192,0.35);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(21,101,192,0.45);
            color: #fff;
        }
        .alert-danger { border-radius: 8px; font-size: 13px; border-left: 4px solid #f44336; }
        .login-footer {
            text-align: center;
            padding: 14px;
            background: #E3F2FD;
            font-size: 12px;
            color: #1565C0;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="icon-wrap">
                <i class="fas fa-cash-register" style="font-size:32px;color:#ffffff;"></i>
            </div>
            <h2><?= htmlspecialchars($appName) ?></h2>
            <p>Sistema de Ponto de Venda</p>
        </div>
        <div class="login-body">
            <?php $this->viewRender(); ?>
        </div>
        <div class="login-footer">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($appName) ?> — Todos os direitos reservados
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
<script src="<?= BASEURL ?>/assets/js/core/jquery.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/assets/js/core/bootstrap.min.js?v=<?= $assetVersion ?>"></script>
<script src="<?= BASEURL ?>/js/helpers.js?v=<?= $assetVersion ?>"></script>
</body>
</html>
