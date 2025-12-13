<?php
session_start();

$erro = $_GET['erro'] ?? null;
$mensagemErro = null;

if ($erro === "UtilizadorNaoExiste") {
  $mensagemErro = "Não existe nenhum utilizador com esse email.";
} elseif ($erro === "PasswordErrada") {
  $mensagemErro = "Palavra-passe incorreta. Tenta novamente.";
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>

  <!-- Mantém o teu caminho de CSS (podes trocar o nome do ficheiro se quiseres) -->
  <link rel="stylesheet" href="../css/login.css">
</head>

<body class="login-page">
  <main class="login-wrap">
    <section class="login-card" aria-label="Área de Login">

      <h1 class="login-title">Login</h1>

      <?php if (!empty($mensagemErro)): ?>
        <div class="login-alert" role="alert">
          <?php echo htmlspecialchars($mensagemErro); ?>
        </div>
      <?php endif; ?>

      <div class="login-layout">
        <aside class="login-side" aria-hidden="true">
          <div class="login-avatar"></div>
          <div class="login-lock"></div>
        </aside>

        <form method="POST" action="login_process.php" class="login-form" autocomplete="on">
          
         <label class="sr-only" for="email">Email</label>
          <div class="field field--mail">
            <input id="email" type="email" name="email" placeholder="Email" required autocomplete="email">
          </div> 

        <label class="sr-only" for="password">Password</label>
          <div class="field field--pass">
            <input id="password" type="password" name="password" placeholder="Password" required autocomplete="current-password">
            <button type="button" class="field-action" aria-label="Mostrar/ocultar password" onclick="togglePassword()"></button>
          </div>

          
          <button type="submit" class="btn-login">Entrar</button>

          <p class="login-footer">
            Ainda não tens conta? <a href="register_1.php">Criar Conta</a>
          </p>
        </form>
      </div>
    </section>
  </main>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
    }
  </script>
</body>
</html>
