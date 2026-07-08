<style>
    .card-panel {
        transition: all 0.2s ease-in-out;
        border-left: 4px solid transparent;
    }
    .card-panel:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
    }
    .card-panel.card-primary:hover {
        border-left-color: var(--bs-primary);
    }
    .card-panel.card-success:hover {
        border-left-color: var(--bs-success);
    }
    .card-panel.card-warning:hover {
        border-left-color: var(--bs-warning);
    }
    .card-panel.card-info:hover {
        border-left-color: var(--bs-info);
    }
</style>
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-light">Painel Principal</h2>
        <p class="lead text-muted">Bem-vindo(a) ao Sistema de Gestão da Qualidade.</p>
    </div>
</div>

<div class="row">
    <!-- Coluna Principal (Cards) -->
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6 mb-4">
                <a href="index.php?modulo=documentos" class="card card-panel card-primary text-center h-100 text-decoration-none">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-text card-icon text-primary"></i>
                        <h5 class="card-title mt-3">Documentos</h5>
                        <p class="card-text">Gerencie os documentos do sistema.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="index.php?modulo=usuarios" class="card card-panel card-success text-center h-100 text-decoration-none">
                    <div class="card-body">
                        <i class="bi bi-people card-icon text-success"></i>
                        <h5 class="card-title mt-3">Usuários</h5>
                        <p class="card-text">Administre os usuários e permissões.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="#" class="card card-panel card-warning text-center h-100 text-decoration-none">
                    <div class="card-body">
                        <i class="bi bi-gear card-icon text-warning"></i>
                        <h5 class="card-title mt-3">Configurações</h5>
                        <p class="card-text">Ajuste as configurações gerais do sistema.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 mb-4">
                <a href="index.php?modulo=siglas" class="card card-panel card-info text-center h-100 text-decoration-none">
                    <div class="card-body">
                        <i class="bi bi-spellcheck card-icon text-info"></i>
                        <h5 class="card-title mt-3">Siglas</h5>
                        <p class="card-text">Consulte e gerencie as siglas do sistema.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <!-- Coluna Lateral (Notificações) -->
    <div class="col-lg-4">
        <h5 class="mb-3 fw-light"><i class="bi bi-bell-fill text-secondary me-2"></i>Notificações</h5>
        


        <!-- Card de Agendados -->
        <div class="card shadow-sm mb-3">
            <h6 class="card-header bg-info text-white p-2">Agendados para os próximos 30 dias <span class="badge bg-light text-dark rounded-pill ms-1"><?php echo count($notificacoes['agendados']); ?></span></h6>
            <div class="card-body p-2" style="max-height: 150px; overflow-y: auto;">
                <?php if (empty($notificacoes['agendados'])): ?>
                    <p class="text-muted small m-2">Nenhum item agendado.</p>
                <?php else: ?>
                    <?php foreach ($notificacoes['agendados'] as $item): ?>
                        <div class="border-bottom pb-1 mb-1">
                            <div class="fw-bold small">
                                <span class="badge bg-<?php echo $item['tipo'] == 'Documento' ? 'primary' : 'success'; ?> me-1"><?php echo htmlspecialchars($item['tipo']); ?></span>
                                <?php echo htmlspecialchars($item['codigo']); ?>
                            </div>
                            <div class="small text-muted"><?php echo htmlspecialchars($item['nome']); ?></div>
                            <div class="text-info small">Entra em vigor em: <?php echo date('d/m/Y', strtotime($item['data_vigor'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card de Vencidos -->
        <div class="card shadow-sm mb-3">
            <h6 class="card-header bg-danger text-white p-2">Vencidos <span class="badge bg-light text-dark rounded-pill ms-1"><?php echo count($notificacoes['vencidos']); ?></span></h6>
            <div class="card-body p-2" style="max-height: 150px; overflow-y: auto;">
                <?php if (empty($notificacoes['vencidos'])): ?>
                    <p class="text-muted small m-2">Nenhum item vencido.</p>
                <?php else: ?>
                    <?php foreach ($notificacoes['vencidos'] as $doc): ?>
                        <div class="border-bottom pb-1 mb-1">
                            <div class="fw-bold small"><?php echo htmlspecialchars(utf8_encode($doc['codigo_documento'])); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars(utf8_encode($doc['nome_documento'])); ?></div>
                            <div class="text-danger small">Venceu em: <?php echo date('d/m/Y', strtotime($doc['data_analise_documento'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card de Próximos Vencimentos -->
        <div class="card shadow-sm mb-3">
            <h6 class="card-header bg-warning text-dark p-2">Próximos 6 meses <span class="badge bg-dark text-white rounded-pill ms-1"><?php echo count($notificacoes['proximos']); ?></span></h6>
            <div class="card-body p-2" style="max-height: 150px; overflow-y: auto;">
                <?php if (empty($notificacoes['proximos'])): ?>
                    <p class="text-muted small m-2">Nenhum item com vencimento próximo.</p>
                <?php else: ?>
                    <?php foreach ($notificacoes['proximos'] as $doc): ?>
                        <div class="border-bottom pb-1 mb-1">
                            <div class="fw-bold small"><?php echo htmlspecialchars(utf8_encode($doc['codigo_documento'])); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars(utf8_encode($doc['nome_documento'])); ?></div>
                            <div class="text-danger small">Vence em: <?php echo date('d/m/Y', strtotime($doc['data_analise_documento'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>