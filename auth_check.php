<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redirecionar para indexv1.html
    header('Location: indexv1.html');
    exit();
}
?>