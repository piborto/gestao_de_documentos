<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold text-dark"><i class="bi bi-house-door-fill me-2 text-primary"></i>Início</h3>
        <p class="text-muted">Bem-vindo(a) ao Sistema de Gestão da Qualidade. Selecione um módulo para começar.</p>
    </div>
</div>

<div class="row">
    <!-- Card Documentos -->
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="index.php?modulo=documentos" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-folder-fill card-icon text-primary"></i>
                <h5 class="card-title mt-3">Documentos</h5>
            </div>
        </a>
    </div>

    <!-- Card Unidades -->
    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 4, 5))): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="index.php?modulo=unidades" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-building-fill card-icon text-secondary"></i>
                <h5 class="card-title mt-3">Unidades</h5>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Card Documentos Externos -->
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="#" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-file-earmark-text-fill card-icon text-info"></i>
                <h5 class="card-title mt-3">Documentos Externos</h5>
            </div>
        </a>
    </div>
    
    <!-- Card Siglas - Visível apenas para perfis 1 e 4 -->
    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 4))): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="index.php?modulo=siglas" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-spellcheck card-icon text-info"></i>
                <h5 class="card-title mt-3">Siglas e Definições</h5>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Card Relatórios -->
    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 2, 4, 5))): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="#" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-file-earmark-bar-graph-fill card-icon text-success"></i>
                <h5 class="card-title mt-3">Relatórios</h5>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Card Alertas -->
    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 2, 4))): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="#" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-bell-fill card-icon text-danger"></i>
                <h5 class="card-title mt-3">Alertas</h5>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Card Dashboard -->
    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 5))): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="index.php?modulo=dashboard_unidades" class="card h-100 text-center text-decoration-none card-panel">
            <div class="card-body">
                <i class="bi bi-graph-up card-icon text-dark"></i>
                <h5 class="card-title mt-3">Dashboard</h5>
            </div>
        </a>
    </div>
    <?php endif; ?>

    <!-- Card Configurações -->
    <?php if (isset($_SESSION['id_perfil']) && in_array($_SESSION['id_perfil'], array(1, 4))): ?>
    <div class="col-md-4 col-lg-3 mb-4">
        <a href="#" class="card h-100 text-center text-decoration-none card-panel" data-bs-toggle="collapse" data-bs-target="#configuracoes-submenu-cards">
            <div class="card-body">
                <i class="bi bi-gear-fill card-icon text-warning"></i>
                <h5 class="card-title mt-3">Configurações</h5>
            </div>
        </a>
    </div>
    <?php endif; ?>


</div>