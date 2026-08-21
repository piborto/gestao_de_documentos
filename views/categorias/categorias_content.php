<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-tags-fill me-2 text-primary"></i>Gestão de Categorias</h3>
        <p class="text-muted">Adicione, edite ou remova categorias de documentos do sistema.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=categorias_cadastrar" class="btn btn-primary fw-bold"><i class="bi bi-plus-circle me-2"></i>Cadastrar Categoria</a>
    </div>
</div>

<?php if(isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">
        <?php 
            if($_GET['sucesso'] == 'cadastro') echo 'Categoria cadastrada com sucesso!';
            if($_GET['sucesso'] == 'edicao') echo 'Categoria atualizada com sucesso!';
        ?>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="thead-custom">
                    <tr>
                        <th style="width: 40%;">Nome da Categoria</th>
                        <th style="width: 15%;">Sigla</th>
                        <th style="width: 30%;">Escopo</th>
                        <th class="text-end pe-3" style="width: 15%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaCategorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Nenhuma categoria encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaCategorias as $cat): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($cat['nome_categoria']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($cat['sigla_categoria']); ?></span></td>
                                <td><?php echo !empty($cat['escopo_categoria']) ? htmlspecialchars($cat['escopo_categoria']) : 'Geral (Todos)'; ?></td>
                                <td class="text-end pe-3">
                                    <a href="index.php?modulo=categorias_editar&id=<?php echo $cat['id_categoria']; ?>" class="btn btn-outline-primary btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>