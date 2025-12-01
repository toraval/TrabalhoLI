<?php
session_start();
require_once("../connect_db.php");

// Verifica se os campos foram enviados
if (!isset($_POST['email'], $_POST['password'])) { // Removi 'nome' aqui
    header("Location: login.php?erro=CamposEmFalta");
    exit;
}

// Ajuste conforme seu formulário de login
$email = trim($_POST['email']);
$password = $_POST['password'];

// Consulta o utilizador (ajustado para usar apenas email)
$sql = $conn->prepare("
    SELECT id, nome, email, password, tipo_util, ocupacao, salario
    FROM utilizadores 
    WHERE email = ?
");

$sql->bind_param("s", $email);
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
$_SESSION['ocupacao']   = $user['ocupacao'];
$_SESSION['salario']    = $user['salario'];

// Redireciona para página principal
header("Location: ../index.php");
exit;
?>