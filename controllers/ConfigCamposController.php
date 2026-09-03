<?php
require_once dirname(__FILE__) . '/../models/ConfigCamposModel.php';

class ConfigCamposController {
    private $model;

    public function __construct($conexao) {
        $this->model = new ConfigCamposModel($conexao);
    }

    public function gerenciarConfiguracao() {
        $idLocal = isset($_GET['id_local']) ? intval($_GET['id_local']) : 0;
        $idCategoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;
        if ($this->isRq()) {
            $idLocal = $this->getLocalRq();
            if ($idCategoria > 0 && !$this->model->categoriaDisponivelParaLocal($idCategoria, $idLocal)) {
                $idCategoria = 0;
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['acao']) && $_POST['acao'] === 'excluir_config') {
                $this->excluirConfiguracao($_POST);
            }
            if (isset($_POST['acao']) && $_POST['acao'] === 'categoria') {
                $this->criarCategoria($_POST);
            }
            if (isset($_POST['acao']) && $_POST['acao'] === 'editar_categoria') {
                $this->editarCategoria($_POST);
            }
            if (isset($_POST['acao']) && $_POST['acao'] === 'excluir_categoria') {
                $this->excluirCategoria($_POST);
            }
            $idLocal = isset($_POST['id_local']) ? intval($_POST['id_local']) : 0;
            $idCategoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
            if ($this->isRq()) {
                $idLocal = $this->getLocalRq();
                if ($idCategoria <= 0 || !$this->model->categoriaDisponivelParaLocal($idCategoria, $idLocal)) {
                    header('Location: index.php?modulo=configurar_campos&erro=categoria_acesso');
                    exit();
                }
            }
            if ($idLocal > 0 && $idCategoria > 0 && isset($_POST['campos'])) {
                $this->salvarCampos($idLocal, $idCategoria, $_POST['campos']);
            }
            header('Location: index.php?modulo=configurar_campos&erro=dados_invalidos');
            exit();
        }
        $localSelecionado = $this->isRq() ? $this->getLocalRq() : $idLocal;
        $categoriasComConfiguracao = $localSelecionado > 0 ? $this->model->listarCategoriasComConfiguracao($localSelecionado) : array();
        $camposSalvosPorCategoria = array();
        foreach ($categoriasComConfiguracao as $categoriaConfig) {
            $camposSalvosPorCategoria[$categoriaConfig['id_categoria']] = $this->model->listarCamposSalvos($localSelecionado, $categoriaConfig['id_categoria']);
        }
        return array('listaUnidades' => $this->model->listarUnidades($this->isRq() ? $this->getLocalRq() : null), 'listaCategorias' => $this->model->listarCategorias($this->isRq() ? $this->getLocalRq() : null),
            'configsAtuais' => ($idLocal > 0 && $idCategoria > 0) ? $this->model->getConfigCampos($idLocal, $idCategoria) : array(),
            'categoriasDaUnidade' => $localSelecionado > 0 ? $this->model->listarCategoriasDaUnidade($localSelecionado) : array(),
            'categoriasComConfiguracao' => $categoriasComConfiguracao,
            'camposSalvosPorCategoria' => $camposSalvosPorCategoria,
            'todasConfiguracoes' => $this->model->listarTodasConfiguracoes($this->isRq() ? $this->getLocalRq() : null),
            'idLocalSelecionado' => $idLocal, 'idCategoriaSelecionada' => $idCategoria);
    }

    private function criarCategoria($dados) {
        $idLocal = $this->isRq() ? $this->getLocalRq() : (isset($dados['id_local']) ? intval($dados['id_local']) : 0);
        if (empty($dados['nome_categoria']) || empty($dados['sigla_categoria']) || $idLocal <= 0) {
            header('Location: index.php?modulo=configurar_campos&erro=categoria_invalida');
            exit();
        }
        $sucesso = $this->model->criarCategoria($dados['nome_categoria'], $dados['sigla_categoria'], $idLocal);
        header('Location: index.php?modulo=configurar_campos&' . ($sucesso ? 'sucesso=categoria' : 'erro=categoria'));
        exit();
    }

    private function salvarCampos($idLocal, $idCategoria, $campos) {
        if ($this->isRq()) {
            $idLocal = $this->getLocalRq();
        }
        $sucesso = $this->model->salvarConfiguracao($idLocal, $idCategoria, $campos);
        header('Location: index.php?modulo=configurar_campos' . ($sucesso ? '&sucesso=1' : '&erro=campos'));
        exit();
    }

    private function editarCategoria($dados) {
        $id = isset($dados['id_categoria']) ? intval($dados['id_categoria']) : 0;
        $local = $this->getLocalRq();
        if ($id <= 0 || !$this->isRq() || empty($dados['nome_categoria']) || empty($dados['sigla_categoria']) || !$this->model->getCategoriaDaUnidade($id, $local)) {
            header('Location: index.php?modulo=configurar_campos&erro=categoria_acesso'); exit();
        }
        $ok = $this->model->atualizarCategoriaDaUnidade($id, $local, $dados['nome_categoria'], $dados['sigla_categoria']);
        header('Location: index.php?modulo=configurar_campos&' . ($ok ? 'sucesso=categoria' : 'erro=categoria')); exit();
    }

    private function excluirCategoria($dados) {
        $id = isset($dados['id_categoria']) ? intval($dados['id_categoria']) : 0;
        $local = $this->getLocalRq();
        if ($id <= 0 || !$this->isRq() || !$this->model->getCategoriaDaUnidade($id, $local)) {
            header('Location: index.php?modulo=configurar_campos&erro=categoria_acesso'); exit();
        }
        $ok = $this->model->excluirCategoriaDaUnidade($id, $local);
        header('Location: index.php?modulo=configurar_campos&' . ($ok ? 'sucesso=categoria' : 'erro=categoria')); exit();
    }

    private function excluirConfiguracao($dados) {
        $idLocal = isset($dados['id_local']) ? intval($dados['id_local']) : 0;
        $idCategoria = isset($dados['id_categoria']) ? intval($dados['id_categoria']) : 0;
        if ($this->isRq()) {
            $idLocal = $this->getLocalRq();
        }
        if ($idLocal <= 0 || $idCategoria <= 0 || ($this->isRq() && !$this->model->categoriaDisponivelParaLocal($idCategoria, $idLocal))) {
            header('Location: index.php?modulo=configurar_campos&erro=dados_invalidos');
            exit();
        }
        $ok = $this->model->excluirConfiguracao($idLocal, $idCategoria);
        header('Location: index.php?modulo=configurar_campos&' . ($ok ? 'sucesso=config' : 'erro=config'));
        exit();
    }

    private function isRq() {
        return isset($_SESSION['id_perfil']) && (int)$_SESSION['id_perfil'] === 2;
    }

    private function getLocalRq() {
        return isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : 0;
    }
}
?>