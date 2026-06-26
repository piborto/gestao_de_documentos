<?php
if (session_id() == "") {
    session_start();
}

// Se o usuário já estiver logado, redireciona para a home
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header("Location: ../index.php");
    exit();
}

// Define o título da página e o arquivo de conteúdo
$page_title = 'SGQ - Login';
$page_content_file = dirname(__FILE__) . '/contents/login_content.php';

// Inclui o layout de autenticação
require_once 'layout/auth.php';