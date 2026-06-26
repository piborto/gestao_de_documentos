<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarAcesso() {
    if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
        header("Location: " . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/views/login.php");
        exit();
    }
}