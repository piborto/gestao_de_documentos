<?php
require_once dirname(__FILE__) . '/../models/SiglasModel.php';
require_once dirname(__FILE__) . '/../models/DocumentosModel.php'; // Para usar o log

class SiglasController {
    private $model;

    public function __construct($conexao) {
        $this->model = new SiglasModel($conexao);
    }

    private function getUsuarioId() {
        return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1; // 1 como fallback para 'Sistema'
    }

    public function gerenciarListagem() {
        $busca = isset($_GET['busca']) ? trim($_GET['busca']) : null;
        $id_status = isset($_GET['status']) ? intval($_GET['status']) : 1; // Padrão 'Em Vigor'
        return $this->model->listarSiglas($busca, $id_status);
    }

    public function exibirFormulario() {
        // No data needed for a blank form
        return array();
    }

    public function exibirFormularioEdicao($id) {
        if ($id <= 0) {
            header('Location: index.php?modulo=siglas&erro=id_invalido');
            exit();
        }
        $sigla = $this->model->getSiglaPorId($id);
        if (!$sigla) {
            header('Location: index.php?modulo=siglas&erro=nao_encontrado');
            exit();
        }
        return array('sigla' => $sigla);
    }

    public function salvarNovaSigla($postData) {
        $hoje = date('Y-m-d');
        $data_sigla = $postData['data_sigla'];

        if ($data_sigla > $hoje) {
            // Agendado
            $postData['id_status'] = 2; // 2 = Agendado
        } else {
            // Em Vigor
            $postData['id_status'] = 1; // 1 = Em Vigor
        }

        $sucesso = $this->model->salvarSigla($postData);

        if ($sucesso) {
            // Reordena e renumera todas as siglas
            $this->model->renumerarSiglas();
            header('Location: index.php?modulo=siglas&sucesso=cadastro');
        } else {
            header('Location: index.php?modulo=siglas_cadastrar&erro=falha_db');
        }
        exit();
    }

    public function atualizarSigla($postData) {
        $id_sigla = isset($postData['id_sigla']) ? intval($postData['id_sigla']) : 0;

        if ($id_sigla <= 0) {
            header('Location: index.php?modulo=siglas&erro=dados_invalidos');
            exit();
        }

        $hoje = date('Y-m-d');
        $data_sigla = $postData['data_sigla'];

        if ($data_sigla > $hoje) {
            $postData['id_status'] = 2; // Agendado
        } else {
            $postData['id_status'] = 1; // Em Vigor
        }

        $sucesso = $this->model->atualizarSigla($id_sigla, $postData);

        if ($sucesso) {
            $this->model->renumerarSiglas();
            header('Location: index.php?modulo=siglas&sucesso=edicao');
        } else {
            header('Location: index.php?modulo=siglas_editar&id=' . $id_sigla . '&erro=falha_db');
        }
        exit();
    }

    public function excluirSigla() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_sigla = isset($_POST['id_sigla']) ? intval($_POST['id_sigla']) : 0;
            if ($id_sigla > 0) {
                $sucesso = $this->model->excluirSigla($id_sigla);
                if ($sucesso) {
                    $this->model->renumerarSiglas();
                    header('Location: index.php?modulo=siglas&sucesso=excluido');
                } else {
                    header('Location: index.php?modulo=siglas&erro=excluir');
                }
            } else {
                header('Location: index.php?modulo=siglas&erro=dados_invalidos');
            }
            exit();
        }
    }

    public function importarCSV($fileData) {
        if (!isset($fileData['csv_file']) || $fileData['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['import_status'] = array('tipo' => 'danger', 'mensagem' => 'Erro no upload do arquivo.');
            header('Location: index.php?modulo=siglas_importar');
            exit();
        }

        $caminho_arquivo = $fileData['csv_file']['tmp_name'];
        $contador = array('sucesso' => 0, 'falha' => 0, 'existente' => 0);
        $erros = array();

        if (($handle = fopen($caminho_arquivo, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ";"); // Pula o cabeçalho

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (strpos($data[0], "\xEF\xBB\xBF") === 0) {
                    $data[0] = substr($data[0], 3);
                }

                $dados_linha = array(
                    'status' => trim($data[0]),
                    'nome_sigla' => trim($data[1]),
                    'definicao_sigla' => trim($data[2]),
                    'referencia_sigla' => trim($data[3]),
                    'data_sigla' => trim($data[4]),
                );

                if (empty($dados_linha['nome_sigla'])) continue;

                if ($this->model->siglaExiste($dados_linha['nome_sigla'])) {
                    $contador['existente']++;
                } else {
                    try {
                        $dados_linha['id_status'] = ($dados_linha['status'] === 'ATIVO') ? 1 : 3; // 1=Em Vigor, 3=Obsoleto
                        $id_nova_sigla = $this->model->salvarSiglaImportada($dados_linha);

                        if ($id_nova_sigla) {
                            $contador['sucesso']++;
                            if ($dados_linha['id_status'] === 3) {
                                $docsModel = new DocumentosModel($this->model->getDb());
                                $justificativa = "Sigla importada como obsoleta. Descrição original: " . (isset($data[9]) ? $data[9] : 'N/A');
                                $docsModel->logHistorico("Importação de Sigla Obsoleta", $justificativa, $this->getUsuarioId(), null, $id_nova_sigla);
                            }
                        } else {
                            throw new Exception("Falha ao salvar no banco.");
                        }
                    } catch (Exception $e) {
                        $contador['falha']++;
                        $erros[] = "Linha com sigla '{$dados_linha['nome_sigla']}': " . $e->getMessage();
                    }
                }
            }
            fclose($handle);
            $this->model->renumerarSiglas();
        }

        $mensagem_feedback = "Importação concluída!\n\n- Siglas inseridas: {$contador['sucesso']}\n- Siglas já existentes (ignoradas): {$contador['existente']}\n- Falhas: {$contador['falha']}";
        if (!empty($erros)) {
            $mensagem_feedback .= "\n\nDetalhes das falhas:\n" . implode("\n", $erros);
        }

        $_SESSION['import_status'] = array('tipo' => 'info', 'mensagem' => $mensagem_feedback);
        header('Location: index.php?modulo=siglas_importar');
        exit();
    }

    public function atualizarPDFMestre($fileData) {
        $pasta_upload = dirname(__FILE__) . '/../uploads/siglas/';
        $pasta_historico = dirname(__FILE__) . '/../uploads/historico/';
        $nome_fixo = 'Siglas_e_Definicoes.pdf';
        $caminho_final = $pasta_upload . $nome_fixo;

        if (isset($fileData['pdf_file']) && $fileData['pdf_file']['error'] === UPLOAD_ERR_OK) {
            if (file_exists($caminho_final)) {
                $nome_backup = "Siglas_e_Definicoes_" . date('Ymd_His') . ".pdf";
                $caminho_backup = $pasta_historico . $nome_backup;
                rename($caminho_final, $caminho_backup);
            }

            if (move_uploaded_file($fileData['pdf_file']['tmp_name'], $caminho_final)) {
                return array('tipo' => 'success', 'mensagem' => 'Arquivo PDF mestre de siglas foi atualizado com sucesso!');
            }
        }
        return array('tipo' => 'danger', 'mensagem' => 'Falha ao atualizar o arquivo PDF.');
    }
}
?>