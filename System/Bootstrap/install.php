<?php
// PDV MASTER — Redirecionamento de instalação
// Quando .env não existe, mostra página de boas-vindas
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PDV MASTER — Configuração</title>
<style>
  body { font-family: sans-serif; background: #1a1a2e; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
  .box { background: white; border-radius: 12px; padding: 40px; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.5); text-align: center; }
  h2 { color: #0f3460; }
  p { color: #555; }
  code { background: #f4f4f4; padding: 4px 8px; border-radius: 4px; font-size: 13px; }
  .step { background: #f8f9ff; border-radius: 8px; padding: 16px; margin: 12px 0; text-align: left; border-left: 3px solid #1a73e8; }
  .step p { margin: 4px 0; font-size: 14px; }
</style>
</head>
<body>
<div class="box">
  <div style="font-size:50px;">🚀</div>
  <h2>PDV MASTER</h2>
  <p>O arquivo <code>.env</code> não foi encontrado.<br>
  Siga os passos abaixo para configurar:</p>

  <div class="step">
    <p><strong>1.</strong> Copie o arquivo de exemplo:</p>
    <p><code>cp .env.example .env</code></p>
  </div>
  <div class="step">
    <p><strong>2.</strong> Suba os containers Docker:</p>
    <p><code>docker-compose up -d</code></p>
  </div>
  <div class="step">
    <p><strong>3.</strong> Instale as dependências:</p>
    <p><code>docker exec pdv_master_app composer install</code></p>
  </div>
  <div class="step">
    <p><strong>4.</strong> Acesse o sistema em:</p>
    <p><code>http://localhost:8080</code></p>
    <p>Login: <code>admin@pdvmaster.com.br</code> | Senha: <code>admin123</code></p>
  </div>
</div>
</body>
</html>
