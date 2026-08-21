<?php
$is_edit = isset($categoria) && !empty($categoria);
$form_action = $is_edit ? 'index.php?modulo=categorias_editar' : 'index.php?modulo=categorias_cadastrar';
$page_title_action = $is_edit ? 'Editar Categoria' : 'Cadastrar Categoria';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark">
            <i class="bi bi-tags-fill me-2 text-primary"></i><?php echo $page_title_action; ?>
        </h3>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="<?php echo $form_action; ?>" method="POST">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_categoria" value="<?php echo $categoria['id_categoria']; ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nome_categoria" class="form-label">Nome da Categoria <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome_categoria" name="nome_categoria" value="<?php echo $is_edit ? htmlspecialchars($categoria['nome_categoria']) : ''; ?>" required>
                </div>

                <div class="col-md-3">
                    <label for="sigla_categoria" class="form-label">Sigla (Ex: FQ, IT) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="sigla_categoria" name="sigla_categoria" value="<?php echo $is_edit ? htmlspecialchars($categoria['sigla_categoria']) : ''; ?>" maxlength="10" required>
                </div>

                <div class="col-md-3">
                    <label for="escopo_categoria" class="form-label">Escopo</label>
                    <select id="escopo_categoria" name="escopo_categoria" class="form-select">
                        <option value="" <?php echo ($is_edit && empty($categoria['escopo_categoria'])) ? 'selected' : ''; ?>>Geral (Todos)</option>
                        <option value="SGQ UNIDADE" <?php echo ($is_edit && $categoria['escopo_categoria'] == 'SGQ UNIDADE') ? 'selected' : ''; ?>>SGQ Unidade</option>
                    </select>
                    <div class="form-text">
                        "SGQ Unidade" restringe a categoria para ser usada apenas pelas unidades.
                    </div>
                </div>

                <?php if ($is_edit): ?>
                <div class="col-12">
                    <label for="justificativa" class="form-label">Justificativa da Alteração <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="justificativa" name="justificativa" rows="3" required placeholder="Ex: Correção do nome, ajuste de escopo, etc."></textarea>
                </div>
                <?php endif; ?>

            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end">
                <a href="index.php?modulo=categorias" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-2"></i>Salvar</button>
            </div>

        </form>
    </div>
</div>