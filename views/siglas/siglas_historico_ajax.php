<?php
require_once dirname(__FILE__) . '/../../config/conexao.php';
require_once dirname(__FILE__) . '/../../models/SiglasModel.php';

$id_sigla = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_sigla > 0) {
    $model = new SiglasModel($conexao);
    $historico = $model->listarHistoricoPorSigla($id_sigla);

    if (empty($historico)) {
        echo '<div class="alert alert-light text-center">Nenhum registro de histórico encontrado para esta sigla.</div>';
    } else {
        echo '<ul class="list-group list-group-flush">';
        foreach ($historico as $item) {
?>
            <li class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 fw-bold text-primary"><?php echo htmlspecialchars($item['acao_historico']); ?></h6>
                    <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($item['data_historico'])); ?></small>
                </div>
                <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($item['justificativa_historico'])); ?></p>
                <small class="text-muted fst-italic">Por: <?php echo htmlspecialchars($item['nome_usuario']); ?></small>
            </li>
<?php
        }
        echo '</ul>';
    }
} else {
    echo '<div class="alert alert-danger">ID da sigla inválido.</div>';
}
?>