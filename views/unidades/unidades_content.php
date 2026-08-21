<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-building-fill me-2 text-primary"></i>Gestão de Unidades e Documentos</h3>
        <p class="text-muted">Visualize os documentos vinculados a cada unidade/local de distribuição.</p>
    </div>
</div>

<div class="accordion" id="accordionUnidades">
    <?php if (empty($listaUnidades)): ?>
        <div class="alert alert-info">Nenhuma unidade encontrada.</div>
    <?php else: ?>
        <?php foreach ($listaUnidades as $index => $unidade): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?php echo $unidade['id_local']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $unidade['id_local']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $unidade['id_local']; ?>">
                        <strong><?php echo htmlspecialchars($unidade['nome_local']); ?></strong>
                        <span class="badge bg-primary rounded-pill ms-auto me-3"><?php echo count($unidade['documentos']); ?> Documento(s)</span>
                    </button>
                </h2>
                <div id="collapse-<?php echo $unidade['id_local']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $unidade['id_local']; ?>" data-bs-parent="#accordionUnidades">
                    <div class="accordion-body p-0">
                        <?php if (empty($unidade['documentos'])): ?>
                            <div class="p-3 text-center text-muted">Nenhum documento "Em Vigor" associado a esta unidade.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="thead-custom">
                                        <tr>
                                            <th style="width: 20%;">Código</th>
                                            <th>Nome do Documento</th>
                                            <th class="text-end" style="width: 10%;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($unidade['documentos'] as $doc): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($doc['sigla_categoria']); ?></span>
                                                    <?php echo htmlspecialchars(utf8_encode($doc['codigo_documento'])); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars(utf8_encode($doc['nome_documento'])); ?></td>
                                                <td class="text-end">
                                                    <a href="index.php?modulo=documentos_editar&id=<?php echo $doc['id_documento']; ?>" class="btn btn-outline-primary btn-sm" title="Editar Documento">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>