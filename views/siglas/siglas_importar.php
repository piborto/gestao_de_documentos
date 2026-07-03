<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-cloud-upload me-2 text-primary"></i>Importar Siglas via CSV</h3>
        <p class="text-muted">Faça o upload de um arquivo CSV para cadastrar ou atualizar siglas em lote.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=siglas" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-light border">
                    <h5 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Instruções</h5>
                    <p>O arquivo CSV deve seguir o padrão do sistema, com as seguintes colunas e usando ponto e vírgula (;) como separador:</p>
                    <p class="font-monospace small bg-light p-2 rounded">Status;Palavra_Sigla;Definicao;Referencia;Data_Atualizacao;...</p>
                    <p>O sistema irá ignorar siglas que já existirem no banco de dados para evitar duplicatas.</p>
                </div>

                <form method="POST" action="index.php?modulo=siglas_importar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Selecione o arquivo CSV</label>
                        <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-lg btn-primary fw-bold mt-3">
                            <i class="bi bi-play-circle me-2"></i> Iniciar Importação
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['import_status'])): ?>
            <hr class="my-4">
            <h4 class="text-center mb-3">Resultados da Importação</h4>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="alert alert-<?php echo $_SESSION['import_status']['tipo']; ?>">
                        <pre class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($_SESSION['import_status']['mensagem']); ?></pre>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['import_status']); ?>
        <?php endif; ?>
    </div>
</div>