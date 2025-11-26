<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: register_1.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Register - Passo 2</title>
    <link rel="stylesheet" href="../css/login_register.css">
</head>
<body>

<div class="register-container">

    <div class="left-side">
        <img src="../img/Imagem_RegistrarLogin.jpg" alt="Imagem Registro">
    </div>

    <div class="right-side">

        <h1>Completar Perfil</h1>

        <form id="step2Form" method="POST" action="register_process_2.php">

            <label>Idade *</label>
            <input type="number" name="idade" required min="10" max="100">

            <label>Ocupação *</label>
            <select name="ocupacao" required>
                <option value="">Selecionar...</option>
                <option value="Estudante">Estudante</option>
                <option value="Trabalhador">Trabalhador</option>
                <option value="Ambos">Estudante / Trabalhador</option>
                <option value="Outro">Outro</option>
            </select>

            <label>Salário Mensal (€) *</label>
            <input type="number" name="salario" required min="0" step="0.01">

            <label>Estilo de Vida *</label>
            <select name="estilo_vida" required>
                <option value="">Selecionar...</option>
                <option value="Académico">Académico</option>
                <option value="Trabalhador Ocupado">Trabalhador Ocupado</option>
                <option value="Social Ativo">Social Ativo</option>
                <option value="Minimalista">Minimalista</option>
                <option value="Outro">Outro</option>
            </select>
            <button type="submit">Finalizar Registo</button>

        </form>

    </div>

</div>

<script src="../js/register_2.js"></script>

</body>
</html>
