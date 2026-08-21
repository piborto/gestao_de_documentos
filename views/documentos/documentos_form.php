<?php
// Garante que o controller já foi carregado pelo index.php e as variáveis $listaCategorias e $documento estão disponíveis.
$back_link_params = array('modulo' => 'documentos');
if (isset($_GET['status'])) $back_link_params['status'] = $_GET['status'];
if (isset($_GET['categoria'])) $back_link_params['categoria'] = $_GET['categoria'];
if (isset($_GET['distribuicao'])) $back_link_params['distribuicao'] = $_GET['distribuicao'];
if (isset($_GET['busca'])) $back_link_params['busca'] = $_GET['busca'];
$back_link = 'index.php?' . http_build_query(array_filter($back_link_params));

$is_edit = isset($documento) && !empty($documento);
$form_action = $is_edit ? 'documentos_editar' : 'documentos_cadastrar';
$page_subtitle = $is_edit ? 'Editar Documento' : 'Cadastrar Documento';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-file-earmark-plus me-2 text-primary"></i><?php echo $page_subtitle; ?></h3>
        <p class="text-muted">Preencha os campos abaixo para <?php echo $is_edit ? 'atualizar o' : 'adicionar um novo'; ?> documento.</p>
    </div>
    <div class="col-auto">
        <a href="<?php echo $back_link; ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form id="form-documento" method="POST" action="index.php?modulo=<?php echo $form_action; ?><?php echo $is_edit ? '&id=' . $documento['id_documento'] : ''; ?>" enctype="multipart/form-data">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_documento" value="<?php echo htmlspecialchars($documento['id_documento']); ?>">
                <!-- Campos ocultos para manter o estado dos filtros -->
                <input type="hidden" name="filtro_status" value="<?php echo isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '1'; ?>">
                <input type="hidden" name="filtro_categoria" value="<?php echo isset($_GET['categoria']) ? htmlspecialchars($_GET['categoria']) : ''; ?>">
                <input type="hidden" name="filtro_distribuicao" value="<?php echo isset($_GET['distribuicao']) ? htmlspecialchars($_GET['distribuicao']) : ''; ?>">
                <input type="hidden" name="filtro_busca" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
            <?php endif; ?>

            <div class="row g-3">
                <!-- Categoria (Sempre visível) -->
                <div class="col-md-6">
                    <label for="id_categoria" class="form-label">Categoria <span class="text-danger">*</span></label>
                    <select id="id_categoria" name="id_categoria" class="form-select" required onchange="carregarCamposConfigurados()">
                        <option value="">Selecione...</option>
                        <?php foreach ($listaCategorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>" data-sigla="<?php echo htmlspecialchars($categoria['sigla_categoria']); ?>" <?php echo ($is_edit && $documento['id_categoria'] == $categoria['id_categoria']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoria['sigla_categoria'] . ' - ' . $categoria['nome_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12" id="campos-dinamicos-container" aria-live="polite"></div>

                <!-- Tipo de Manual (MQ, MS) -->
                <div class="col-md-6 campo-dinamico" id="div-tipo_manual" style="display: none;">
                    <label for="tipo_manual" class="form-label text-primary fw-bold">Tipo de Manual</label>
                    <select id="tipo_manual" name="tipo_manual" class="form-select border-primary">
                        <option value="Controlado" <?php echo ($is_edit && $documento['controle_documento'] == 1) ? 'selected' : ''; ?>>Controlado</option>
                        <option value="Nao_Controlado" <?php echo ($is_edit && $documento['controle_documento'] == 0) ? 'selected' : ''; ?>>Não Controlado</option>
                    </select>
                </div>

                <!-- Código -->
                <div class="col-md-3 campo-dinamico" id="div-codigo" style="display: none;">
                    <label for="codigo_documento" class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="codigo_documento" name="codigo_documento" value="<?php echo $is_edit ? htmlspecialchars($documento['codigo_documento']) : ''; ?>">
                </div>

                <!-- Nome do Documento -->
                <div class="col-md-9 campo-dinamico" id="div-nome" style="display: none;">
                    <label for="nome_documento" class="form-label">Nome do Documento <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome_documento" name="nome_documento" value="<?php echo $is_edit ? htmlspecialchars($documento['nome_documento']) : ''; ?>">
                </div>

                <!-- Autor -->
                <div class="col-md-4 campo-dinamico" id="div-autor" style="display: none;">
                    <label for="autor_documento" class="form-label">Autor</label>
                    <input type="text" class="form-control" id="autor_documento" name="autor_documento" value="<?php echo $is_edit ? htmlspecialchars($documento['autor_documento']) : ''; ?>">
                </div>

                <!-- Revisão -->
                <div class="col-md-2 campo-dinamico" id="div-revisao" style="display: none;">
                    <label for="revisao_documento" class="form-label">Revisão</label>
                    <input type="number" class="form-control" id="revisao_documento" name="revisao_documento" value="<?php echo $is_edit ? htmlspecialchars($documento['revisao_documento']) : '0'; ?>">
                </div>

                <!-- Sufixo -->
                <div class="col-md-2 campo-dinamico" id="div-sufixo" style="display: none;">
                    <label for="sufixo" class="form-label">Sufixo/Idioma</label>
                    <input type="text" class="form-control" id="sufixo" name="sufixo" placeholder="Ex: PT" value="<?php echo $is_edit ? htmlspecialchars($documento['sufixo_documento']) : ''; ?>">
                </div>

                <!-- Ano (RE, CA, PR) -->
                <div class="col-md-2 campo-dinamico" id="div-ano" style="display: none;">
                    <label for="ano" class="form-label">Ano</label>
                    <input type="number" class="form-control" id="ano" name="ano_documento" value="<?php echo $is_edit ? htmlspecialchars($documento['ano_documento']) : date('Y'); ?>">
                </div>

                <!-- Data de Vigor/Publicação -->
                <div class="col-md-2 campo-dinamico" id="div-vigor" style="display: none;">
                    <label for="data_vigor_documento" class="form-label">Publicação <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="data_vigor_documento" name="data_vigor_documento" value="<?php echo $is_edit ? $documento['data_vigor_documento'] : ''; ?>">
                </div>

                <!-- Próxima Análise -->
                <div class="col-md-2 campo-dinamico" id="div-analise" style="display: none;">
                    <label for="data_analise_documento" class="form-label">Próxima Análise</label>
                    <input type="date" class="form-control" id="data_analise_documento" name="data_analise_documento" value="<?php echo $is_edit ? $documento['data_analise_documento'] : ''; ?>">
                </div>

                <!-- Arquivo -->
                <div class="col-md-6 campo-dinamico" id="div-arquivo" style="display: none;">
                    <label for="arquivo_documento" class="form-label">Anexar Documento <?php if (!$is_edit) echo '<span class="text-danger">*</span>'; ?></label>
                    <input class="form-control" type="file" id="arquivo_documento" name="arquivo_documento" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" <?php if (!$is_edit) echo 'required'; ?>>
                    <?php if ($is_edit && !empty($documento['arquivo_documento'])): ?>
                        <div class="form-text text-muted">
                            Arquivo atual: <a href="uploads/documentos/<?php echo htmlspecialchars($documento['sigla_categoria']); ?>/<?php echo urlencode($documento['arquivo_documento']); ?>" target="_blank"><?php echo htmlspecialchars($documento['arquivo_documento']); ?></a>.
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="remover_arquivo" name="remover_arquivo" value="1">
                                <label class="form-check-label text-danger" for="remover_arquivo">Remover arquivo atual</label>
                            </div>
                        </div>
                    <?php endif; ?>
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
                                    <label class="input-group-text bg-white flex-grow-1 small" for="loc_<?php echo $loc['id_local']; ?>"><?php echo htmlspecialchars($loc['nome_local']); ?></label>
                                    <input type="number" name="numero_manual[<?php echo $loc['id_local']; ?>]" class="form-control input-num-manual" value="1" style="max-width: 60px; display:none;" title="Nº da cópia" disabled>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                <div class="col-12">
                    <hr>
                    <label for="justificativa" class="form-label fw-bold text-danger">Justificativa da Alteração <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="justificativa" name="justificativa" rows="3" required placeholder="Ex: Correção do código, atualização da data de análise, etc."></textarea>
                </div>
                <?php endif; ?>

                <div class="col-12 text-end">
                    <hr>
                    <?php if ($is_edit): ?>
                        <div class="btn-group float-start">
                            <button type="button" class="btn btn-outline-warning" title="Tornar Obsoleto" data-bs-toggle="modal" data-bs-target="#modalObsoleto" data-id="<?php echo $documento['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($documento['nome_documento']); ?>"><i class="bi bi-archive me-2"></i>Tornar Obsoleto</button>
                            <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $documento['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($documento['nome_documento']); ?>"><i class="bi bi-trash me-2"></i>Excluir</button>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle me-2"></i>Salvar Documento</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function carregarCamposConfigurados() {
    var categoria = document.getElementById('id_categoria');
    var container = document.getElementById('campos-dinamicos-container');
    if (!categoria || !container || !categoria.value) {
        if (container) container.innerHTML = '';
        return;
    }

    var idLocal = <?php echo isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : 0; ?>;
    container.innerHTML = '<div class="text-muted small py-2"><span class="spinner-border spinner-border-sm me-2"></span>Carregando campos...</div>';

    fetch('index.php?modulo=documentos_campos_ajax&id_categoria=' + encodeURIComponent(categoria.value) + '&id_local=' + idLocal)
        .then(function (response) { return response.json(); })
        .then(function (resultado) {
            if (!resultado.sucesso) throw new Error('Falha ao carregar campos');
            var campos = resultado.campos || [];
            var mapa = {
                'codigo_documento': 'div-codigo', 'nome_documento': 'div-nome',
                'autor_documento': 'div-autor', 'revisao_documento': 'div-revisao',
                'sufixo_documento': 'div-sufixo', 'sufixo': 'div-sufixo',
                'ano_documento': 'div-ano', 'data_vigor_documento': 'div-vigor',
                'data_analise_documento': 'div-analise', 'arquivo_documento': 'div-arquivo',
                'distribuicao': 'div-distribuicao', 'controle_documento': 'div-tipo_manual'
            };
            var htmlExtra = '';
            campos.forEach(function (campo) {
                var nome = campo.nome_campo_interno;
                var blocoId = mapa[nome];
                var bloco = blocoId ? document.getElementById(blocoId) : null;
                var visivel = parseInt(campo.visivel, 10) === 1;
                var obrigatorio = parseInt(campo.obrigatorio, 10) === 1;
                if (bloco) {
                    bloco.style.display = visivel ? '' : 'none';
                    bloco.querySelectorAll('input, select, textarea').forEach(function (input) {
                        input.disabled = !visivel;
                        input.required = visivel && obrigatorio;
                    });
                    var label = bloco.querySelector('label');
                    if (label && visivel) label.innerHTML = escapeCamposHtml(campo.rotulo_personalizado || nome) + (obrigatorio ? ' <span class="text-danger">*</span>' : '');
                } else if (visivel && nome !== 'id_local') {
                    var tipo = campo.tipo_campo || 'text';
                    if (['text', 'number', 'date', 'email', 'file'].indexOf(tipo) === -1) tipo = 'text';
                    htmlExtra += '<div class="col-md-6 mb-3"><label class="form-label" for="metadado_' + escapeCamposHtml(nome) + '">' + escapeCamposHtml(campo.rotulo_personalizado || nome) + (obrigatorio ? ' <span class="text-danger">*</span>' : '') + '</label><input type="' + tipo + '" class="form-control" id="metadado_' + escapeCamposHtml(nome) + '" name="metadados[' + escapeCamposHtml(nome) + ']"' + (obrigatorio ? ' required' : '') + '></div>';
                }
            });
            container.innerHTML = htmlExtra;
            if (!htmlExtra) container.innerHTML = '<div class="text-muted small">Nenhum campo personalizado adicional.</div>';
        })
        .catch(function () {
            container.innerHTML = '<div class="alert alert-warning py-2">Não foi possível carregar a configuração dos campos.</div>';
        });
}

function escapeCamposHtml(valor) {
    return String(valor).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {
    var categoria = document.getElementById('id_categoria');
    if (categoria) {
        categoria.addEventListener('change', carregarCamposConfigurados);
        if (categoria.value) carregarCamposConfigurados();
    }
});
</script>

<?php if ($is_edit): ?>
<!-- Modal Tornar Obsoleto -->
<div class="modal fade" id="modalObsoleto" tabindex="-1" aria-labelledby="modalObsoletoLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="index.php?modulo=documentos_obsoleto">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="modalObsoletoLabel"><i class="bi bi-archive me-2"></i>Tornar Documento Obsoleto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Você está prestes a tornar o documento <strong id="nome-documento-obsoleto"></strong> obsoleto. Esta ação não pode ser desfeita.</p>
          <input type="hidden" name="id_documento" id="id-documento-obsoleto">
          <div class="mb-3">
            <label for="justificativa_obsoleto" class="form-label">Justificativa <span class="text-danger">*</span></label>
            <textarea class="form-control" id="justificativa_obsoleto" name="justificativa" rows="4" required placeholder="Ex: Substituído pela revisão X, incorporado ao documento Y, etc."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Confirmar Obsolescência</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="index.php?modulo=documentos_excluir">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalExcluirLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Exclusão</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir permanentemente o documento <strong id="nome-documento-excluir"></strong>?</p>
          <p class="text-danger small"><strong>Atenção:</strong> Esta ação é irreversível e o documento será removido do banco de dados sem deixar registro no histórico.</p>
          <input type="hidden" name="id_documento" id="id-documento-excluir">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Sim, Excluir</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Modal de Obsolescência
    var modalObsoleto = document.getElementById('modalObsoleto');
    if (modalObsoleto) {
        modalObsoleto.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('id-documento-obsoleto').value = button.getAttribute('data-id');
            document.getElementById('nome-documento-obsoleto').textContent = button.getAttribute('data-nome');
        });
    }

    // Modal de Exclusão
    var modalExcluir = document.getElementById('modalExcluir');
    if (modalExcluir) {
        modalExcluir.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('id-documento-excluir').value = button.getAttribute('data-id');
            document.getElementById('nome-documento-excluir').textContent = button.getAttribute('data-nome');
        });
    }

    // Preenche os locais de distribuição e números de cópia ao editar
    <?php if ($is_edit && !empty($documento['locais_distribuicao_ids'])): ?>
        var locaisVinculados = <?php echo json_encode($documento['locais_distribuicao_ids']); ?>;
        
        // Para distribuição padrão
        document.querySelectorAll('#div-distribuicao input[type="checkbox"]').forEach(function(checkbox) {
            if (locaisVinculados.hasOwnProperty(checkbox.value)) {
                checkbox.checked = true;
            }
        });

        // Para distribuição de manuais
        document.querySelectorAll('#div-distribuicao_manual .check-dist-manual').forEach(function(checkbox) {
            if (locaisVinculados.hasOwnProperty(checkbox.value)) {
                checkbox.checked = true;
                var inputNum = document.querySelector('input[name="numero_manual[' + checkbox.value + ']"]');
                if (inputNum) {
                    inputNum.value = locaisVinculados[checkbox.value] || '1';
                    inputNum.style.display = 'block'; // Torna o campo visível
                    inputNum.disabled = false; // Habilita o campo para edição
                }
            }
        });

        // Garante que os campos sejam atualizados no carregamento da página em modo de edição
        atualizarCamposDinamicos();
    <?php endif; ?>
});
</script>
<?php endif; ?>