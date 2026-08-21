<?php
// Recupera os valores atuais dos filtros
$filtro_busca = isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '';
$filtro_status = isset($_GET['status']) ? intval($_GET['status']) : 1;
$filtro_data_de = isset($_GET['data_de']) ? htmlspecialchars($_GET['data_de']) : '';
$filtro_data_ate = isset($_GET['data_ate']) ? htmlspecialchars($_GET['data_ate']) : '';

// Constrói a query string com os filtros atuais para ser usada nos links
$query_params_string = http_build_query(array_filter(array(
    'status' => $filtro_status,
    'busca' => $filtro_busca,
    'data_de' => $filtro_data_de,
    'data_ate' => $filtro_data_ate,
)));
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-spellcheck me-2 text-primary"></i>Gestão de Siglas e Definições</h3>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#modalPdf"><i class="bi bi-file-earmark-pdf me-2"></i>Gerar PDF</button>
        <!--<a href="index.php?modulo=siglas_importar&<?php echo $query_params_string; ?>" class="btn btn-outline-success fw-bold"><i class="bi bi-cloud-upload me-2"></i>Importar CSV</a>
        <a href="index.php?modulo=siglas_sincronizar&<?php echo $query_params_string; ?>" class="btn btn-outline-info fw-bold"><i class="bi bi-arrow-repeat me-2"></i>Atualizar PDF Mestre</a> -->
        <a href="index.php?modulo=siglas_cadastrar&<?php echo $query_params_string; ?>" class="btn btn-primary fw-bold"><i class="bi bi-plus-circle me-2"></i>Cadastrar Sigla</a>
    </div>
</div>

<div class="d-flex justify-content-end mb-2">
    <span class="badge bg-primary rounded-pill px-3 py-2">
        <?php echo isset($total_siglas_vigor) ? $total_siglas_vigor : '0'; ?> Sigla(s) em Vigor
    </span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form id="form-filtros" method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="modulo" value="siglas">

            <div class="col-md-2">
                <label for="filtroStatus" class="form-label text-muted small fw-bold">Status</label>
                <select name="status" id="filtroStatus" class="form-select form-select-sm filtro-ajax">
                    <option value="1" <?php echo $filtro_status === 1 ? 'selected' : ''; ?>>Em Vigor</option>
                    <option value="2" <?php echo $filtro_status === 2 ? 'selected' : ''; ?>>Agendado</option>
                    <option value="3" <?php echo $filtro_status === 3 ? 'selected' : ''; ?>>Obsoleto</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="filtroBusca" class="form-label text-muted small fw-bold">Buscar Termo</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="busca" id="filtroBusca" class="form-control" value="<?php echo $filtro_busca; ?>" placeholder="Digite para pesquisar...">
                </div>
            </div>

            <div class="col-md-2">
                <label for="filtroDataDe" class="form-label text-muted small fw-bold">De</label>
                <input type="date" name="data_de" id="filtroDataDe" class="form-control form-control-sm filtro-ajax" value="<?php echo $filtro_data_de; ?>">
            </div>

            <div class="col-md-2">
                <label for="filtroDataAte" class="form-label text-muted small fw-bold">Até</label>
                <input type="date" name="data_ate" id="filtroDataAte" class="form-control form-control-sm filtro-ajax" value="<?php echo $filtro_data_ate; ?>">
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-filter me-1"></i> Filtrar</button>
                <a href="index.php?modulo=siglas" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div id="tabela-siglas-container" class="card-body p-0">
        <?php include 'siglas_tabela_ajax.php'; // Carrega a tabela inicial ?>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?modulo=siglas_excluir">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalExcluirLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir permanentemente a sigla <strong id="nome-sigla-excluir"></strong>?</p>
                    <p class="text-danger small"><strong>Atenção:</strong> Esta ação é irreversível.</p>
                    <input type="hidden" name="id_sigla" id="id-sigla-excluir">
                    <input type="hidden" name="filtro_status_retorno" value="<?php echo $filtro_status; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tornar Obsoleto -->
<div class="modal fade" id="modalObsoleto" tabindex="-1" aria-labelledby="modalObsoletoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?modulo=siglas_obsoleto">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="modalObsoletoLabel"><i class="bi bi-archive me-2"></i>Tornar Sigla Obsoleta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Você está prestes a tornar a sigla<strong id="nome-sigla-obsoleto"></strong> obsoleta.</p>
                    <input type="hidden" name="id_sigla" id="id-sigla-obsoleto">
                    <div class="mb-3">
                        <label for="justificativa_obsoleto" class="form-label">Justificativa <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="justificativa_obsoleto" name="justificativa" rows="3" required placeholder="Ex: Substituído por outro termo, não mais utilizado, etc."></textarea>
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

<!-- Modal Restaurar -->
<div class="modal fade" id="modalRestaurarSigla" tabindex="-1" aria-labelledby="modalRestaurarSiglaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="index.php?modulo=siglas_restaurar">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modalRestaurarSiglaLabel"><i class="bi bi-arrow-counterclockwise me-2"></i>Restaurar Sigla</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja restaurar a sigla <strong id="nome-sigla-restaurar"></strong> para o status "Em Vigor"?</p>
          <input type="hidden" name="id_sigla" id="id-sigla-restaurar">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Sim, Restaurar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Gerar PDF -->
<div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdfLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalPdfLabel"><i class="bi bi-file-pdf me-2"></i>Gerar PDF de Siglas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-pdf" action="index.php" method="GET" target="_blank">
            <input type="hidden" name="modulo" value="siglas_pdf">
            
            <div class="modal-body">
                <p class="small text-muted">O PDF será gerado com os mesmos filtros de busca aplicados na tela.</p>
                    <div class="mb-3">
                        <label for="rodape_pdf" class="form-label fw-bold small text-muted">Texto do Rodapé:</label>
                        <input type="text" id="rodape_pdf" name="rodape" class="form-control" value="FQ-04.11 (Anexo à IT-04.02.01) - RA - revisão 01 de 09/04/26">
                    </div>
                    <!-- Campos ocultos para passar os filtros -->
                    <input type="hidden" name="busca" id="pdf_busca">
                    <input type="hidden" name="data_de" id="pdf_data_de">
                    <input type="hidden" name="data_ate" id="pdf_data_ate">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-printer me-2"></i>Gerar PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Lógica dos Modais ---
    document.body.addEventListener('show.bs.modal', function(event) {
        const modal = event.target;
        const button = event.relatedTarget;
        if (!button) return;

        const id = button.getAttribute('data-id');
        const nome = button.getAttribute('data-nome');

        if (modal.id === 'modalExcluir') {
            modal.querySelector('#id-sigla-excluir').value = id;
            modal.querySelector('#nome-sigla-excluir').textContent = nome;
            const statusAtual = document.getElementById('filtroStatus').value;
            modal.querySelector('input[name="filtro_status_retorno"]').value = statusAtual;
        }

        if (modal.id === 'modalObsoleto') {
            modal.querySelector('#id-sigla-obsoleto').value = id;
            modal.querySelector('#nome-sigla-obsoleto').textContent = nome;
        }

        if (modal.id === 'modalPdf') {
            // Passa o filtro de busca para o formulário do PDF
            modal.querySelector('#pdf_busca').value = document.getElementById('filtroBusca').value;
            modal.querySelector('#pdf_data_de').value = document.getElementById('filtroDataDe').value;
            modal.querySelector('#pdf_data_ate').value = document.getElementById('filtroDataAte').value;
        }
    });

    // --- Lógica dos Modais (específico para conteúdo carregado via AJAX) ---
    document.getElementById('tabela-siglas-container').addEventListener('click', function(event) {
        let target = event.target.closest('button[data-bs-toggle="modal"]');
        if (!target) return;

        const modalId = target.getAttribute('data-bs-target');
        const id = target.getAttribute('data-id');
        const nome = target.getAttribute('data-nome');

        if (modalId === '#modalRestaurarSigla') {
            document.getElementById('id-sigla-restaurar').value = id;
            document.getElementById('nome-sigla-restaurar').textContent = nome;
        }

        // Delegação de evento para o histórico, garantindo que funcione após o AJAX
        if (target.getAttribute('data-bs-target') === '#modalHistoricoSigla') {
            const siglaId = target.getAttribute('data-id');
            const modalBody = document.getElementById('historico-content-sigla');
            modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>';
            fetch('index.php?modulo=siglas_historico_ajax&id=' + siglaId)
                .then(response => response.text())
                .then(html => { modalBody.innerHTML = html; });
        }
    });

    // --- Lógica da Pesquisa AJAX ---
    const searchInput = document.getElementById('filtroBusca');
    const selectFilters = document.querySelectorAll('.filtro-ajax');
    const form = document.getElementById('form-filtros');
    const container = document.getElementById('tabela-siglas-container');
    let typingTimer;
    const doneTypingInterval = 500; // 0.5s

    const performSearch = () => {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();

        // Atualiza a URL
        const newUrl = `${window.location.pathname}?${params}`;
        window.history.pushState({path: newUrl}, '', newUrl);

        // Mostra o loading
        container.style.opacity = '0.5';

        fetch(`views/siglas/siglas_tabela_ajax.php?${params}`)
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
            })
            .catch(error => {
                console.error('Erro ao buscar as siglas:', error);
                container.style.opacity = '1';
                container.innerHTML = '<p class="text-center text-danger">Erro ao carregar os dados.</p>';
            });
    };

    searchInput.addEventListener('keyup', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(performSearch, doneTypingInterval);
    });

    selectFilters.forEach(select => {
        select.addEventListener('change', performSearch);
    });
});
</script>