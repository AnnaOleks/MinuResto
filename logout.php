<?php
session_start();
if (!isset($_SESSION['klient']) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
if(isset($_POST['logout'])){
    session_destroy();
    header('Location: avaleht.php');
    exit();
}
?>