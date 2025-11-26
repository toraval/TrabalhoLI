<?php
session_start();
require_once("../connect_db.php"); 

$nome  = trim($_POST['nome']);
$email = trim($_POST['email']);
$pass  = trim($_POST['pass']);

$hash = password_hash($pass, PASSWORD_DEFAULT);

$tipo = 1;

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
