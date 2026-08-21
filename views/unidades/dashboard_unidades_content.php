<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Dashboard de Unidades</h3>
        <p class="text-muted">Visão geral do status dos documentos em todas as unidades.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="thead-custom">
                    <tr>
                        <th>Unidade / Local</th>
                        <th class="text-center">Total de Documentos</th>
                        <th class="text-center">Vigentes</th>
                        <th class="text-center">Obsoletos / Vencidos</th>
                        <th class="text-center" style="width: 15%;">% Conformidade</th>
                        <th class="text-center">Alertas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dados_dashboard)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhuma unidade encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dados_dashboard as $unidade): ?>
                            <?php
                                $total_docs = $unidade['total_docs'];
                                $docs_vigentes = $unidade['docs_vigentes'];
                                $docs_obsoletos = $unidade['docs_obsoletos'];
                                $conformidade = ($total_docs > 0) ? ($docs_vigentes / $total_docs) * 100 : 100;

                                $cor_conformidade = 'success';
                                if ($conformidade < 90) {
                                    $cor_conformidade = 'warning';
                                }
                                if ($conformidade < 75) {
                                    $cor_conformidade = 'danger';
                                }
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($unidade['nome_local']); ?></td>
                                <td class="text-center"><?php echo $total_docs; ?></td>
                                <td class="text-center text-success fw-bold"><?php echo $docs_vigentes; ?></td>
                                <td class="text-center text-danger fw-bold"><?php echo $docs_obsoletos; ?></td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?php echo $cor_conformidade; ?>" role="progressbar" style="width: <?php echo $conformidade; ?>%;" aria-valuenow="<?php echo $conformidade; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo number_format($conformidade, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($conformidade < 90): ?>
                                        <span class="badge bg-warning text-dark" title="Conformidade abaixo de 90%"><i class="bi bi-exclamation-triangle-fill"></i> Baixa Conformidade</span>
                                    <?php endif; ?>
                                    <?php if ($docs_obsoletos > 0): ?>
                                        <span class="badge bg-danger" title="<?php echo $docs_obsoletos; ?> documento(s) obsoleto(s)"><i class="bi bi-archive-fill"></i> Obsoletos</span>
                                    <?php endif; ?>
                                    <?php if ($conformidade >= 90 && $docs_obsoletos == 0): ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>