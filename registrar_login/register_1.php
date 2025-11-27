<?php
session_start();
?>

<?php if (isset($_GET['erro'])): ?>
    <div class="alerta-erro">
        <?php 
            if ($_GET['erro'] == "email_existente") 
                echo "⚠ O email inserido já está registado!";

            if ($_GET['erro'] == "nome_existente") 
                echo "⚠ O nome de utilizador já está em uso!";
        ?>
    </div>
<?php endif; ?>

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
        <img src="../img/Imagem_RegistrarLogin.jpg" alt="Imagem de Registro">
    </div>

    <div class="right-side">

        <h1>Criar Conta</h1>

        <form id="registerForm" method="POST" action="register_process_1.php">

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

<script src="../js/register.js"></script>
</body>
</html>
