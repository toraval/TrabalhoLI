<?php
session_start();
require_once("../connect_db.php");

// Verifica se os campos foram enviados
if (!isset($_POST['nome'], $_POST['email'], $_POST['password'])) {
    header("Location: login.php?erro=CamposEmFalta");
    exit;
}

$nome     = trim($_POST['nome']);
$email    = trim($_POST['email']);
$password = $_POST['password'];

// Consulta o utilizador
$sql = $conn->prepare("
    SELECT id, nome, email, password, tipo_util 
    FROM utilizadores 
    WHERE nome = ? AND email = ?
");

$sql->bind_param("ss", $nome, $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php?erro=UtilizadorNaoExiste");
    exit;
}

$user = $result->fetch_assoc();

// Verifica se a password está correta
if (!password_verify($password, $user['password'])) {
    header("Location: login.php?erro=PasswordErrada");
    exit;
}

// Login bem-sucedido → cria sessão
$_SESSION['user_id']    = $user['id'];
$_SESSION['nome']       = $user['nome'];
$_SESSION['email']      = $user['email'];
$_SESSION['tipo_util']  = $user['tipo_util'];

// Redireciona para página principal
header("Location: ../main/indexv1.html");
exit;
