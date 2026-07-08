<?php
require_once dirname(__FILE__) . '/../../config/conexao.php';
require_once dirname(__FILE__) . '/../../models/SiglasModel.php';

$model = new SiglasModel($conexao);

$filtro_busca = isset($_GET['busca']) ? trim($_GET['busca']) : null;
$filtro_status = isset($_GET['status']) ? intval($_GET['status']) : 1;
$filtro_data_de = isset($_GET['data_de']) && !empty($_GET['data_de']) ? $_GET['data_de'] : null;
$filtro_data_ate = isset($_GET['data_ate']) && !empty($_GET['data_ate']) ? $_GET['data_ate'] : null;

$listaSiglas = $model->listarSiglas($filtro_busca, $filtro_status, $filtro_data_de, $filtro_data_ate);
?>

<div class="table-responsive">
    <table class="table table-hover table-striped align-middle">
        <thead class="thead-custom">
            <tr>
                <th style="width: 20%;">Sigla</th>
                <th style="width: 45%;">Definição</th>
                <th style="width: 20%;">Referência</th>
                <th style="width: 5%;">Data</th>
                <th class="text-end pe-3" style="width: 10%;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listaSiglas)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">Nenhuma sigla encontrada com os filtros atuais.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listaSiglas as $sigla): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($sigla['nome_sigla']); ?></td>
                        <td><?php echo htmlspecialchars($sigla['definicao_sigla']); ?></td>
                        <td><small><?php echo htmlspecialchars($sigla['referencia_sigla']); ?></small></td>
                        <td><?php echo date('d/m/Y', strtotime($sigla['data_sigla'])); ?></td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info" title="Histórico" data-bs-toggle="modal" data-bs-target="#modalHistoricoSigla" data-id="<?php echo $sigla['id_sigla']; ?>"><i class="bi bi-clock-history"></i></button>
                                <?php if ($filtro_status == 3): // Ações para Obsoletos ?>
                                    <button type="button" class="btn btn-outline-success" title="Restaurar Sigla" data-bs-toggle="modal" data-bs-target="#modalRestaurarSigla" data-id="<?php echo $sigla['id_sigla']; ?>" data-nome="<?php echo htmlspecialchars($sigla['nome_sigla']); ?>"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir Permanentemente" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $sigla['id_sigla']; ?>" data-nome="<?php echo htmlspecialchars($sigla['nome_sigla']); ?>"><i class="bi bi-trash"></i></button>
                                <?php else: // Ações para Ativos/Agendados ?>
                                    <?php
                                    $query_params = http_build_query(array_filter(array('status' => $filtro_status, 'busca' => $filtro_busca, 'data_de' => $filtro_data_de, 'data_ate' => $filtro_data_ate)));
                                    $edit_link = "index.php?modulo=siglas_editar&id=" . $sigla['id_sigla'] . "&" . $query_params;
                                    ?>
                                    <a href="<?php echo $edit_link; ?>" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-outline-warning" title="Tornar Obsoleto" data-bs-toggle="modal" data-bs-target="#modalObsoleto" data-id="<?php echo $sigla['id_sigla']; ?>" data-nome="<?php echo htmlspecialchars($sigla['nome_sigla']); ?>"><i class="bi bi-archive"></i></button>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $sigla['id_sigla']; ?>" data-nome="<?php echo htmlspecialchars($sigla['nome_sigla']); ?>"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>