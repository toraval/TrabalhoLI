<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Register - Passo 1</title>
    <link rel="stylesheet" href="../css/login_register.css">
</head>
<body>

<div class="register-container">

    <div class="left-side">
        <img src="img/register.png" alt="Imagem de Registo">
    </div>

    <div class="right-side">

        <h1>Criar Conta</h1>

        <form id="registerForm" method="POST" action="register_process_step1.php">

            <label>Nome *</label>
            <input type="text" name="nome" id="nome" required>

            <label>Email *</label>
            <input type="email" name="email" id="email" required>

            <label>Password *</label>
            <input type="password" name="pass" id="pass" required>

            <label>Confirmar Password *</label>
            <input type="password" name="pass2" id="pass2" required>

            <button type="submit">Continuar</button>

        </form>

    </div>
</div>

<script src="register.js"></script>
</body>
</html>
