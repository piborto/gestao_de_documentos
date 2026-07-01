<?php
// Inicia a sessão se ainda não foi iniciada para poder acessar as variáveis de feedback
if (session_id() == "") {
    session_start();
}
?>
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-secondary"><i class="bi bi-cloud-upload me-2 text-primary"></i>Importar Documentos via CSV</h3>
        <p class="text-muted">Faça o upload do arquivo CSV gerado pelo sistema antigo para migrar os dados.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=documentos" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<?php if (isset($_SESSION['import_status'])): ?>
    <div class="alert alert-<?php echo $_SESSION['import_status']['tipo']; ?> alert-dismissible fade show" role="alert">
        <strong>Resultado da Importação:</strong><br>
        <?php echo nl2br(htmlspecialchars($_SESSION['import_status']['mensagem'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['import_status']); ?>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="index.php?modulo=documentos_importar" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="csv_file" class="form-label">Selecione o arquivo CSV</label>
                <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                <div class="form-text">O arquivo deve seguir o padrão exportado pelo sistema antigo, com separador ponto e vírgula (;) e codificação UTF-8.</div>
            </div>
            <hr>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-play-circle me-2"></i>Iniciar Importação</button>
            </div>
        </form>
    </div>
</div>