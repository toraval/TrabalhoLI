<?php
session_start();

$_SESSION = array();

session_destroy();

header('Location: indexv1.html');
exit();
?>