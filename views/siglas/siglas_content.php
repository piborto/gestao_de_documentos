<?php
$filtro_busca = isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '';
$filtro_status = isset($_GET['status']) ? intval($_GET['status']) : 1; // Padrão é 'Em Vigor'
?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h3 class="fw-bold text-black"><i class="bi bi-alphabet-uppercase me-2 text-primary"></i>Gestão de Siglas e Definições</h3>
        <p class="text-muted">Consulte, filtre e gerencie as siglas e definições do SGQ.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=siglas_pdf&busca=<?php echo urlencode($filtro_busca); ?>" target="_blank" class="btn btn-outline-danger fw-bold">
            <i class="bi bi-file-earmark-pdf me-2"></i>Gerar PDF
        </a>
        <a href="index.php?modulo=siglas_importar" class="btn btn-outline-success fw-bold">
            <i class="bi bi-cloud-upload me-2"></i>Importar CSV
        </a>
        <a href="index.php?modulo=siglas_sincronizar" class="btn btn-outline-info fw-bold">
            <i class="bi bi-file-earmark-pdf me-2"></i>Atualizar PDF
        </a>
        <a href="index.php?modulo=siglas_cadastrar" class="btn btn-primary fw-bold ms-2">
            <i class="bi bi-plus-circle me-2"></i>Cadastrar Sigla
        </a>

        
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group" aria-label="Filtro de Status">
        <a href="index.php?modulo=siglas&status=1" class="btn btn-sm <?php echo ($filtro_status == 1) ? 'btn-primary' : 'btn-outline-primary'; ?>">Em Vigor</a>
        <a href="index.php?modulo=siglas&status=2" class="btn btn-sm <?php echo ($filtro_status == 2) ? 'btn-primary' : 'btn-outline-primary'; ?>">Agendadas</a>
        <a href="index.php?modulo=siglas&status=3" class="btn btn-sm <?php echo ($filtro_status == 3) ? 'btn-primary' : 'btn-outline-primary'; ?>">Obsoletas</a>
    </div>
    <div>
        <span class="text-muted fw-bold"><?php echo count($listaSiglas); ?> siglas encontradas</span>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="modulo" value="siglas">
            <input type="hidden" name="status" value="<?php echo $filtro_status; ?>">
            <div class="col-md-10">
                <label class="form-label text-muted small fw-bold">Buscar por Sigla ou Definição</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" class="form-control" value="<?php echo $filtro_busca; ?>" placeholder="Ex: ABNT ou Associação Brasileira...">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filtrar</button>
                <a href="index.php?modulo=siglas&status=<?php echo $filtro_status; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                        <th style="width: 20%;">Sigla</th>
                        <th style="width: 50%;">Definição</th>
                        <th style="width: 20%;">Referência</th>
                        <th style="width: 5%;">Data</th>
                        <th class="text-end pe-3" style="width: 5%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaSiglas)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhuma sigla encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaSiglas as $sigla): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($sigla['nome_sigla']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($sigla['definicao_sigla'])); ?></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($sigla['referencia_sigla']); ?></small></td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($sigla['data_sigla'])); ?>
                                    <?php if ($sigla['id_status'] == 2): ?>
                                        <span class="badge bg-info ms-1">Agendada</span>
                                    <?php elseif ($sigla['id_status'] == 3): ?>
                                        <span class="badge bg-secondary ms-1">Obsoleta</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?modulo=siglas_editar&id=<?php echo $sigla['id_sigla']; ?>" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <button type="button" class="btn btn-outline-info" title="Histórico" data-bs-toggle="modal" data-bs-target="#modalHistoricoSigla" data-id="<?php echo $sigla['id_sigla']; ?>"><i class="bi bi-clock-history"></i></button>
                                        <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluirSigla" data-id="<?php echo $sigla['id_sigla']; ?>" data-nome="<?php echo htmlspecialchars($sigla['nome_sigla']); ?>"><i class="bi bi-trash"></i></button>
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
<div class="modal fade" id="modalHistoricoSigla" tabindex="-1" aria-labelledby="modalHistoricoSiglaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="modalHistoricoSiglaLabel"><i class="bi bi-clock-history me-2"></i>Histórico da Sigla</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="historico-content-sigla">
        <div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Excluir Sigla -->
<div class="modal fade" id="modalExcluirSigla" tabindex="-1" aria-labelledby="modalExcluirSiglaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="index.php?modulo=siglas_excluir">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalExcluirSiglaLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Exclusão</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir permanentemente a sigla <strong id="nome-sigla-excluir"></strong>?</p>
          <input type="hidden" name="id_sigla" id="id-sigla-excluir">
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
    var modalExcluir = document.getElementById('modalExcluirSigla');
    modalExcluir.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('id-sigla-excluir').value = button.getAttribute('data-id');
        document.getElementById('nome-sigla-excluir').textContent = button.getAttribute('data-nome');
    });

    var modalHistorico = document.getElementById('modalHistoricoSigla');
    modalHistorico.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var siglaId = button.getAttribute('data-id');
        var modalBody = document.getElementById('historico-content-sigla');
        modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>';
        
        fetch('views/siglas/siglas_historico_ajax.php?id=' + siglaId)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
            });
    });
});
</script>