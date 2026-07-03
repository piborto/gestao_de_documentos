<?php

// Recupera os valores atuais dos filtros para manter os campos preenchidos após o submit
$filtro_status    = isset($_GET['status']) ? intval($_GET['status']) : 1; // Padrão visual é "Em Vigor" (ID 1)
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$filtro_busca     = isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-black"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Gestão de Documentos</h3>
        <p class="text-muted">Consulte, filtre e acesse a lista de documentos do SGQ.</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-outline-danger fw-bold" onclick="abrirModalPdf()">
            <i class="bi bi-file-earmark-pdf me-2"></i><span id="textoBotaoPdf">Gerar PDF</span>
        </button>
        <a href="index.php?modulo=documentos_importar" class="btn btn-outline-success fw-bold"><i class="bi bi-cloud-upload me-2"></i>Importar CSV</a>
        <a href="index.php?modulo=documentos_sincronizar" class="btn btn-outline-info fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Sincronizar Arquivos</a>
        <a href="index.php?modulo=documentos_cadastrar" class="btn btn-primary fw-bold">
            <i class="bi bi-plus-circle me-2"></i>Cadastrar Documento
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="modulo" value="documentos">

            <div class="col-md-2">
                <label for="filtroStatus" class="form-label text-muted small fw-bold">Status</label>
                <select name="status" id="filtroStatus" class="form-select form-select-sm">
                    <option value="1" <?php echo $filtro_status === 1 ? 'selected' : ''; ?>>Em Vigor</option>
                    <option value="2" <?php echo $filtro_status === 2 ? 'selected' : ''; ?>>Agendado / Em Revisão</option>
                    <option value="3" <?php echo $filtro_status === 3 ? 'selected' : ''; ?>>Obsoletos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="filtroCategoria" class="form-label text-muted small fw-bold">Categoria</label>
                <select name="categoria" id="filtroCategoria" class="form-select form-select-sm" onchange="atualizarBotaoPdf()">
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
            <table class="table table-hover table-striped align-middle">
                <thead class="thead-custom">
                    <tr>
                        <th style="width: 10%;">Código</th>
                        <th style="width: 23%;">Nome</th>
                        <th class="text-center" style="width: 1%;"><i class="bi bi-paperclip"></i></th>
                        <?php if ($filtro_status == 3): // Cabeçalho para Obsoletos ?>
                            <th class="text-center" style="width: 5%;">Categoria</th>
                            <th style="width: 15%;">Responsável</th>
                            <th style="width: 10%;">Data Obsoleto</th>
                            <th style="width: 29%;">Distribuição (Histórico)</th>
                        <?php else: // Cabeçalho Padrão ?>
                            <th class="text-center" style="width: 5%;">Categoria</th>
                            <th style="width: 12%;">Autor</th>
                            <th class="text-center" style="width: 5%;">Revisão</th>
                            <th style="width: 8%;">Vigor</th>
                            <th style="width: 8%;">Análise</th>
                            <th style="width: 23%;">Distribuição</th>
                        <?php endif; ?>
                        <th class="text-end pe-3" style="width: 4%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaDocumentos)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">Nenhum documento encontrado com os filtros atuais.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaDocumentos as $doc): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($doc['codigo_documento']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($doc['nome_documento']); ?>
                                    <?php if (isset($doc['controle_documento']) && ($doc['controle_documento'] !== null) && in_array($doc['sigla_categoria'], array('MQ', 'MS'))): ?>
                                        <span class="badge bg-<?php echo $doc['controle_documento'] == 1 ? 'primary' : 'secondary'; ?> ms-2">
                                            <?php echo $doc['controle_documento'] == 1 ? 'Controlado' : 'Não Controlado'; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($doc['arquivo_documento'])): ?><i class="bi bi-file-earmark-text text-secondary"></i><?php endif; ?>
                                </td>
                                <?php if ($filtro_status == 3): // Colunas para Obsoletos ?>
                                    <td class="text-center"><span class="badge bg-white text-dark border"><?php echo htmlspecialchars($doc['sigla_categoria']); ?></span></td>
                                    <td>
                                        <small class="text-muted fst-italic">
                                            <?php echo htmlspecialchars(isset($doc['responsavel_obsoleto']) ? $doc['responsavel_obsoleto'] : 'Não informado'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo !empty($doc['data_obsoleto']) ? date('d/m/Y', strtotime($doc['data_obsoleto'])) : '-'; ?>
                                    </td>
                                <?php else: // Colunas Padrão ?>
                                    <td class="text-center"><span class="badge bg-white text-dark border"><?php echo htmlspecialchars($doc['sigla_categoria']); ?></span></td>
                                    <td><small><?php echo htmlspecialchars($doc['autor_documento'] ? $doc['autor_documento'] : '-'); ?></small></td>
                                    <td class="text-center"><?php echo htmlspecialchars($doc['revisao_documento'] !== '' ? $doc['revisao_documento'] : '-'); ?></td>
                                    <td><?php echo $doc['data_vigor_documento'] ? date('d/m/Y', strtotime($doc['data_vigor_documento'])) : '-'; ?></td>
                                    <td>
                                        <?php echo (!empty($doc['data_analise_documento']) && $doc['data_analise_documento'] != '0000-00-00') ? date('d/m/Y', strtotime($doc['data_analise_documento'])) : '-'; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if (!empty($doc['locais_distribuicao']) && $doc['locais_distribuicao'] !== '-'): ?>
                                        <?php
                                        $locais = explode(', ', $doc['locais_distribuicao']);
                                        foreach ($locais as $local):
                                        ?>
                                            <span class="badge bg-info text-dark me-1" style="font-size: 0.7em;"><?php echo htmlspecialchars(trim($local)); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: echo '-'; endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!empty($doc['arquivo_documento'])): ?>
                                            <a href="/uploads/documentos/<?php echo htmlspecialchars($doc['sigla_categoria']); ?>/<?php echo htmlspecialchars($doc['arquivo_documento']); ?>" target="_blank" class="btn btn-outline-secondary" title="Visualizar"><i class="bi bi-eye"></i></a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary disabled" style="opacity: 0.3;"><i class="bi bi-eye-slash"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-info" title="Histórico" data-bs-toggle="modal" data-bs-target="#modalHistorico" data-id="<?php echo $doc['id_documento']; ?>"><i class="bi bi-clock-history"></i></button>
                                        <?php if ($filtro_status == 3): // Ações para Obsoletos ?>
                                            <button type="button" class="btn btn-outline-success" title="Restaurar Documento"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        <?php else: // Ações para Ativos/Em Revisão ?>
                                            <a href="index.php?modulo=documentos_editar&id=<?php echo $doc['id_documento']; ?>" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn btn-outline-warning" title="Tornar Obsoleto" data-bs-toggle="modal" data-bs-target="#modalObsoleto" data-id="<?php echo $doc['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($doc['nome_documento']); ?>"><i class="bi bi-archive"></i></button>
                                            <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $doc['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($doc['nome_documento']); ?>"><i class="bi bi-trash"></i></button>
                                        <?php endif; ?>
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

<!-- Modal Histórico -->
<div class="modal fade" id="modalHistorico" tabindex="-1" aria-labelledby="modalHistoricoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalHistoricoLabel"><i class="bi bi-clock-history me-2"></i>Histórico do Documento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="historico-content">
        <div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

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

<!-- Modal Gerar PDF -->
<div class="modal fade" id="modalPdfData" tabindex="-1" aria-labelledby="modalPdfDataLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalPdfDataLabel"><i class="bi bi-file-pdf me-2"></i>Gerar Lista Mestra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="views/documentos/gerar_pdf.php" method="GET" target="_blank" onsubmit="fecharModalAposClick()">
                <div class="modal-body">
                    <div id="avisoExclusaoGeral" class="alert alert-info small" style="display:none;">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <strong>Nota:</strong> Ao gerar a lista geral, algumas categorias como DO, Manuais e Relatórios não são incluídas e devem ser geradas individualmente.
                    </div>
                    <div class="mb-3">
                        <label for="rodape_pdf" class="form-label fw-bold small text-muted">Texto do Rodapé:</label>
                        <input type="text" id="rodape_pdf" name="rodape" class="form-control" value="FQ-04.01 (anexo ao PQ-04.02) - RA - revisão 08 de 08/11/21">
                    </div>
                    <input type="hidden" name="categoria" id="idCategoriaPdf" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-printer me-2"></i>Gerar PDF</button>
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
    // Modal de Histórico
    var modalHistorico = document.getElementById('modalHistorico');
    modalHistorico.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var docId = button.getAttribute('data-id');
        var modalBody = document.getElementById('historico-content');
        modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>';
        
        fetch('views/documentos/documentos_historico_ajax.php?id=' + docId)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
            });
    });

    // Modal de Obsolescência
    var modalObsoleto = document.getElementById('modalObsoleto');
    modalObsoleto.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('id-documento-obsoleto').value = button.getAttribute('data-id');
        document.getElementById('nome-documento-obsoleto').textContent = button.getAttribute('data-nome');
    });

    // Modal de Exclusão
    var modalExcluir = document.getElementById('modalExcluir');
    modalExcluir.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('id-documento-excluir').value = button.getAttribute('data-id');
        document.getElementById('nome-documento-excluir').textContent = button.getAttribute('data-nome');
    });

    // Funções para o Modal de PDF
    window.atualizarBotaoPdf = function() {
        var cat = document.getElementById('filtroCategoria').value;
        var textoSpan = document.getElementById('textoBotaoPdf');
        textoSpan.textContent = (cat === "") ? "Gerar Lista Geral" : "Gerar PDF da Categoria";
    }

    window.abrirModalPdf = function() {
        var idCategoria = document.getElementById('filtroCategoria').value;
        document.getElementById('idCategoriaPdf').value = idCategoria;
        document.getElementById('avisoExclusaoGeral').style.display = (!idCategoria) ? 'block' : 'none';
        var modalPdf = new bootstrap.Modal(document.getElementById('modalPdfData'));
        modalPdf.show();
    }

    window.fecharModalAposClick = function() {
        setTimeout(function() {
            var modalPdfInstance = bootstrap.Modal.getInstance(document.getElementById('modalPdfData'));
            if (modalPdfInstance) modalPdfInstance.hide();
        }, 500);
    }
});
</script>