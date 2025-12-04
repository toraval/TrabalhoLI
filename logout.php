<?php
session_start();
$_SESSION = array();
session_destroy();
header('Location: main/indexv1.html');
exit();
?>