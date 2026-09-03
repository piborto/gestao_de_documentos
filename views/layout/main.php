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
        /* --- Sidebar Toggle Styles --- */
        .sidebar {
            position: fixed;
            /* A altura (top) deve ser exata ao tamanho da sua Navbar azul. 
               Normalmente no Bootstrap 5 é 56px, 60px ou 64px. Ajuste se necessário. */
            top: 56px; 
            bottom: 0;
            left: 0;
            z-index: 1040; /* Fica uma camada abaixo da Navbar (1050) */
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa; /* Garante fundo opaco */
            transition: transform 0.3s ease-in-out; /* Animação super suave */
            transform: translateX(0); /* Estado visível */
        }

        .sidebar-collapsed {
            transform: translateX(-100%); /* Esconde a sidebar jogando-a para a esquerda */
        }

        .sidebar-sticky { 
            position: relative; 
            top: 0; 
            height: calc(100vh - 56px);
            padding-top: .5rem; 
            overflow-x: hidden; 
            overflow-y: auto; 
        }
        main {
            transition: all 0.3s ease-in-out;
        }

        /* Classe aplicada via JS quando a sidebar for oculta */
        .main-expanded {
            margin-left: 0 !important;
            width: 100% !important;
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }
    </style>
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
        /* --- Tabela Responsiva Fix --- */
        .table {
            font-size: 0.85rem; /* Reduz a fonte para caber mais conteúdo */
        }
        .distribuicao-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            white-space: normal;
        }
        .col-distribuicao {
            max-width: 250px; 
            white-space: normal !important; 
            display: flex; flex-wrap: wrap; gap: 4px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-dark sticky-top" style="z-index: 1050;">
    <div class="container-fluid d-flex align-items-center">
        <button id="sidebarToggle" class="btn btn-outline-light btn-sm me-3"><i class="bi bi-list fs-5"></i></button>
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $base_path; ?>index.php">
            <img src="<?php echo $base_path; ?>uploads/Logo-Ital-branco.png" alt="Logo ITAL" height="30" class="d-inline-block align-top me-2">
            <i class="bi bi-folder2-open"></i>   Gestão de Documentos do SGQ
        </a>
        <div class="d-flex align-items-center text-white small ms-auto">
            <span class="me-3"><i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['usuario_nome'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($_SESSION['usuario_perfil'], ENT_QUOTES, 'UTF-8'); ?>)</span>
            <a href="<?php echo $base_path; ?>controllers/AutenticacaoController.php?action=logout" class="btn btn-outline-light btn-sm">Sair <i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($modulo == 'inicio') ? 'active' : ''; ?>" href="index.php?modulo=inicio"><i class="bi bi-house-door-fill me-2"></i>Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($modulo == 'documentos') ? 'active' : ''; ?>" href="index.php?modulo=documentos"><i class="bi bi-folder-fill me-2"></i>Documentos</a>
                    </li>
                    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 4, 5))): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($modulo == 'unidades') ? 'active' : ''; ?>" href="index.php?modulo=unidades"><i class="bi bi-building-fill me-2"></i>Unidades</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-file-earmark-text-fill me-2"></i>Documentos Externos</a>
                    </li>
                    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 4))): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($modulo == 'siglas') ? 'active' : ''; ?>" href="index.php?modulo=siglas"><i class="bi bi-spellcheck me-2"></i>Siglas</a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 2, 4, 5))): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Relatórios</a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 2, 4))): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center <?php echo ($modulo == 'alertas') ? 'active' : ''; ?>" href="index.php?modulo=alertas">
                            <span><i class="bi bi-bell-fill me-2"></i>Alertas</span>
                            <?php if (isset($total_notificacoes) && $total_notificacoes > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $total_notificacoes; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 2, 5))): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($modulo == 'dashboard_unidades') ? 'active' : ''; ?>" href="index.php?modulo=dashboard_unidades"><i class="bi bi-graph-up me-2"></i>Dashboard</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 2, 4))): ?><li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#configuracoes-submenu" role="button" aria-expanded="false" aria-controls="configuracoes-submenu">
                            <i class="bi bi-gear-fill me-2"></i>Configurações
                        </a>
                        <div class="collapse" id="configuracoes-submenu">
                            <ul class="nav flex-column ms-4">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($modulo == 'usuarios') ? 'active' : ''; ?>" href="index.php?modulo=usuarios">Usuários</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($modulo == 'configurar_campos') ? 'active' : ''; ?>" href="index.php?modulo=configurar_campos"><i class="bi bi-sliders me-2"></i>Configurar Campos</a>
                                </li>
                                <?php if (isset($_SESSION['id_perfil']) && (int)$_SESSION['id_perfil'] === 4): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo ($modulo == 'ftp_explorer') ? 'active' : ''; ?>" href="index.php?modulo=ftp_explorer"><i class="bi bi-hdd-network me-2"></i>Explorador FTP</a>
                                </li>
                                <?php endif; ?>
                                <?php if ($_SESSION['id_perfil'] != 2): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo (strpos($modulo, 'categorias') === 0) ? 'active' : ''; ?>" href="index.php?modulo=categorias">Categorias</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="pt-3 pb-2 mb-3 border-bottom">
                <?php if (isset($page_content_file) && file_exists($page_content_file)) include($page_content_file); ?>
            </div>
        </main>
    </div>
</div>

<footer class="mt-4 text-center text-muted small">
    © 2026 Desenvolvido por GTI - Versão 2.0
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/documentos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebarMenu');
        const mainContent = document.querySelector('main.col-md-9'); // Seleciona o conteúdo principal

        if(toggleBtn && sidebar && mainContent) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('main-expanded');
            });
        }
    });
</script>
</body>
</html>