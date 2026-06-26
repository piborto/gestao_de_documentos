<?php
// controllers/DocumentosController.php

require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/../models/DocumentosModel.php';

class DocumentosController {
    private $model;

    public function __construct($conexao) {
        // Inicializa o Model repassando a conexão PDO
        $this->model = new DocumentosModel($conexao);
    }

    /**
     * Gerencia a listagem trazendo os filtros aplicados na tela
     */
    public function gerenciarListagem() {
        // Captura os filtros tratando a falta deles (Padrão PHP 5.2)
        $id_status    = isset($_GET['status']) ? intval($_GET['status']) : 1; // 1 = Ativos por padrão
        $id_categoria = (isset($_GET['categoria']) && $_GET['categoria'] !== '') ? intval($_GET['categoria']) : null;
        $busca        = (isset($_GET['busca']) && $_GET['busca'] !== '') ? trim($_GET['busca']) : null;

        // Executa a busca no Model com os parâmetros parametrizados
        return $this->model->listarDocumentos($id_status, $id_categoria, $busca);
    }

    /**
     * Exibe o formulário de cadastro/edição
     */
    public function exibirFormulario() {
        // Busca a lista de categorias para popular o <select> no formulário
        return array(
            'listaCategorias' => $this->model->listarCategorias(),
            'listaLocais' => $this->model->listarLocais()
        );
    }
}

// Instancia o controller para uso na View
$documentosCtrl = new DocumentosController($conexao);
$listaDocumentos = $documentosCtrl->gerenciarListagem();
$formData = $documentosCtrl->exibirFormulario();
$listaCategorias = $formData['listaCategorias'];
$listaLocais = $formData['listaLocais'];