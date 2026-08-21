<?php
require_once dirname(__FILE__) . '/../models/UnidadesModel.php';

class UnidadesController {
    private $model;

    public function __construct($conexao) {
        $this->model = new UnidadesModel($conexao);
    }

    /**
     * Gerencia a listagem de unidades com seus documentos.
     */
    public function listarUnidadesComDocumentos() {
        return $this->model->getUnidadesComDocumentos();
    }

    /**
     * Gerencia a tela de configuração de campos.
     */
    public function gerenciarConfiguracaoCampos() {
        // Salva os dados do formulário de configuração
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvarConfiguracaoCampos($_POST);
            // O método salvarConfiguracaoCampos já redireciona
        }

        // Retorna os dados necessários para a view
        return array(
            'listaUnidades' => $this->model->getUnidadesPermitidas(), // Reutiliza a lista de 8 unidades
            'listaCategorias' => $this->model->getCategoriasConfiguraveis()
        );
    }

    /**
     * Salva as configurações de campos enviadas pelo formulário.
     */
    public function salvarConfiguracaoCampos($postData) {
        $id_local = isset($postData['id_local']) ? intval($postData['id_local']) : 0;
        $id_categoria = isset($postData['id_categoria']) ? intval($postData['id_categoria']) : 0;

        if ($id_local > 0 && $id_categoria > 0) {
            $sucesso = $this->model->salvarCampos($id_local, $id_categoria, $postData);
            if ($sucesso) {
                // Redireciona para a mesma página com parâmetros para manter a seleção
                header('Location: index.php?modulo=unidades_configurar_campos&sucesso=1&id_local='.$id_local.'&id_categoria='.$id_categoria);
            } else {
                header('Location: index.php?modulo=unidades_configurar_campos&erro=1&id_local='.$id_local.'&id_categoria='.$id_categoria);
            }
        } else {
            header('Location: index.php?modulo=unidades_configurar_campos&erro=dados_invalidos');
        }
        exit();
    }
}
?>