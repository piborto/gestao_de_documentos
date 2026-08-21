<?php
// views/documentos/documentos_tabela_ajax.php
// Este arquivo é um endpoint dedicado para chamadas AJAX.
// Ele não deve renderizar o layout completo da página, apenas a tabela.

require_once dirname(__FILE__) . '/../../config/conexao.php';
require_once dirname(__FILE__) . '/../../models/DocumentosModel.php';

// Inicia a sessão para obter os dados do usuário, se não estiver iniciada
if (session_id() == "") {
    session_start();
}

// --- Lógica de busca (semelhante ao DocumentosController) ---
$model = new DocumentosModel($conexao);

$filtro_status    = isset($_GET['status']) ? intval($_GET['status']) : 1;
$filtro_categoria = (isset($_GET['categoria']) && $_GET['categoria'] !== '') ? intval($_GET['categoria']) : null;
$filtro_busca     = isset($_GET['busca']) ? trim($_GET['busca']) : null;
$filtro_distribuicao = (isset($_GET['distribuicao']) && $_GET['distribuicao'] !== '') ? intval($_GET['distribuicao']) : null;

// Captura o perfil e local do usuário logado para aplicar as regras de visibilidade
$id_perfil_usuario = isset($_SESSION['id_perfil']) ? intval($_SESSION['id_perfil']) : null;
$id_local_usuario = isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : null;

$ids_para_busca = array();
if ($filtro_categoria !== null) {
    if ($model->isParentCategory($filtro_categoria)) {
        $ids_para_busca = $model->getChildCategoryIds($filtro_categoria);
    } else {
        $ids_para_busca[] = $filtro_categoria;
    }
}

$listaDocumentos = $model->listarDocumentos($filtro_status, $ids_para_busca, $filtro_busca, $filtro_distribuicao, $id_perfil_usuario, $id_local_usuario);

?>
<div class="table-responsive">
    <table class="table table-hover table-striped align-middle">
        <thead class="thead-custom">
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 23%;">Nome</th>
                <th class="text-center" style="width: 1%;"><i class="bi bi-paperclip"></i></th>
                <?php if ($filtro_status == 3): ?>
                    <th class="text-center" style="width: 5%;">Categoria</th>
                    <th style="width: 15%;">Responsável</th>
                    <th style="width: 10%;">Data Obsoleto</th>
                    <th style="width: 29%;">Distribuição (Histórico)</th>
                <?php else: ?>
                    <th class="text-center" style="width: 5%;">Categoria</th>
                    <th style="width: 12%;">Autor</th>
                    <th class="text-center" style="width: 5%;">Revisão</th>
                    <th style="width: 8%;">Vigor</th>
                    <th style="width: 8%;">Análise</th>
                    <th style="width: 23%;">Distribuição</th>
                <?php endif; ?>
                <th class="text-end pe-3" style="width: 4%;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listaDocumentos)): ?>
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">Nenhum documento encontrado com os filtros atuais.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listaDocumentos as $doc): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($doc['codigo_documento']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($doc['nome_documento']); ?>
                            <?php if (isset($doc['controle_documento']) && ($doc['controle_documento'] !== null) && in_array($doc['sigla_categoria'], array('MQ', 'MS'))): ?>
                                <span class="badge bg-<?php echo $doc['controle_documento'] == 1 ? 'primary' : 'secondary'; ?> ms-2">
                                    <?php echo $doc['controle_documento'] == 1 ? 'Controlado' : 'Não Controlado'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($doc['arquivo_documento'])): ?><i class="bi bi-file-earmark-text text-secondary"></i><?php endif; ?>
                        </td>
                        <?php if ($filtro_status == 3): ?>
                            <td class="text-center"><span class="badge bg-white text-dark border"><?php echo htmlspecialchars($doc['sigla_categoria']); ?></span></td>
                            <td><small class="text-muted fst-italic"><?php echo htmlspecialchars(isset($doc['responsavel_obsoleto']) ? $doc['responsavel_obsoleto'] : 'Não informado'); ?></small></td>
                            <td><?php echo !empty($doc['data_obsoleto']) ? date('d/m/Y', strtotime($doc['data_obsoleto'])) : '-'; ?></td>
                        <?php else: ?>
                            <td class="text-center"><span class="badge bg-white text-dark border"><?php echo htmlspecialchars($doc['sigla_categoria']); ?></span></td>
                            <td><small><?php echo htmlspecialchars($doc['autor_documento'] ? $doc['autor_documento'] : '-'); ?></small></td>
                            <td class="text-center"><?php echo htmlspecialchars($doc['revisao_documento'] !== '' ? $doc['revisao_documento'] : '-'); ?></td>
                            <td><?php echo $doc['data_vigor_documento'] ? date('d/m/Y', strtotime($doc['data_vigor_documento'])) : '-'; ?></td>
                            <td><?php echo (!empty($doc['data_analise_documento']) && $doc['data_analise_documento'] != '0000-00-00') ? date('d/m/Y', strtotime($doc['data_analise_documento'])) : '-'; ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if (!empty($doc['locais_distribuicao']) && $doc['locais_distribuicao'] !== '-'): ?>
                                <?php $locais = explode(', ', $doc['locais_distribuicao']); foreach ($locais as $local): ?>
                                    <span class="badge bg-info text-dark me-1" style="font-size: 0.7em;"><?php echo htmlspecialchars(trim($local)); ?></span>
                                <?php endforeach; ?>
                            <?php else: echo '-'; endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <?php if (!empty($doc['arquivo_documento'])): ?>
                                    <a href="uploads/documentos/<?php echo htmlspecialchars($doc['sigla_categoria']); ?>/<?php echo urlencode($doc['arquivo_documento']); ?>" target="_blank" class="btn btn-outline-secondary" title="Visualizar"><i class="bi bi-eye"></i></a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary disabled" style="opacity: 0.3;"><i class="bi bi-eye-slash"></i></button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-info" title="Histórico" data-bs-toggle="modal" data-bs-target="#modalHistorico" data-id="<?php echo $doc['id_documento']; ?>"><i class="bi bi-clock-history"></i></button>
                                <?php if ($filtro_status == 3): ?>
                                    <button type="button" class="btn btn-outline-success" title="Restaurar Documento" data-bs-toggle="modal" data-bs-target="#modalRestaurar" data-id="<?php echo $doc['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($doc['nome_documento']); ?>"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir Permanentemente" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $doc['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($doc['nome_documento']); ?>"><i class="bi bi-trash"></i></button>
                                <?php else: ?>
                                    <?php $query_params_edit = http_build_query(array_filter(array('status' => $filtro_status, 'categoria' => $filtro_categoria, 'distribuicao' => $filtro_distribuicao, 'busca' => $filtro_busca))); ?>
                                    <a href="index.php?modulo=documentos_editar&id=<?php echo $doc['id_documento']; ?>&<?php echo $query_params_edit; ?>" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <button type="button" class="btn btn-outline-warning" title="Tornar Obsoleto" data-bs-toggle="modal" data-bs-target="#modalObsoleto" data-id="<?php echo $doc['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($doc['nome_documento']); ?>"><i class="bi bi-archive"></i></button>
                                    <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $doc['id_documento']; ?>" data-nome="<?php echo htmlspecialchars($doc['nome_documento']); ?>"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>