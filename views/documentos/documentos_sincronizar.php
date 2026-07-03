<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Sincronizar Arquivos</h3>
        <p class="text-muted">Vincule automaticamente os arquivos da pasta de uploads aos seus respectivos registros no sistema.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=documentos" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <p>Esta ferramenta irá varrer a pasta <strong>/uploads/documentos/</strong> e todas as suas subpastas (por categoria, ex: /IT, /PQ, etc.).</p>
                <p>Para cada arquivo encontrado, o sistema tentará localizar um registro de documento com o <strong>mesmo nome de arquivo</strong> e irá atualizar o campo `arquivo_documento` no banco de dados.</p>
                <form method="POST" action="index.php?modulo=documentos_sincronizar">
                    <button type="submit" class="btn btn-lg btn-primary fw-bold mt-3">
                        <i class="bi bi-play-circle me-2"></i> Iniciar Sincronização
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($resultados)): ?>
            <hr class="my-4">
            <h4 class="text-center mb-3">Resultados da Sincronização</h4>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="alert alert-info">
                        <ul class="list-unstyled mb-0">
                            <li><i class="bi bi-search me-2"></i><strong>Arquivos encontrados na pasta:</strong> <?php echo $resultados['total_arquivos']; ?></li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Documentos atualizados com sucesso:</strong> <?php echo $resultados['sucesso']; ?></li>
                            <li><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i><strong>Arquivos sem registro correspondente:</strong> <?php echo $resultados['nao_encontrados']; ?></li>
                        </ul>
                    </div>

                    <?php if (!empty($resultados['log_nao_encontrados'])): ?>
                        <div class="mt-3">
                            <p class="fw-bold">Detalhes dos arquivos não vinculados:</p>
                            <textarea class="form-control bg-light" rows="8" readonly><?php
                                foreach ($resultados['log_nao_encontrados'] as $log) {
                                    echo htmlspecialchars($log) . "\n";
                                }
                            ?></textarea>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($resultados['log_sucesso'])): ?>
                        <div class="mt-3">
                            <p class="fw-bold">Detalhes dos documentos atualizados:</p>
                            <textarea class="form-control bg-light" rows="8" readonly><?php
                                foreach ($resultados['log_sucesso'] as $log) {
                                    echo htmlspecialchars($log) . "\n";
                                }
                            ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>