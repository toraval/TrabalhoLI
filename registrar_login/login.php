<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login_register.css">
</head>
<body>

<div class="register-container">

    <div class="left-side">
        <img src="../img/Imagem_RegistrarLogin.jpg" alt="Imagem Login">
    </div>

    <div class="right-side">

        <h1>Iniciar Sessão</h1>

        <form method="POST" action="login_process.php">

            <label>Nome *</label>
            <input type="text" name="nome" required>

            <label>Email *</label>
            <input type="email" name="email" required>

            <label>Password *</label>
            <input type="password" name="password" required>

            <button type="submit">Entrar</button>

        </form>

        <p style="margin-top: 15px; font-size:14px;">
            Ainda não tens conta? <a href="register_1.php" style="color:#6c63ff; font-weight:bold;">Criar Conta</a>
        </p>

    </div>

</div>

</body>
</html>
