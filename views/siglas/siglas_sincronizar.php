<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-file-earmark-pdf me-2 text-primary"></i>Atualizar PDF Mestre de Siglas</h3>
        <p class="text-muted">Substitua o arquivo PDF principal de Siglas e Definições.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=siglas" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <p>Esta ferramenta permite substituir o arquivo <strong>Siglas_e_Definicoes.pdf</strong> na pasta <strong>/uploads/siglas/</strong>.</p>
                <p>O arquivo antigo será movido para um backup no histórico antes de ser substituído.</p>
                <form method="POST" action="index.php?modulo=siglas_sincronizar" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="pdf_file" class="form-label">Selecione o novo arquivo PDF</label>
                        <input class="form-control" type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-lg btn-primary fw-bold mt-3">
                        <i class="bi bi-upload me-2"></i> Atualizar PDF
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($resultados)): ?>
            <hr class="my-4">
            <h4 class="text-center mb-3">Resultado da Atualização</h4>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="alert alert-<?php echo $resultados['tipo']; ?>">
                        <?php echo htmlspecialchars($resultados['mensagem']); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>