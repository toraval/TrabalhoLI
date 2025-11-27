<?php
session_start();
require_once("../connect_db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: register_1.php");
    exit;
}

$id = $_SESSION['user_id'];

$idade       = $_POST['idade'];
$ocupacao    = $_POST['ocupacao'];
$salario     = $_POST['salario'];
$estilo_vida = $_POST['estilo_vida'];

/*$sql = $conn->prepare("
    UPDATE utilizadores SET 
        idade = ?, 
        ocupacao = ?, 
        salario = ?, 
        estilo_vida = ?, 
        objetivo = ?, 
        dificuldade = ?
    WHERE id = ?
");*/

$sql = $conn->prepare("
    UPDATE utilizadores SET 
        idade = ?, 
        ocupacao = ?, 
        salario = ?, 
        estilo_vida = ?
    WHERE id = ?
");

$sql->bind_param("isdsi", 
    $idade,
    $ocupacao,
    $salario,
    $estilo_vida,
    $id
);

$sql->execute();

unset($_SESSION['user_id']);

header("Location: ../login.php");
exit;
