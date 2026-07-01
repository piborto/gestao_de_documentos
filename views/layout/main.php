<?php
// Constrói o caminho base de forma segura para os assets e links
$base_path = strpos($_SERVER['REQUEST_URI'], '/views/') !== false ? '../' : '';

// Validação de segurança
if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    // CORREÇÃO: Ajuste do caminho do login
    header("Location: " . $base_path . "views/login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'SGQ - Painel Principal'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_path; ?>assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d47a1; /* Seu novo azul principal */
            --primary-hover-color: #0a3880; /* Um tom um pouco mais escuro para hover */
            --bs-primary: var(--primary-color);
            --bs-primary-rgb: 13, 71, 161;
            --bs-link-color: var(--primary-color);
            --bs-link-hover-color: var(--primary-hover-color);
        }

        .navbar-custom {
            background-color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover-color);
            border-color: var(--primary-hover-color);
        }

        /* Classe específica para o cabeçalho da tabela, aplicando a cor também nas células th */
        .thead-custom th {
            background-color: var(--primary-color);
            color: white;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .table-responsive {
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-dark">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php">
            <img src="<?php echo $base_path; ?>uploads/Logo-Ital-branco.png" alt="Logo ITAL" height="30" class="d-inline-block align-top me-2">
            <i class="bi bi-folder2-open"></i>   Gestão de Documentos do SGQ
        </a>
        <div class="d-flex align-items-center text-white small">
            <span class="me-3"><i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['usuario_nome'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($_SESSION['usuario_perfil'], ENT_QUOTES, 'UTF-8'); ?>)</span>
            <a href="<?php echo $base_path; ?>controllers/AutenticacaoController.php?action=logout" class="btn btn-outline-light btn-sm">Sair <i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</nav>

<main class="container my-5">
    <?php if (isset($page_content_file) && file_exists($page_content_file)) include($page_content_file); ?>
</main>
<footer class="mt-4 text-center text-muted small">
    © 2026 Desenvolvido por GTI - Versão 1.0
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/documentos.js"></script>
</body>
</html>