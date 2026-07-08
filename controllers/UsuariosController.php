<?php
require_once dirname(__FILE__) . '/../models/UsuariosModel.php';
require_once dirname(__FILE__) . '/../models/DocumentosModel.php'; // Para usar o log

class UsuariosController {
    private $model;

    public function __construct($conexao) {
        $this->model = new UsuariosModel($conexao);
    }

    /**
     * Gerencia a listagem de usuários.
     */
    public function gerenciarListagem() {
        return $this->model->listarUsuarios();
    }

    private function getUsuarioId() {
        return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1; // 1 como fallback para 'Sistema'
    }

    public function exibirFormulario() {
        return array(
            'listaPerfis' => $this->model->listarPerfis(),
            'listaLocais' => $this->model->listarLocais()
        );
    }

    public function exibirFormularioEdicao($id) {
        if ($id <= 0) {
            header('Location: index.php?modulo=usuarios&erro=id_invalido');
            exit();
        }
        $usuario = $this->model->getUsuarioPorId($id);
        if (!$usuario) {
            header('Location: index.php?modulo=usuarios&erro=nao_encontrado');
            exit();
        }
        $formData = $this->exibirFormulario();
        $formData['usuario'] = $usuario;
        return $formData;
    }

    public function salvarNovoUsuario($postData) {
        $sucesso = $this->model->salvarUsuario($postData);
        if ($sucesso) {
            header('Location: index.php?modulo=usuarios&sucesso=cadastro');
        } else {
            header('Location: index.php?modulo=usuarios_cadastrar&erro=falha_db');
        }
        exit();
    }

    public function atualizarUsuario($postData) {
        $id_usuario = isset($postData['id_usuario']) ? intval($postData['id_usuario']) : 0;
        $justificativa = isset($postData['justificativa']) ? trim($postData['justificativa']) : '';

        if ($id_usuario <= 0) { // A justificativa é obrigatória apenas no formulário de edição
            header('Location: index.php?modulo=usuarios&erro=dados_invalidos');
            exit();
        }

        $sucesso = $this->model->atualizarUsuario($id_usuario, $postData);

        if ($sucesso) {
            $docsModel = new DocumentosModel($this->model->getDb());
            $docsModel->logHistorico("Usuário Editado", $justificativa, $this->getUsuarioId(), null, null);
            header('Location: index.php?modulo=usuarios&sucesso=edicao');
        } else {
            header('Location: index.php?modulo=usuarios_editar&id=' . $id_usuario . '&erro=falha_db');
        }
        exit();
    }

    public function alterarStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
            $novo_status = isset($_POST['status']) ? intval($_POST['status']) : -1;
            $justificativa = isset($_POST['justificativa']) ? trim($_POST['justificativa']) : '';

            if ($id_usuario > 0 && ($novo_status === 0 || $novo_status === 1)) {
                $sucesso = $this->model->alterarStatusUsuario($id_usuario, $novo_status);
                if ($sucesso) {
                    if ($novo_status === 0 && !empty($justificativa)) { // Log apenas na desativação
                        $docsModel = new DocumentosModel($this->model->getDb());
                        $docsModel->logHistorico("Usuário Desativado", $justificativa, $this->getUsuarioId(), null, null);
                    } elseif ($novo_status === 1) {
                        $docsModel = new DocumentosModel($this->model->getDb());
                        $docsModel->logHistorico("Usuário Reativado", "Usuário reativado no sistema.", $this->getUsuarioId(), null, null);
                    }
                    header('Location: index.php?modulo=usuarios&sucesso=status');
                } else {
                    header('Location: index.php?modulo=usuarios&erro=status');
                }
            } else {
                header('Location: index.php?modulo=usuarios&erro=dados_invalidos');
            }
            exit();
        }
    }

    public function excluirUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
            if ($id_usuario > 0) {
                $sucesso = $this->model->excluirUsuario($id_usuario);
                if ($sucesso) {
                    header('Location: index.php?modulo=usuarios&sucesso=excluido');
                } else {
                    header('Location: index.php?modulo=usuarios&erro=excluir');
                }
            } else {
                header('Location: index.php?modulo=usuarios&erro=dados_invalidos');
            }
            exit();
        }
    }
}
?>
