<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-bell-fill me-2 text-primary"></i>Alertas e Notificações</h3>
        <p class="text-muted">Acompanhe os documentos que precisam de atenção e os itens agendados.</p>
    </div>
</div>

<div class="row">
    <!-- Coluna de Documentos Vencidos -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>Documentos Vencidos</h5>
            </div>
            <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($notificacoes['vencidos'])): ?>
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                        <p class="mt-2">Nenhum documento vencido.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notificacoes['vencidos'] as $doc): ?>
                            <li class="list-group-item">
                                <div class="fw-bold small"><?php echo htmlspecialchars($doc['codigo_documento']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($doc['nome_documento']); ?></div>
                                <div class="text-danger small fw-bold">Venceu em: <?php echo date('d/m/Y', strtotime($doc['data_analise_documento'])); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light text-end">
                <span class="badge bg-danger rounded-pill"><?php echo count($notificacoes['vencidos']); ?> item(s)</span>
            </div>
        </div>
    </div>

    <!-- Coluna de Próximos Vencimentos -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Próximos do Vencimento (6 meses)</h5>
            </div>
            <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($notificacoes['proximos'])): ?>
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                        <p class="mt-2">Nenhum documento com vencimento próximo.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notificacoes['proximos'] as $doc): ?>
                            <li class="list-group-item">
                                <div class="fw-bold small"><?php echo htmlspecialchars($doc['codigo_documento']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($doc['nome_documento']); ?></div>
                                <div class="text-warning-emphasis small fw-bold">Vence em: <?php echo date('d/m/Y', strtotime($doc['data_analise_documento'])); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light text-end">
                <span class="badge bg-warning text-dark rounded-pill"><?php echo count($notificacoes['proximos']); ?> item(s)</span>
            </div>
        </div>
    </div>

    <!-- Coluna de Itens Agendados -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm border-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-check-fill me-2"></i>Itens Agendados (30 dias)</h5>
            </div>
            <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($notificacoes['agendados'])): ?>
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-check-circle-fill fs-2 text-success"></i>
                        <p class="mt-2">Nenhum item agendado para os próximos 30 dias.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notificacoes['agendados'] as $item): ?>
                            <li class="list-group-item">
                                <div class="fw-bold small">
                                    <span class="badge bg-<?php echo $item['tipo'] == 'Documento' ? 'primary' : 'success'; ?> me-1"><?php echo htmlspecialchars($item['tipo']); ?></span>
                                    <?php echo htmlspecialchars($item['codigo']); ?>
                                </div>
                                <div class="small text-muted"><?php echo htmlspecialchars($item['nome']); ?></div>
                                <div class="text-info-emphasis small fw-bold">Entra em vigor em: <?php echo date('d/m/Y', strtotime($item['data_vigor'])); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light text-end">
                <span class="badge bg-info text-dark rounded-pill"><?php echo count($notificacoes['agendados']); ?> item(s)</span>
            </div>
        </div>
    </div>
</div>
