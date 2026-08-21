<?php
require_once dirname(__FILE__) . '/../../config/conexao.php';
require_once dirname(__FILE__) . '/../../models/UnidadesModel.php';

$id_local = isset($_GET['id_local']) ? intval($_GET['id_local']) : 0;
$id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;

if ($id_local > 0 && $id_categoria > 0) {
    $model = new UnidadesModel($conexao);
    $campos = $model->getCamposConfigurados($id_local, $id_categoria);
} else {
    $campos = array();
}
?>

<?php if (empty($campos)): ?>
    <div class="alert alert-warning">Nenhum campo padrão encontrado para esta categoria.</div>
<?php else: ?>
    <p class="text-muted small">Arraste para reordenar. Marque as caixas para definir a visibilidade e obrigatoriedade de cada campo.</p>
    <table class="table table-sm table-borderless">
        <thead class="text-muted small">
            <tr>
                <th style="width: 5%;">Ordem</th>
                <th>Rótulo do Campo</th>
                <th class="text-center" style="width: 10%;">Visível</th>
                <th class="text-center" style="width: 10%;">Obrigatório</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($campos as $campo): ?>
                <tr>
                    <td class="align-middle text-center text-muted"><i class="bi bi-grip-vertical"></i></td>
                    <td>
                        <input type="hidden" name="campos[<?php echo $campo['nome_campo_interno']; ?>][ordem]" value="<?php echo $campo['ordem']; ?>">
                        <input type="text" class="form-control form-control-sm" name="campos[<?php echo $campo['nome_campo_interno']; ?>][rotulo]" value="<?php echo htmlspecialchars(utf8_encode($campo['rotulo_personalizado'])); ?>">
                    </td>
                    <td class="text-center align-middle">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="campos[<?php echo $campo['nome_campo_interno']; ?>][visivel]" <?php echo $campo['visivel'] ? 'checked' : ''; ?>>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" role="switch" name="campos[<?php echo $campo['nome_campo_interno']; ?>][obrigatorio]" <?php echo $campo['obrigatorio'] ? 'checked' : ''; ?>>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
