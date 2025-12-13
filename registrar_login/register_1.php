<?php
session_start();

$erro = $_GET['erro'] ?? null;
$mensagemErro = null;

if ($erro === "email_existente") {
  $mensagemErro = "Este email já está registado.";
} elseif ($erro === "nome_existente") {
  $mensagemErro = "Este nome já está registado.";
} elseif ($erro === "password_mismatch") {
  $mensagemErro = "As passwords não coincidem.";
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar Conta</title>

  <link rel="stylesheet" href="../css/login.css">
  <link rel="stylesheet" href="../css/register.css">
</head>

<body class="login-page">
  <main class="login-wrap">
    <section class="login-card" aria-label="Registo - Passo 1">

      <h1 class="login-title">Criar Conta</h1>

      <!-- Alerta do PHP (ex: email/nome existente) -->
      <?php if (!empty($mensagemErro)): ?>
        <div class="login-alert" role="alert">
          <?php echo htmlspecialchars($mensagemErro); ?>
        </div>
      <?php endif; ?>

      <!-- Alerta do JS (password mismatch em tempo real) -->
      <div id="clientAlert" class="login-alert login-alert--client" role="alert" style="display:none;">
        As passwords não coincidem.
      </div>

      <div class="login-layout">
        <aside class="login-side" aria-hidden="true">
          <div class="login-avatar"></div>
          <div class="login-lock"></div>
        </aside>

        <form id="registerForm" method="POST" action="register_process_1.php" class="login-form" autocomplete="on">
          <div class="auth-step">Passo 1 de 2</div>

          <label class="sr-only" for="nome">Nome</label>
          <div class="field field--user">
            <input id="nome" type="text" name="nome" placeholder="Nome" required autocomplete="name">
          </div>

          <label class="sr-only" for="email">Email</label>
          <div class="field field--mail">
            <input id="email" type="email" name="email" placeholder="Email" required autocomplete="email">
          </div>

          <label class="sr-only" for="password">Password</label>
          <div class="field field--pass">
            <input id="password" type="password" name="password" placeholder="Password" required autocomplete="new-password">
            <button type="button" class="field-action" aria-label="Mostrar/ocultar password" onclick="togglePassword('password')"></button>
          </div>

          <label class="sr-only" for="password_confirm">Confirmar password</label>
          <div class="field field--pass field--pass-confirm">
            <input id="password_confirm" type="password" name="password_confirm" placeholder="Confirmar password" required autocomplete="new-password">
            <button type="button" class="field-action" aria-label="Mostrar/ocultar confirmação" onclick="togglePassword('password_confirm')"></button>
          </div>

          <!-- Mantém o name="tipo_util" para o PHP continuar igual -->
          <div class="field field--select">
            <select name="tipo_util" required aria-label="Tipo de utilizador">
              <option value="" selected disabled>Tipo de utilizador</option>
              <option value="0">Utilizador</option>
              <option value="1">Administrador</option>
            </select>
          </div>

          <button id="btnSubmit" type="submit" class="btn-login">Continuar</button>

          <p class="login-footer">
            Já tens conta? <a href="login.php">Entrar</a>
          </p>
        </form>
      </div>
    </section>
  </main>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = (input.type === 'password') ? 'text' : 'password';
    }

    const form = document.getElementById('registerForm');
    const pass = document.getElementById('password');
    const pass2 = document.getElementById('password_confirm');
    const alertBox = document.getElementById('clientAlert');

    function validateMatch() {
      const mismatch = pass.value && pass2.value && (pass.value !== pass2.value);
      alertBox.style.display = mismatch ? 'block' : 'none';
      return !mismatch;
    }

    pass.addEventListener('input', validateMatch);
    pass2.addEventListener('input', validateMatch);

    form.addEventListener('submit', function(e) {
      if (!validateMatch()) e.preventDefault();
    });
  </script>
</body>
</html>
