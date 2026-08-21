<?php
require_once dirname(__FILE__) . '/../models/CategoriasModel.php';

class CategoriasController {
    private $model;

    public function __construct($conexao) {
        $this->model = new CategoriasModel($conexao);
    }

    public function gerenciarListagem() {
        return $this->model->listarCategorias();
    }

    public function exibirFormularioEdicao($id) {
        if ($id <= 0) {
            header('Location: index.php?modulo=categorias&erro=id_invalido');
            exit();
        }
        $categoria = $this->model->getCategoriaPorId($id);
        if (!$categoria) {
            header('Location: index.php?modulo=categorias&erro=nao_encontrado');
            exit();
        }
        return $categoria;
    }

    public function salvarNovaCategoria($postData) {
        $sucesso = $this->model->salvarCategoria($postData);
        if ($sucesso) {
            header('Location: index.php?modulo=categorias&sucesso=cadastro');
        } else {
            header('Location: index.php?modulo=categorias_cadastrar&erro=falha_db');
        }
        exit();
    }

    public function atualizarCategoria($postData) {
        $id_categoria = isset($postData['id_categoria']) ? intval($postData['id_categoria']) : 0;

        if ($id_categoria <= 0) {
            header('Location: index.php?modulo=categorias&erro=dados_invalidos');
            exit();
        }

        $sucesso = $this->model->atualizarCategoria($id_categoria, $postData);

        if ($sucesso) {
            header('Location: index.php?modulo=categorias&sucesso=edicao');
        } else {
            header('Location: index.php?modulo=categorias_editar&id=' . $id_categoria . '&erro=falha_db');
        }
        exit();
    }
}
?>