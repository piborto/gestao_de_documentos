<?php
if (session_id() == "") {
    session_start();
}

if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    // Mude de: header("Location: ../index.php");
    header("Location: ../../index.php");
    exit();
}

// Define o título da página e o arquivo de conteúdo
$page_title = 'SGQ - Login';
$page_content_file = dirname(__FILE__) . '/login_content.php';

// Inclui o layout de autenticação
require_once '../layout/auth.php';