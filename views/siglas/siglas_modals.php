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
        
        fetch('index.php?modulo=siglas_historico_ajax&id=' + siglaId)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
            });
    });
});
</script>