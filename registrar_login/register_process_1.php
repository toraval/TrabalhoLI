<?php
session_start();
require_once("../connect_db.php"); 

$nome  = trim($_POST['nome']);
$email = trim($_POST['email']);
$pass  = trim($_POST['pass']);

$hash = password_hash($pass, PASSWORD_DEFAULT);
$tipo = 1;


$checkEmail = $conn->prepare("SELECT id FROM utilizadores WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$resultEmail = $checkEmail->get_result();

if ($resultEmail->num_rows > 0) {
    header("Location: register_1.php?erro=email_existente");
    exit;
}


$checkNome = $conn->prepare("SELECT id FROM utilizadores WHERE nome = ?");
$checkNome->bind_param("s", $nome);
$checkNome->execute();
$resultNome = $checkNome->get_result();

if ($resultNome->num_rows > 0) {
    header("Location: register_1.php?erro=nome_existente");
    exit;
}


$sql = $conn->prepare("
    INSERT INTO utilizadores 
    (nome, email, password, tipo_util) 
    VALUES (?, ?, ?, ?)
");

$sql->bind_param("sssi", $nome, $email, $hash, $tipo);
$sql->execute();

$_SESSION['user_id'] = $conn->insert_id;

header("Location: register_2.php");
exit;
