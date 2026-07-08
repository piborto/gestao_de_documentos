<?php
$back_link_params = array('modulo' => 'documentos');
if (isset($_GET['status'])) $back_link_params['status'] = $_GET['status'];
if (isset($_GET['categoria'])) $back_link_params['categoria'] = $_GET['categoria'];
if (isset($_GET['distribuicao'])) $back_link_params['distribuicao'] = $_GET['distribuicao'];
if (isset($_GET['busca'])) $back_link_params['busca'] = $_GET['busca'];
$back_link = 'index.php?' . http_build_query(array_filter($back_link_params));
?>
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-arrow-repeat me-2 text-primary"></i>Sincronizar Arquivos por Upload</h3>
        <p class="text-muted">Envie os arquivos para que o sistema os vincule automaticamente aos registros de documentos correspondentes.</p>
    </div>
    <div class="col-auto">
        <a href="<?php echo $back_link; ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <p>Selecione até <strong>10 arquivos</strong> do seu computador. O sistema tentará encontrar um registro de documento correspondente para cada arquivo com base no <strong>nome exato do arquivo</strong> e o moverá para a pasta da sua categoria.</p>
                
                <form method="POST" action="index.php?modulo=documentos_sincronizar" enctype="multipart/form-data" id="form-sincronizar">
                    <div class="mb-3">
                        <label for="arquivos" class="form-label">Selecione os arquivos:</label>
                        <input class="form-control" type="file" id="arquivos" name="arquivos[]" multiple required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-cloud-upload me-2"></i> Enviar e Sincronizar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($resultados)): ?>
            <hr class="my-4">
            <h4 class="text-center mb-3">Resultados da Sincronização</h4>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <?php if (!empty($resultados['sucesso'])): ?>
                        <div class="alert alert-success">
                            <h5 class="alert-heading">Arquivos Vinculados com Sucesso (<?php echo count($resultados['sucesso']); ?>)</h5>
                            <ul class="mb-0 small">
                                <?php foreach ($resultados['sucesso'] as $log): ?>
                                    <li><?php echo htmlspecialchars($log); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($resultados['falha'])): ?>
                        <div class="alert alert-danger">
                            <h5 class="alert-heading">Falhas na Vinculação (<?php echo count($resultados['falha']); ?>)</h5>
                            <ul class="mb-0 small">
                                <?php foreach ($resultados['falha'] as $log): ?>
                                    <li><?php echo htmlspecialchars($log); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('form-sincronizar').addEventListener('submit', function(e) {
    var files = document.getElementById('arquivos').files;
    if (files.length > 10) {
        alert('Você só pode selecionar até 10 arquivos por vez.');
        e.preventDefault();
    }
});
</script>