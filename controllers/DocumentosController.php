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

    public function exibirFormularioEdicao($id_documento) {
        if ($id_documento <= 0) {
            header('Location: index.php?modulo=documentos&erro=id_invalido');
            exit();
        }
        $documento = $this->model->getDocumentoPorId($id_documento);
        if (!$documento) {
            header('Location: index.php?modulo=documentos&erro=nao_encontrado');
            exit();
        }
        $formData = $this->exibirFormulario();
        $formData['documento'] = $documento;
        return $formData;
    }

    /**
     * Busca os dados para os cards de notificação do painel principal.
     */
    public function obterNotificacoes() {
        return array(
            'vencidos' => $this->model->listarDocumentosVencidos(),
            'proximos' => $this->model->listarDocumentosProximosVencimento()
        );
    }

    /**
     * Helper function to sanitize filenames, based on the old system's logic.
     * @param string $nome
     * @return string
     */
    private function limparNomeArquivo($nome) {
        $nome = preg_replace(
            array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"),
            explode(" ","a A e E i I o O u U n N c C"),
            $nome
        );
        $nome = preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $nome);
        return strtolower($nome);
    }

    /**
     * Processes the form submission for a new document, saves it, and redirects.
     */
    public function salvarNovoDocumento($postData, $fileData) {
        // --- 1. Basic Validation ---
        if (empty($postData['id_categoria']) || !isset($fileData['arquivo_documento']) || $fileData['arquivo_documento']['error'] !== UPLOAD_ERR_OK) {
            header('Location: index.php?modulo=documentos&erro=dados_invalidos');
            exit();
        }

        // --- 2. Get Category Info ---
        $categoriaInfo = $this->model->getCategoriaPorId($postData['id_categoria']);
        if (!$categoriaInfo) {
            header('Location: index.php?modulo=documentos&erro=categoria_invalida');
            exit();
        }
        $cat_sigla = $categoriaInfo['sigla_categoria'];

        // --- 3. Handle File Upload & Renaming ---
        $nome_arquivo_salvo = null;
        $pasta_upload = dirname(__FILE__) . '/../uploads/documentos/' . $cat_sigla . '/';
        if (!is_dir($pasta_upload)) {
            mkdir($pasta_upload, 0775, true);
        }

        $extensao = strtolower(pathinfo($fileData['arquivo_documento']['name'], PATHINFO_EXTENSION));
        
        // --- Renaming Logic from 'qualidade2' system ---
        $cat_sigla_check = strtoupper(trim($cat_sigla));
        
        if ($cat_sigla_check == 'LM') {
            $nome_final_padronizado = "ListaMestra." . $extensao;
        } elseif ($cat_sigla_check == 'DO') {
            $cod_do = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $postData['codigo_documento']));
            $nome_sanitizado = $this->limparNomeArquivo($postData['nome_documento']);
            $nome_final_padronizado = $cod_do . "_" . $nome_sanitizado . "." . $extensao;
        } elseif ($cat_sigla_check == 'MQ' || $cat_sigla_check == 'MS') {
            // Lógica específica para Manuais
            $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
            $revisao = isset($postData['revisao_documento']) ? $postData['revisao_documento'] : '0';
            $rev_pad = str_pad($revisao, 2, '0', STR_PAD_LEFT);
            $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
            $str_local = '';

            // Se exatamente UM local de distribuição for selecionado, adiciona ao nome do arquivo
            if (isset($postData['distribuicao']) && count($postData['distribuicao']) === 1) {
                $id_local_unico = $postData['distribuicao'][0];
                $nome_local = $this->model->getNomeLocalPorId($id_local_unico);
                $str_local = "_" . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nome_local));
            }
            $nome_final_padronizado = $cod_limpo . $str_local . "_rev" . $rev_pad . "_" . $ano_vigor . "." . $extensao;
        } else { // Default for PQ, IT, FQ, MQ, MS, etc.
            $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
            $revisao = isset($postData['revisao_documento']) ? $postData['revisao_documento'] : '0';
            $rev_pad = str_pad($revisao, 2, '0', STR_PAD_LEFT);
            $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
            $sufixo = isset($postData['sufixo']) ? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['sufixo'])) : '';
            $str_sufixo = !empty($sufixo) ? "_" . $sufixo : "";
            $nome_final_padronizado = $cod_limpo . "_rev" . $rev_pad . "_" . $ano_vigor . $str_sufixo . "." . $extensao;
        }

        $caminho_final = $pasta_upload . $nome_final_padronizado;

        if (move_uploaded_file($fileData['arquivo_documento']['tmp_name'], $caminho_final)) {
            $nome_arquivo_salvo = $nome_final_padronizado;
        } else {
            header('Location: index.php?modulo=documentos&erro=falha_upload');
            exit();
        }

        // --- 4. Prepare data and save to DB ---
        $postData['arquivo_documento'] = $nome_arquivo_salvo;
        $id_documento = $this->model->salvarDocumento($postData);

        if ($id_documento) {
            if (!empty($postData['distribuicao'])) {
                $this->model->vincularLocais($id_documento, $postData['distribuicao'], (isset($postData['numero_manual']) ? $postData['numero_manual'] : array()));
            }
            header('Location: index.php?modulo=documentos&sucesso=cadastro');
        } else {
            if (file_exists($caminho_final)) { unlink($caminho_final); }
            header('Location: index.php?modulo=documentos&erro=falha_db');
        }
        exit();
    }

    public function atualizarDocumento($postData, $fileData) {
        $id_documento = isset($postData['id_documento']) ? intval($postData['id_documento']) : 0;
        $justificativa = isset($postData['justificativa']) ? trim($postData['justificativa']) : '';
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;

        if ($id_documento <= 0 || empty($justificativa)) {
            header('Location: index.php?modulo=documentos_editar&id=' . $id_documento . '&erro=dados_invalidos');
            exit();
        }

        // Busca os dados atuais do documento para obter a sigla da categoria
        $doc_atual = $this->model->getDocumentoPorId($id_documento);
        if (!$doc_atual) {
             header('Location: index.php?modulo=documentos&erro=nao_encontrado');
             exit();
        }

        // Lógica para upload e substituição de arquivo, se um novo for enviado
        if (isset($fileData['arquivo_documento']) && $fileData['arquivo_documento']['error'] === UPLOAD_ERR_OK) {
            $pasta_upload = dirname(__FILE__) . '/../uploads/documentos/' . $doc_atual['sigla_categoria'] . '/';
            if (!is_dir($pasta_upload)) {
                mkdir($pasta_upload, 0775, true);
            }

            // Remove o arquivo antigo para evitar lixo no servidor
            if (!empty($doc_atual['arquivo_documento']) && file_exists($pasta_upload . $doc_atual['arquivo_documento'])) {
                unlink($pasta_upload . $doc_atual['arquivo_documento']);
            }

            // Salva o novo arquivo (a lógica de renomear pode ser adicionada aqui se necessário)
            $extensao = strtolower(pathinfo($fileData['arquivo_documento']['name'], PATHINFO_EXTENSION));
            $cat_sigla_check = strtoupper(trim($doc_atual['sigla_categoria']));

            // Lógica de renomeação replicada do cadastro para garantir consistência
            if ($cat_sigla_check == 'LM') {
                $nome_final_padronizado = "ListaMestra." . $extensao;
            } elseif ($cat_sigla_check == 'DO') {
                $cod_do = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $postData['codigo_documento']));
                $nome_sanitizado = $this->limparNomeArquivo($postData['nome_documento']);
                $nome_final_padronizado = $cod_do . "_" . $nome_sanitizado . "." . $extensao;
            } elseif ($cat_sigla_check == 'MQ' || $cat_sigla_check == 'MS') {
                $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
                $rev_pad = str_pad($postData['revisao_documento'], 2, '0', STR_PAD_LEFT);
                $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
                $str_local = (isset($postData['distribuicao']) && count($postData['distribuicao']) === 1) ? "_" . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $this->model->getNomeLocalPorId($postData['distribuicao'][0]))) : '';
                $nome_final_padronizado = $cod_limpo . $str_local . "_rev" . $rev_pad . "_" . $ano_vigor . "." . $extensao;
            } else { // Padrão para PQ, IT, FQ, etc.
                $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
                $rev_pad = str_pad($postData['revisao_documento'], 2, '0', STR_PAD_LEFT);
                $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
                $sufixo = isset($postData['sufixo']) ? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['sufixo'])) : '';
                $str_sufixo = !empty($sufixo) ? "_" . $sufixo : "";
                $nome_final_padronizado = $cod_limpo . "_rev" . $rev_pad . "_" . $ano_vigor . $str_sufixo . "." . $extensao;
            }

            $novo_nome_arquivo = $nome_final_padronizado;
            if (move_uploaded_file($fileData['arquivo_documento']['tmp_name'], $pasta_upload . $novo_nome_arquivo)) {
                $postData['arquivo_documento'] = $novo_nome_arquivo;
            }
        }

        $sucesso = $this->model->atualizarDocumento($id_documento, $postData);

        if ($sucesso) {
            $this->model->logHistorico("Documento Editado", $justificativa, $id_usuario, $id_documento);
            header('Location: index.php?modulo=documentos&sucesso=edicao');
        } else {
            header('Location: index.php?modulo=documentos_editar&id=' . $id_documento . '&erro=falha_db');
        }
        exit();
    }

    public function tornarObsoleto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;
            $justificativa = isset($_POST['justificativa']) ? trim($_POST['justificativa']) : '';
            $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1; // 1 como fallback para 'Sistema'

            if ($id_documento > 0 && !empty($justificativa)) {
                $sucesso = $this->model->tornarObsoleto($id_documento, $justificativa, $id_usuario);
                if ($sucesso) {
                    header('Location: index.php?modulo=documentos&status=3&sucesso=obsoleto');
                } else {
                    header('Location: index.php?modulo=documentos&erro=obsoleto');
                }
            } else {
                header('Location: index.php?modulo=documentos&erro=dados_invalidos');
            }
            exit();
        }
    }

    public function excluirDocumento() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;
            if ($id_documento > 0) {
                $this->model->excluirDocumento($id_documento);
            }
            header('Location: index.php?modulo=documentos&sucesso=excluido');
            exit();
        }
    }

    /**
     * Processa o upload de um arquivo CSV e importa os dados.
     */
    public function importarCSV($fileData) {
        if (!isset($fileData['csv_file']) || $fileData['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['import_status'] = array('tipo' => 'danger', 'mensagem' => 'Erro no upload do arquivo.');
            header('Location: index.php?modulo=documentos_importar');
            exit();
        }

        $caminho_arquivo = $fileData['csv_file']['tmp_name'];
        $contador = array('sucesso' => 0, 'falha' => 0, 'existente' => 0);
        $erros = array();
        $ignorados = array();

        if (($handle = fopen($caminho_arquivo, "r")) !== FALSE) {            
            $cabecalho = fgetcsv($handle, 2000, ";");
            $tipo_csv = $this->detectarTipoCSV($cabecalho);
            
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // Remove o BOM do início da primeira coluna, se houver
                if (strpos($data[0], "\xEF\xBB\xBF") === 0) {
                    $data[0] = substr($data[0], 3);
                }

                // Limpa aspas duplas que o fgetcsv pode deixar
                $cleaned_data = array();
                foreach ($data as $value) {
                    $cleaned_data[] = trim($value, '"');
                }

                $dados_linha = $this->mapearDadosLinha($cleaned_data, $tipo_csv);

                // Pula linhas vazias ou com código inválido
                if (empty($dados_linha['codigo']) && empty($dados_linha['nome'])) {
                    continue;
                }

                $id_documento_existente = $this->model->getDocumentoIdPorCodigoEArquivo($dados_linha['codigo'], $dados_linha['nome_arquivo']);

                if ($id_documento_existente) {
                    // Documento já existe.
                    $contador['existente']++;
                    $ignorados[] = "- [" . $dados_linha['status_nome'] . "] Codigo: " . $dados_linha['codigo'] . ", Arquivo: " . $dados_linha['nome_arquivo'];

                    // Se for um registro obsoleto de um documento que já existe, apenas registra a ação no histórico.
                    if ($dados_linha['status_nome'] === 'OBSOLETO') {
                        $justificativa = "Documento importado como obsoleto. Motivo original: " . $dados_linha['motivo_obsoleto'] . ". Responsavel original: " . $dados_linha['responsavel_obsoleto'] . ".";
                        $id_usuario_logado = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;
                        $this->model->logHistorico("Importacao de Obsoleto", $justificativa, $id_usuario_logado, $id_documento_existente);
                    }
                } else {
                    // Documento não existe, então insere.
                    try {
                        $id_categoria = $this->model->getOrCreateCategoriaPorSigla($dados_linha['categoria_sigla']);
                        $ids_locais = array();
                        if (!empty($dados_linha['locais_distribuicao'])) {
                            $nomes_locais = explode('|', $dados_linha['locais_distribuicao']);
                            foreach ($nomes_locais as $nome_local) {
                                $nome_local_tratado = trim($nome_local);
                                if (!empty($nome_local_tratado)) { $ids_locais[] = $this->model->getOrCreateLocalPorNome($nome_local_tratado); }
                            }
                        }

                        $id_status = ($dados_linha['status_nome'] === 'ATIVO') ? 1 : 3;

                        $dados_db = array(
                            'id_categoria' => $id_categoria, 'id_status' => $id_status,
                            'codigo_documento' => $dados_linha['codigo'], 'nome_documento' => $dados_linha['nome'],
                            'autor_documento' => $dados_linha['autor'], 'revisao_documento' => $dados_linha['revisao'],
                            'data_vigor_documento' => (!empty($dados_linha['data_vigor']) && $dados_linha['data_vigor'] != '0000-00-00') ? $dados_linha['data_vigor'] : null,
                            'data_analise_documento' => (!empty($dados_linha['data_analise']) && $dados_linha['data_analise'] != '0000-00-00') ? $dados_linha['data_analise'] : null,
                            'arquivo_documento' => $dados_linha['nome_arquivo'],
                            'controle_documento' => (isset($dados_linha['tipo_manual']) && $dados_linha['tipo_manual'] === 'Controlado') ? 1 : 0
                        );

                        $id_documento_novo = $this->model->salvarDocumentoImportado($dados_db);

                        if ($id_documento_novo) {
                            if (!empty($ids_locais)) { $this->model->vincularLocais($id_documento_novo, $ids_locais, array()); }
                            if ($id_status === 3) {
                                $justificativa = "Documento importado como obsoleto. Motivo original: " . $dados_linha['motivo_obsoleto'] . ". Responsavel original: " . $dados_linha['responsavel_obsoleto'] . ".";
                                $id_usuario_logado = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;
                                $this->model->logHistorico("Importacao de Obsoleto", $justificativa, $id_usuario_logado, $id_documento_novo);
                            }
                            $contador['sucesso']++;
                        } else {
                            throw new Exception("Falha ao salvar o documento no banco de dados.");
                        }
                    } catch (Exception $e) {
                        $contador['falha']++;
                        $erros[] = "Linha com codigo '{$dados_linha['codigo']}': " . $e->getMessage();
                    }
                }
            }
            fclose($handle);
        }

        $mensagem_feedback = "Importação concluída!\n\n- Documentos inseridos: {$contador['sucesso']}\n- Documentos já existentes (ignorados): {$contador['existente']}\n- Falhas: {$contador['falha']}";
        if (!empty($erros)) {
            $mensagem_feedback .= "\n\nDetalhes das falhas:\n" . implode("\n", $erros);
        }
        if (!empty($ignorados)) {
            $mensagem_feedback .= "\n\nDocumentos ignorados (já existentes):\n" . implode("\n", $ignorados);
        }

        $_SESSION['import_status'] = array('tipo' => 'info', 'mensagem' => $mensagem_feedback);
        header('Location: index.php?modulo=documentos_importar');
        exit();
    }

    private function detectarTipoCSV($cabecalho) {
        if (in_array('Tipo_Manual', $cabecalho)) {
            return 'manuais';
        }
        if (in_array('Ano', $cabecalho) && !in_array('Tipo_Manual', $cabecalho)) {
            return 'relatorios';
        }
        return 'documentos'; // Padrão
    }

    private function mapearDadosLinha($data, $tipo) {
        $dados = array(
            'status_nome' => '', 'codigo' => '', 'nome' => '', 'autor' => '', 'revisao' => '',
            'data_vigor' => '', 'data_analise' => '', 'categoria_sigla' => '', 'nome_arquivo' => '',
            'locais_distribuicao' => '', 'data_obsoleto' => '', 'motivo_obsoleto' => '',
            'responsavel_obsoleto' => '', 'tipo_manual' => ''
        );

        if ($tipo === 'manuais') {
            // Mapeamento para Manuais (MQ, MS)
            $dados['status_nome'] = trim($data[0]);
            $dados['tipo_manual'] = trim($data[1]);
            $dados['codigo'] = trim($data[2]);
            $dados['nome'] = trim($data[3]);
            $dados['revisao'] = trim($data[4]);
            $dados['data_vigor'] = trim($data[5]);
            $dados['data_analise'] = trim($data[6]);
            $dados['nome_arquivo'] = trim($data[7]);
            $dados['locais_distribuicao'] = trim($data[8]);
            $dados['autor'] = trim($data[9]); // O campo 'Responsavel' do CSV vira 'autor'
            $dados['data_obsoleto'] = trim($data[10]);
            $dados['motivo_obsoleto'] = trim($data[11]);
            
            // Lógica para definir a categoria correta (MQ ou MS)
            if (strpos($dados['codigo'], 'MS') === 0) {
                $dados['categoria_sigla'] = 'MS';
            } else {
                $dados['categoria_sigla'] = 'MQ';
            }

        } elseif ($tipo === 'relatorios') {
            // Mapeamento para Relatórios (REL)
            $dados['status_nome'] = trim($data[0]);
            $dados['nome'] = trim($data[1]);
            // Cria um código mais descritivo: REL-ANO-NOME
            $ano = trim($data[2]);
            $nome_limpo = substr(preg_replace('/[^a-zA-Z0-9]/', '', $dados['nome']), 0, 10);
            $dados['codigo'] = 'REL-' . $ano . '-' . strtoupper($nome_limpo);
            $dados['nome_arquivo'] = trim($data[3]);
            $dados['data_vigor'] = trim($data[4]);
            $dados['autor'] = trim($data[5]); // O campo 'Responsavel' do CSV vira 'autor'
            $dados['data_obsoleto'] = trim($data[6]);
            $dados['motivo_obsoleto'] = trim($data[7]);
            $dados['categoria_sigla'] = 'RE'; // Categoria correta para relatórios

        } else { // Padrão 'documentos'
            // Mapeamento para Documentos (PQ, IT, FQ, DO, etc.)
            $dados['status_nome'] = trim($data[0]);
            $dados['codigo'] = trim($data[2]);
            $dados['nome'] = trim($data[3]);
            $dados['autor'] = trim($data[4]);
            $dados['revisao'] = trim($data[5]);
            $dados['data_vigor'] = trim($data[6]);
            $dados['data_analise'] = trim($data[7]); // Data_Analise_Critica
            $dados['categoria_sigla'] = trim($data[11]);
            $dados['nome_arquivo'] = trim($data[12]);
            $dados['locais_distribuicao'] = trim($data[13]);
        }
        return $dados;
    }
}