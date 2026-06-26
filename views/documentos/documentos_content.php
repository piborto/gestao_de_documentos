<?php

// Recupera os valores atuais dos filtros para manter os campos preenchidos após o submit
$filtro_status    = isset($_GET['status']) ? intval($_GET['status']) : 1;
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$filtro_busca     = isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-secondary"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Gestão de Documentos</h3>
        <p class="text-muted">Consulte, filtre e acesse a lista de documentos do SGQ.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=documentos_cadastrar" class="btn btn-primary fw-bold"><i class="bi bi-plus-circle me-2"></i>Cadastrar Documento</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="modulo" value="documentos">

            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="1" <?php echo $filtro_status === 1 ? 'selected' : ''; ?>>Ativos</option>
                    <option value="2" <?php echo $filtro_status === 2 ? 'selected' : ''; ?>>Em Revisão</option>
                    <option value="3" <?php echo $filtro_status === 3 ? 'selected' : ''; ?>>Obsoletos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">Categoria</label>
                <select name="categoria" class="form-select form-select-sm">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($listaCategorias as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>" <?php echo $filtro_categoria == $categoria['id_categoria'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['sigla_categoria'] . ' - ' . $categoria['nome_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold">Buscar por Código ou Nome</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control" value="<?php echo $filtro_busca; ?>" placeholder="Ex: PQ-01 ou Manual de Termos...">
                </div>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filtrar</button>
                <a href="index.php?modulo=documentos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 15%;">Código</th>
                        <th style="width: 40%;">Título do Documento</th>
                        <th style="width: 10%;" class="text-center">Rev.</th>
                        <th style="width: 15%;">Vigor</th>
                        <th style="width: 10%;" class="text-center">PDF</th>
                        <th class="pe-3 text-end" style="width: 10%;">Ações</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($listaDocumentos)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x display-6 d-block mb-2 text-black-50"></i>
                                Nenhum documento encontrado com os filtros selecionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaDocumentos as $doc): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark"><?php echo htmlspecialchars($doc['codigo_documento']); ?></td>
                                <td>
                                    <span class="badge bg-light text-primary border me-1"><?php echo htmlspecialchars($doc['sigla_categoria']); ?></span>
                                    <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($doc['nome_documento']); ?></span>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary text-white rounded-pill"><?php echo str_pad($doc['revisao_documento'], 2, "0", STR_PAD_LEFT); ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($doc['data_vigor_documento'])); ?></td>
                                <td class="text-center">
                                    <?php if (!empty($doc['arquivo_documento'])): ?>
                                        <a href="/arquivos/<?php echo $doc['arquivo_documento']; ?>" target="_blank" class="btn btn-link text-danger p-0 fs-5"><i class="bi bi-filetype-pdf"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?modulo=documentos_editar&id=<?php echo $doc['id_documento']; ?>" class="btn btn-outline-secondary border-0 text-primary" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                        <a href="index.php?modulo=documentos_historico&id=<?php echo $doc['id_documento']; ?>" class="btn btn-outline-secondary border-0 text-info" title="Histórico"><i class="bi bi-clock-history"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>