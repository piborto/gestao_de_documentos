<?php
// views/documentos/documentos_historico_ajax.php

// Este arquivo é um endpoint dedicado para chamadas AJAX.
// Ele não deve renderizar o layout completo da página.

require_once dirname(__FILE__) . '/../../config/conexao.php';
require_once dirname(__FILE__) . '/../../models/DocumentosModel.php';

$id_documento = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_documento > 0) {
    $model = new DocumentosModel($conexao);
    $historico = $model->listarHistoricoPorDocumento($id_documento);

    if (empty($historico)) {
        echo '<p class="text-center text-muted p-4">Nenhum registro de histórico para este documento.</p>';
    } else {
        echo '<ul class="list-group list-group-flush">';
        foreach ($historico as $item) {
            echo '<li class="list-group-item">';
            echo '<div class="d-flex w-100 justify-content-between">';
            echo '<h6 class="mb-1">' . htmlspecialchars($item['acao_historico']) . '</h6>';
            echo '<small class="text-muted">' . date('d/m/Y H:i', strtotime($item['data_historico'])) . '</small>';
            echo '</div>';
            echo '<p class="mb-1 small">' . nl2br(htmlspecialchars($item['justificativa_historico'])) . '</p>';
            echo '<small class="text-muted">Por: ' . htmlspecialchars($item['nome_usuario']) . '</small>';
            echo '</li>';
        }
        echo '</ul>';
    }
} else {
    echo '<p class="text-center text-danger p-4">ID de documento inválido.</p>';
}
exit(); // Garante que nada mais seja executado.