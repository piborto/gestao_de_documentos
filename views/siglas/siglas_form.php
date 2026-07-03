<?php
$is_edit = isset($sigla) && !empty($sigla);
$form_action = $is_edit ? 'siglas_editar' : 'siglas_cadastrar';
$page_subtitle = $is_edit ? 'Editar Sigla' : 'Cadastrar Sigla';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-alphabet-uppercase me-2 text-primary"></i><?php echo $page_subtitle; ?></h3>
        <p class="text-muted">Preencha os campos abaixo para <?php echo $is_edit ? 'atualizar a' : 'adicionar uma nova'; ?> sigla.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=siglas" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="index.php?modulo=<?php echo $form_action; ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_sigla" value="<?php echo htmlspecialchars($sigla['id_sigla']); ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="nome_sigla" class="form-label">Sigla <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome_sigla" name="nome_sigla" value="<?php echo $is_edit ? htmlspecialchars($sigla['nome_sigla']) : ''; ?>" required>
                </div>

                <div class="col-md-8">
                    <label for="referencia_sigla" class="form-label">Referência</label>
                    <input type="text" class="form-control" id="referencia_sigla" name="referencia_sigla" value="<?php echo $is_edit ? htmlspecialchars($sigla['referencia_sigla']) : ''; ?>">
                </div>

                <div class="col-12">
                    <label for="definicao_sigla" class="form-label">Definição <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="definicao_sigla" name="definicao_sigla" rows="4" required><?php echo $is_edit ? htmlspecialchars($sigla['definicao_sigla']) : ''; ?></textarea>
                </div>

                <div class="col-md-4">
                    <label for="data_sigla" class="form-label">Data de Publicação <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="data_sigla" name="data_sigla" value="<?php echo $is_edit ? $sigla['data_sigla'] : date('Y-m-d'); ?>" required>
                    <div class="form-text text-muted small"><i class="bi bi-info-circle"></i> Data futura = Agendado.</div>
                </div>

                <?php if ($is_edit && $sigla['id_status'] == 2): ?>
                    <div class="col-12">
                        <div class="alert alert-info small"><i class="bi bi-clock-fill me-2"></i>Esta sigla está <strong>agendada</strong> e se tornará pública na data de publicação.</div>
                    </div>
                <?php endif; ?>

                <div class="col-12 text-end">
                    <hr>
                    <?php if ($is_edit): ?>
                        <button type="button" class="btn btn-outline-danger float-start" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluirSigla" data-id="<?php echo $sigla['id_sigla']; ?>" data-nome="<?php echo htmlspecialchars($sigla['nome_sigla']); ?>"><i class="bi bi-trash me-2"></i>Excluir</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle me-2"></i>Salvar Sigla</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Include do modal de exclusão -->
<?php include(dirname(__FILE__) . '/siglas_content.php'); ?>