<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Completar Perfil</title>
  <link rel="icon" type="image/x-icon" href="../favicon_io/favicon.ico">
  <link rel="stylesheet" href="../css/login.css?v=1">
  <link rel="stylesheet" href="../css/register.css?v=1">
</head>

<body class="login-page">
  <main class="login-wrap">
    <section class="login-card" aria-label="Registo - Passo 2">
      <h1 class="login-title">Completar Perfil</h1>

      <div class="login-layout">
        <aside class="login-side" aria-hidden="true">
          <div class="login-avatar"></div>
          <div class="login-lock"></div>
        </aside>

        <form method="POST" action="register_process_2.php" class="login-form" autocomplete="on">
          <div class="auth-step">Passo 2 de 2</div>

          <div class="field field--number">
            <input type="number" name="idade" placeholder="Idade" required min="0" inputmode="numeric">
          </div>

          <div class="field field--select">
            <select name="ocupacao" required aria-label="Ocupação">
              <option value="" selected disabled>Ocupação</option>
              <option value="Estudante">Estudante</option>
              <option value="Trabalhador">Trabalhador</option>
              <option value="Estudante / Trabalhador">Estudante / Trabalhador</option>
              <option value="Outro">Outro</option>
            </select>
          </div>

          <div class="field field--money">
            <input type="number" name="salario" placeholder="Salário Mensal (€)" required min="0" step="0.01" inputmode="decimal">
          </div>

          <div class="field field--select">
            <select name="estilo_vida" required aria-label="Estilo de Vida">
              <option value="" selected disabled>Estilo de Vida</option>
              <option value="Académico">Académico</option>
              <option value="Trabalhador Ocupado">Trabalhador Ocupado</option>
              <option value="Social Ativo">Social Ativo</option>
              <option value="Minimalista">Minimalista</option>
              <option value="Outro">Outro</option>
            </select>
          </div>

          <button type="submit" class="btn-login">Finalizar Registo</button>

          <p class="login-footer">
            Voltar ao <a href="register_1.php">Passo 1</a>
          </p>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
