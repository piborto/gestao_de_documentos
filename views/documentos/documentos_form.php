<?php
// Garante que o controller já foi carregado pelo index.php e as variáveis $listaCategorias e $documento estão disponíveis.
$is_edit = isset($documento) && !empty($documento['id_documento']);
$form_action = $is_edit ? 'documentos_editar' : 'documentos_cadastrar';
$page_subtitle = $is_edit ? 'Editar Documento' : 'Cadastrar Documento';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-secondary"><i class="bi bi-file-earmark-plus me-2 text-primary"></i><?php echo $page_subtitle; ?></h3>
        <p class="text-muted">Preencha os campos abaixo para <?php echo $is_edit ? 'atualizar o' : 'adicionar um novo'; ?> documento.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=documentos" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="form-documento" method="POST" action="index.php?modulo=<?php echo $form_action; ?>" enctype="multipart/form-data">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_documento" value="<?php echo htmlspecialchars($documento['id_documento']); ?>">
            <?php endif; ?>

            <div class="row g-3">
                <!-- Categoria (Sempre visível) -->
                <div class="col-md-6">
                    <label for="id_categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                    <select id="id_categoria" name="id_categoria" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($listaCategorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>" data-sigla="<?php echo htmlspecialchars($categoria['sigla_categoria']); ?>">
                                <?php echo htmlspecialchars($categoria['sigla_categoria'] . ' - ' . $categoria['nome_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tipo de Manual (MQ, MS) -->
                <div class="col-md-6 campo-dinamico" id="div-tipo_manual" style="display: none;">
                    <label for="tipo_manual" class="form-label text-primary fw-bold">Tipo de Manual</label>
                    <select id="tipo_manual" name="tipo_manual" class="form-select border-primary">
                        <option value="Controlado">Controlado</option>
                        <option value="Nao_Controlado">Não Controlado</option>
                    </select>
                </div>

                <!-- Código -->
                <div class="col-md-3 campo-dinamico" id="div-codigo" style="display: none;">
                    <label for="codigo_documento" class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="codigo_documento" name="codigo_documento">
                </div>

                <!-- Nome do Documento -->
                <div class="col-md-9 campo-dinamico" id="div-nome" style="display: none;">
                    <label for="nome_documento" class="form-label">Nome do Documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome_documento" name="nome_documento">
                </div>

                <!-- Autor -->
                <div class="col-md-4 campo-dinamico" id="div-autor" style="display: none;">
                    <label for="autor_documento" class="form-label">Autor</label>
                    <input type="text" class="form-control" id="autor_documento" name="autor_documento">
                </div>

                <!-- Revisão -->
                <div class="col-md-2 campo-dinamico" id="div-revisao" style="display: none;">
                    <label for="revisao_documento" class="form-label">Revisão</label>
                    <input type="number" class="form-control" id="revisao_documento" name="revisao_documento" value="0">
                </div>

                <!-- Sufixo -->
                <div class="col-md-2 campo-dinamico" id="div-sufixo" style="display: none;">
                    <label for="sufixo" class="form-label">Sufixo/Idioma</label>
                    <input type="text" class="form-control" id="sufixo" name="sufixo" placeholder="Ex: PT">
                </div>

                <!-- Ano (RE, CA, PR) -->
                <div class="col-md-2 campo-dinamico" id="div-ano" style="display: none;">
                    <label for="ano" class="form-label">Ano</label>
                    <input type="number" class="form-control" id="ano" name="ano" value="<?php echo date('Y'); ?>">
                </div>

                <!-- Data de Vigor/Publicação -->
                <div class="col-md-2 campo-dinamico" id="div-vigor" style="display: none;">
                    <label for="data_vigor_documento" class="form-label">Publicação <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="data_vigor_documento" name="data_vigor_documento">
                </div>

                <!-- Próxima Análise -->
                <div class="col-md-2 campo-dinamico" id="div-analise" style="display: none;">
                    <label for="data_analise_documento" class="form-label">Próxima Análise</label>
                    <input type="date" class="form-control" id="data_analise_documento" name="data_analise_documento">
                </div>

                <!-- Arquivo -->
                <div class="col-md-6 campo-dinamico" id="div-arquivo" style="display: none;">
                    <label for="arquivo_documento" class="form-label">Anexar Documento <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="arquivo_documento" name="arquivo_documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
                </div>

                <!-- Distribuição Padrão -->
                <div class="col-12 campo-dinamico" id="div-distribuicao" style="display: none;">
                    <h6 class="text-muted fw-light border-bottom pb-2 mb-3">Distribuição / Unidades</h6>
                    <div class="row g-2">
                        <?php foreach ($listaLocais as $loc): ?>
                            <div class="col-md-3 div-checkbox-loc" data-nome="<?php echo htmlspecialchars($loc['nome_local']); ?>">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="distribuicao[]" value="<?php echo $loc['id_local']; ?>" id="loc_<?php echo $loc['id_local']; ?>"><label class="form-check-label small" for="loc_<?php echo $loc['id_local']; ?>"><?php echo htmlspecialchars($loc['nome_local']); ?></label></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Distribuição Manuais (com nº da cópia) -->
                <div class="col-12 campo-dinamico" id="div-distribuicao_manual" style="display: none;">
                    <h6 class="text-muted fw-light border-bottom pb-2 mb-3">Distribuição / Unidades (Manuais)</h6>
                    <div class="row g-2">
                        <?php foreach ($listaLocais as $loc): ?>
                            <div class="col-md-4 div-checkbox-loc" data-nome="<?php echo htmlspecialchars($loc['nome_local']); ?>">
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-text"><input class="form-check-input mt-0 check-dist-manual" type="checkbox" name="distribuicao[]" value="<?php echo $loc['id_local']; ?>"></div>
                                    <span class="input-group-text bg-white flex-grow-1 small"><?php echo htmlspecialchars($loc['nome_local']); ?></span>
                                    <input type="number" name="numero_manual[<?php echo $loc['id_local']; ?>]" class="form-control input-num-manual" value="1" style="max-width: 60px; display:none;" title="Nº da cópia" disabled>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-12 text-end">
                    <hr>
                    <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle me-2"></i>Salvar Documento</button>
                </div>
            </div>
        </form>
    </div>
</div>