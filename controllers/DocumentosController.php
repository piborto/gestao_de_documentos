<?php
// controllers/DocumentosController.php

require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/../models/DocumentosModel.php';
require_once dirname(__FILE__) . '/../models/ConfigCamposModel.php';

class DocumentosController {
    private $model;
    private $configModel;

    public function __construct($conexao) {
        // Inicializa o Model repassando a conexão PDO
        $this->model = new DocumentosModel($conexao);
        $this->configModel = new ConfigCamposModel($conexao);
    }

    public function obterCamposConfiguradosAjax() {
        $id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;
        $id_local = isset($_GET['id_local']) ? intval($_GET['id_local']) : 0;
        $id_perfil = isset($_SESSION['id_perfil']) ? intval($_SESSION['id_perfil']) : 0;

        if ($id_perfil == 2 || $id_perfil == 3) {
            $id_local = isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : 0;
        }

        header('Content-Type: application/json; charset=utf-8');
        if ($id_categoria <= 0 || $id_local <= 0 || (($id_perfil == 2 || $id_perfil == 3) && !$this->configModel->categoriaDisponivelParaLocal($id_categoria, $id_local))) {
            echo json_encode(array('sucesso' => false, 'campos' => array()));
            exit();
        }

        $campos = $this->configModel->getConfigCampos($id_local, $id_categoria);
        echo json_encode(array('sucesso' => true, 'campos' => $campos));
        exit();
    }

    /**
     * Gerencia a listagem trazendo os filtros aplicados na tela
     */
    public function gerenciarListagem() {
        // Captura os filtros tratando a falta deles (Padrão PHP 5.2)
        $id_status    = isset($_GET['status']) ? intval($_GET['status']) : 1; // Padrão alterado para 1 (Em Vigor/Ativos)
        $id_categoria = (isset($_GET['categoria']) && $_GET['categoria'] !== '') ? intval($_GET['categoria']) : null;
        $busca        = isset($_GET['busca']) ? trim($_GET['busca']) : null;
        $id_distribuicao = (isset($_GET['distribuicao']) && $_GET['distribuicao'] !== '') ? intval($_GET['distribuicao']) : null;

        // Captura o perfil e local do usuário logado para aplicar as regras de visibilidade
        $id_perfil_usuario = isset($_SESSION['id_perfil']) ? intval($_SESSION['id_perfil']) : null;
        $id_local_usuario = isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : null;

        // Lógica para tratar categorias pai
        $ids_para_busca = array();
        if ($id_categoria !== null) {
            if ($this->model->isParentCategory($id_categoria)) {
                $ids_para_busca = $this->model->getChildCategoryIds($id_categoria);
            } else {
                $ids_para_busca[] = $id_categoria;
            }
        }

        // Executa a busca no Model com os parâmetros parametrizados
        return array(
            'documentos' => $this->model->listarDocumentos($id_status, $ids_para_busca, $busca, $id_distribuicao, $id_perfil_usuario, $id_local_usuario),
            'listaCategorias' => $this->model->listarCategorias(
                ($id_perfil_usuario == 2 || $id_perfil_usuario == 3) ? 'SGQ UNIDADE' : null,
                ($id_perfil_usuario == 2 || $id_perfil_usuario == 3) ? $id_local_usuario : null
            ),
            'listaLocais' => $this->model->listarLocais($id_perfil_usuario, $id_local_usuario)
        );
    }

    /**
     * Exibe o formulário de cadastro/edição
     */
    public function exibirFormulario() {
        $escopo_categorias = null;
        $id_local_categorias = null;
        // Se for RQ da Unidade, só pode ver categorias do escopo da unidade
        if (isset($_SESSION['id_perfil']) && $_SESSION['id_perfil'] == 2) {
            $escopo_categorias = 'SGQ UNIDADE';
            $id_local_categorias = isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : null;
        } elseif (isset($_SESSION['id_perfil']) && $_SESSION['id_perfil'] == 3) {
            $escopo_categorias = 'SGQ UNIDADE';
            $id_local_categorias = isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : null;
        }

        // Busca a lista de categorias para popular o <select> no formulário
        return array(
            // Passa o escopo para filtrar as categorias, se aplicável
            'listaCategorias' => $this->model->listarCategorias($escopo_categorias, $id_local_categorias),
            'listaLocais' => $this->model->listarLocais($this->getPerfilAtual(), $this->getLocalAtual())
        );
    }

    public function exibirFormularioEdicao($id_documento) {
        if ($id_documento <= 0) {
            header('Location: index.php?modulo=documentos&erro=id_invalido');
            exit();
        }
        $documento = $this->model->getDocumentoPorId($id_documento, $this->getPerfilAtual(), $this->getLocalAtual());
        if (!$documento) {
            header('Location: index.php?modulo=documentos&erro=nao_encontrado');
            exit();
        }
        if (!$this->podeGerirDocumento($documento)) {
            header('Location: index.php?modulo=documentos&erro=acesso_negado');
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
        $agendados = $this->model->listarItensAgendados($this->getPerfilAtual(), $this->getLocalAtual());
        $agendados_utf8 = array();
        foreach ($agendados as $item) {
            $item['codigo'] = utf8_encode($item['codigo']);
            $item['nome'] = utf8_encode($item['nome']);
            // Limita o nome para não quebrar o layout
            if (strlen($item['nome']) > 50) {
                $item['nome'] = substr($item['nome'], 0, 50) . '...';
            }
            $agendados_utf8[] = $item;
        }

        $vencidos = $this->model->listarDocumentosVencidos($this->getPerfilAtual(), $this->getLocalAtual());
        $vencidos_utf8 = array();
        foreach ($vencidos as $doc) {
            $doc['codigo_documento'] = utf8_encode($doc['codigo_documento']);
            $doc['nome_documento'] = utf8_encode($doc['nome_documento']);
            $vencidos_utf8[] = $doc;
        }

        $proximos = $this->model->listarDocumentosProximosVencimento($this->getPerfilAtual(), $this->getLocalAtual());
        $proximos_utf8 = array();
        foreach ($proximos as $doc) {
            $doc['codigo_documento'] = utf8_encode($doc['codigo_documento']);
            $doc['nome_documento'] = utf8_encode($doc['nome_documento']);
            $proximos_utf8[] = $doc;
        }

        return array(
            'vencidos' => $vencidos_utf8,
            'proximos' => $proximos_utf8,
            'agendados' => $agendados_utf8
        );
    }

    /**
     * Busca os dados para o dashboard de unidades.
     */
    public function obterDadosDashboardUnidades() {
        return $this->model->listarStatusDocumentosPorUnidade($this->getPerfilAtual(), $this->getLocalAtual());
    }

    /**
     * Retorna o total de documentos em vigor.
     */
    public function getTotalDocumentosEmVigor() {
        return $this->model->getTotalDocumentosEmVigor($this->getPerfilAtual(), $this->getLocalAtual());
    }

    /**
     * Helper function to sanitize filenames, based on the old system's logic.
     * @param string $nome
     * @return string
     */
    private function limparNomeArquivo($nome) {
        // Mapa de caracteres para substituição (robusto para PHP 5.2)
        $mapa = array(
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ç' => 'c', 'Ç' => 'C', ' ' => '_'
        );
        $nome_sem_acentos = strtr($nome, $mapa);
        $nome_limpo = preg_replace('/[^a-zA-Z0-9_]/', '', $nome_sem_acentos);
        return strtolower($nome_limpo);
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

        // Regra de Negócio: Força o id_local para o RQ da Unidade
        if (isset($_SESSION['id_perfil']) && $_SESSION['id_perfil'] == 2) {
            $postData['id_local'] = isset($_SESSION['id_local']) ? $_SESSION['id_local'] : null;
            $postData = $this->limitarDistribuicaoRq($postData);
        } else {
            $postData['id_local'] = isset($postData['id_local']) && !empty($postData['id_local']) ? $postData['id_local'] : null;
        }

        // --- 2. Get Category Info ---
        $categoriaInfo = $this->model->getCategoriaPorId($postData['id_categoria']);
        if (!$categoriaInfo) {
            header('Location: index.php?modulo=documentos&erro=categoria_invalida');
            exit();
        }
        $cat_sigla = $categoriaInfo['sigla_categoria'];

        // Força o código 'CA' para a categoria Calendário no backend para garantir a regra de negócio
        if ($cat_sigla === 'CA') {
            $postData['codigo_documento'] = 'CA';
        }

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
            $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $postData['codigo_documento']));
            $nome_sanitizado = $this->limparNomeArquivo($postData['nome_documento']);
            $nome_final_padronizado = $cod_limpo . "_" . $nome_sanitizado . "." . $extensao;
        } elseif ($cat_sigla_check == 'MQ' || $cat_sigla_check == 'MS') {
            // Lógica específica para Manuais
            $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
            $revisao = isset($postData['revisao_documento']) ? (int)$postData['revisao_documento'] : 0;
            $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
            $str_local = '';

            // Se exatamente UM local de distribuição for selecionado, adiciona ao nome do arquivo
            if (isset($postData['distribuicao']) && count($postData['distribuicao']) === 1) {
                $id_local_unico = $postData['distribuicao'][0];
                $nome_local = $this->model->getNomeLocalPorId($id_local_unico);
                $str_local = "_" . $this->limparNomeArquivo($nome_local);
            }
            $nome_final_padronizado = $cod_limpo . $str_local . "_rev" . $revisao . "_" . $ano_vigor . "." . $extensao;
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
            $this->salvarMetadadosDoFormulario($id_documento, $postData);
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
        $doc_atual = $this->model->getDocumentoPorId($id_documento, $this->getPerfilAtual(), $this->getLocalAtual());
        if (!$doc_atual) {
             header('Location: index.php?modulo=documentos&erro=nao_encontrado');
             exit();
        }
           if (!$this->podeGerirDocumento($doc_atual)) {
               header('Location: index.php?modulo=documentos&erro=acesso_negado');
               exit();
           }

        // Regra de Negócio: Força o id_local para o RQ da Unidade
        if (isset($_SESSION['id_perfil']) && $_SESSION['id_perfil'] == 2) {
            $postData['id_local'] = isset($_SESSION['id_local']) ? $_SESSION['id_local'] : null;
            $postData = $this->limitarDistribuicaoRq($postData);
        } else {
            $postData['id_local'] = isset($postData['id_local']) && !empty($postData['id_local']) ? $postData['id_local'] : null;
        }

        // Força o código 'CA' para a categoria Calendário no backend para garantir a regra de negócio
        if ($doc_atual['sigla_categoria'] === 'CA') {
            $postData['codigo_documento'] = 'CA';
        }

        // Lógica para remover o arquivo se a checkbox for marcada
        $remover_arquivo = isset($postData['remover_arquivo']) && $postData['remover_arquivo'] == '1';
        if ($remover_arquivo && !empty($doc_atual['arquivo_documento'])) {
            $pasta_arquivo_antigo = dirname(__FILE__) . '/../uploads/documentos/' . $doc_atual['sigla_categoria'] . '/';
            $caminho_arquivo_antigo = $pasta_arquivo_antigo . $doc_atual['arquivo_documento'];
            if (file_exists($caminho_arquivo_antigo)) {
                unlink($caminho_arquivo_antigo);
            }
            // Prepara o campo para ser salvo como nulo no banco
            $postData['arquivo_documento'] = null;
            $doc_atual['arquivo_documento'] = null; // Atualiza a variável local para a lógica a seguir
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

            // Lógica de renomeação replicada do cadastro para garantir consistência
            $extensao = strtolower(pathinfo($fileData['arquivo_documento']['name'], PATHINFO_EXTENSION));
            $cat_sigla_check = strtoupper(trim($doc_atual['sigla_categoria']));
            $nome_final_padronizado = '';
            
            if ($cat_sigla_check == 'LM') {
                $nome_final_padronizado = "ListaMestra." . $extensao;
            } elseif ($cat_sigla_check == 'DO') {
                $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $postData['codigo_documento']));
                $nome_sanitizado = $this->limparNomeArquivo($postData['nome_documento']);
                $nome_final_padronizado = $cod_limpo . "_" . $nome_sanitizado . "." . $extensao;
            } elseif ($cat_sigla_check == 'MQ' || $cat_sigla_check == 'MS') {
                $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
                $revisao = (int)$postData['revisao_documento'];
                $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
                $str_local = (isset($postData['distribuicao']) && count($postData['distribuicao']) === 1) ? "_" . $this->limparNomeArquivo($this->model->getNomeLocalPorId($postData['distribuicao'][0])) : '';
                $nome_final_padronizado = $cod_limpo . $str_local . "_rev" . $revisao . "_" . $ano_vigor . "." . $extensao;
            } else { // Padrão para PQ, IT, FQ, etc.
                $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['codigo_documento']));
                $rev_pad = str_pad($postData['revisao_documento'], 2, '0', STR_PAD_LEFT);
                $ano_vigor = date('Y', strtotime($postData['data_vigor_documento']));
                $sufixo = isset($postData['sufixo']) ? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $postData['sufixo'])) : '';
                $str_sufixo = !empty($sufixo) ? "_" . $sufixo : "";
                $nome_final_padronizado = $cod_limpo . "_rev" . $rev_pad . "_" . $ano_vigor . $str_sufixo . "." . $extensao;
            }

            if (move_uploaded_file($fileData['arquivo_documento']['tmp_name'], $pasta_upload . $nome_final_padronizado)) {
                $postData['arquivo_documento'] = $nome_final_padronizado;
            }
        } else {
            // Se nenhum arquivo novo foi enviado, mantém o nome do arquivo antigo.
            $postData['arquivo_documento'] = $doc_atual['arquivo_documento'];
        }

        // Adiciona o controle de manual (se existir) aos dados a serem salvos
        $postData['controle_documento'] = (isset($postData['tipo_manual']) && $postData['tipo_manual'] === 'Controlado') ? 1 : 0;

        $sucesso = $this->model->atualizarDocumento($id_documento, $postData);

        if ($sucesso) {
            $this->salvarMetadadosDoFormulario($id_documento, $postData);
            $this->model->logHistorico("Documento Editado", $justificativa, $id_usuario, $id_documento);
            
            // Atualiza os locais de distribuição, incluindo os números de cópia para manuais
            $distribuicao = isset($postData['distribuicao']) ? $postData['distribuicao'] : array();
            $numeros_copia = array();
            if (isset($postData['numero_manual']) && is_array($postData['numero_manual'])) {
                $numeros_copia = $postData['numero_manual'];
            }
            $this->model->vincularLocais($id_documento, $distribuicao, $numeros_copia);

            // Reconstrói a URL de redirecionamento com os filtros
            $query_params = http_build_query(array(
                'status' => isset($postData['filtro_status']) ? $postData['filtro_status'] : '1',
                'categoria' => isset($postData['filtro_categoria']) ? $postData['filtro_categoria'] : '',
                'busca' => isset($postData['filtro_busca']) ? $postData['filtro_busca'] : '',
                'distribuicao' => isset($postData['filtro_distribuicao']) ? $postData['filtro_distribuicao'] : ''
            ));

            header('Location: index.php?modulo=documentos&sucesso=edicao&' . $query_params);
        } else {
            // Mantém os filtros mesmo em caso de erro
            header('Location: index.php?modulo=documentos_editar&id=' . $id_documento . '&erro=falha_db&' . http_build_query($_GET));
        }
        exit();
    }

    /**
     * Gera o nome de arquivo padronizado com base nas regras de negócio.
     * Reutiliza a lógica de cadastro/edição.
     */
    private function _gerarNomeArquivoPadronizado($documento, $extensao, $nome_arquivo_original = null) {
        $cat_sigla_check = strtoupper(trim($documento['sigla_categoria']));

        if ($cat_sigla_check == 'LM') {
            return "ListaMestra." . $extensao;
        }

        $cod_limpo = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $documento['codigo_documento']));

        if ($cat_sigla_check == 'DO') {
            $nome_sanitizado = $this->limparNomeArquivo($documento['nome_documento']);
            return $cod_limpo . "_" . $nome_sanitizado . "." . $extensao;
        }

        // Lógica de extração para sincronização
        $revisao = '0';
        $ano_vigor = date('Y', strtotime($documento['data_vigor_documento']));

        if ($nome_arquivo_original) {
            if (preg_match('/rev(\d+)/i', $nome_arquivo_original, $rev_match)) {
                $revisao = (int)$rev_match[1];
            }
            if (preg_match('/(\d{4})/', $nome_arquivo_original, $ano_match)) {
                $ano_vigor_extraido = (int)$ano_match[1];
                if ($ano_vigor_extraido > 1990 && $ano_vigor_extraido < 2100) {
                    $ano_vigor = $ano_vigor_extraido;
                }
            }
        }

        if ($cat_sigla_check == 'MQ' || $cat_sigla_check == 'MS') {
            $rev_str = (int)$revisao; // Sem padding para manuais
            return $cod_limpo . "_rev" . $rev_str . "_" . $ano_vigor . "." . $extensao;
        }

        // Padrão para PQ, IT, FQ, etc.
        $sufixo = isset($documento['sufixo_documento']) ? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $documento['sufixo_documento'])) : '';
        $str_sufixo = !empty($sufixo) ? "_" . $sufixo : "";
        $rev_pad = str_pad($revisao, 2, '0', STR_PAD_LEFT);
        return $cod_limpo . "_rev" . $rev_pad . "_" . $ano_vigor . $str_sufixo . "." . $extensao;
    }

    /**
     * Extrai o código do documento de um nome de arquivo com padrões variados.
     */
    private function _extrairCodigoDoNomeArquivo($nome_arquivo) {
        // Padrão 1: FQ-01, PQ-01.01, DO-01
        if (preg_match('/^([A-Z]{2}-[\d\.]+)/i', $nome_arquivo, $matches)) {
            return $matches[1];
        }
        // Padrão 2: MQ_rev, MS_Intranet_rev
        if (preg_match('/^(MQ|MS)/i', $nome_arquivo, $matches)) {
            // Para manuais, o código é apenas a sigla.
            return strtoupper($matches[1]);
        }
        // Padrão 3: FQ0401, PQ0101
        if (preg_match('/^([A-Z]{2,3}[\d]+)/i', $nome_arquivo, $matches)) {
            // Tenta formatar para o padrão com hífen, ex: FQ0401 -> FQ-0401
            $sigla = substr($matches[1], 0, 2);
            $numero = substr($matches[1], 2);
            if (strlen($numero) == 6 && strpos($numero, '.') === false) {
                // Formata IT040201 -> 04.02.01
                $parte1 = substr($numero, 0, 2);
                $parte2 = substr($numero, 2, 2);
                $parte3 = substr($numero, 4, 2);
                $numero = $parte1 . '.' . $parte2 . '.' . $parte3;
            } elseif (strlen($numero) == 4 && strpos($numero, '.') === false) {
                // Formata FQ0401 -> 04.01
                $parte1 = substr($numero, 0, 2);
                $parte2 = substr($numero, 2);
                $numero = $parte1 . '.' . $parte2;
            }
            return $sigla . '-' . $numero;
        }
        return null;
    }

    public function sincronizarArquivos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['arquivos'])) {
            return null; // Não exibe resultados se a página for acessada via GET
        }

        $resultados = array(
            'sucesso' => array(),
            'falha' => array()
        );

        $arquivos = $_FILES['arquivos'];
        $total_arquivos = count($arquivos['name']);

        for ($i = 0; $i < $total_arquivos; $i++) {
            $nome_arquivo = $arquivos['name'][$i];
            $tmp_name = $arquivos['tmp_name'][$i];
            $error = $arquivos['error'][$i];

            if ($error !== UPLOAD_ERR_OK || empty($nome_arquivo)) {
                if (!empty($nome_arquivo)) {
                    $resultados['falha'][] = "Erro no upload do arquivo '{$nome_arquivo}'.";
                }
                continue;
            }

            // Lógica de busca flexível: extrai o código do nome do arquivo
            $codigo_documento = $this->_extrairCodigoDoNomeArquivo($nome_arquivo);
            $documento = $this->model->getDocumentoPorCodigo($codigo_documento);

            if (!$documento) {
                $resultados['falha'][] = "Nenhum registro de documento encontrado para o arquivo '{$nome_arquivo}' (código extraído: " . ($codigo_documento ? $codigo_documento : 'N/A') . ").";
                continue;
            }

            // Gera o novo nome padronizado para o arquivo
            $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
            $nome_final_padronizado = $this->_gerarNomeArquivoPadronizado($documento, $extensao, $nome_arquivo);

            $pasta_destino = dirname(__FILE__) . '/../uploads/documentos/' . $documento['sigla_categoria'] . '/';
            if (!is_dir($pasta_destino)) {
                mkdir($pasta_destino, 0775, true);
            }
            $caminho_final = $pasta_destino . $nome_final_padronizado;

            // Move o arquivo enviado para o destino JÁ COM O NOME CORRETO
            if (move_uploaded_file($tmp_name, $caminho_final)) {
                // Atualiza o banco de dados com o novo nome padronizado
                $this->model->vincularArquivo($documento['id_documento'], $nome_final_padronizado);
                $resultados['sucesso'][] = "Arquivo '{$nome_arquivo}' vinculado e renomeado para '{$nome_final_padronizado}' (Doc: {$documento['codigo_documento']}).";
            } else {
                $resultados['falha'][] = "Falha ao mover o arquivo '{$nome_arquivo}' para a pasta de destino.";
            }
        }
        return $resultados;
    }

    public function tornarObsoleto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;
            $justificativa = isset($_POST['justificativa']) ? trim($_POST['justificativa']) : '';
            $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1; // 1 como fallback para 'Sistema'

            if ($id_documento > 0 && !empty($justificativa)) {
                $documento = $this->model->getDocumentoPorId($id_documento, $this->getPerfilAtual(), $this->getLocalAtual());
                $sucesso = ($documento && $this->podeGerirDocumento($documento)) ? $this->model->tornarObsoleto($id_documento, $justificativa, $id_usuario) : false;
                if ($sucesso) {
                    header('Location: index.php?modulo=documentos&status=3&sucesso=obsoleto'); // status 3 = Obsoletos
                } else {
                    header('Location: index.php?modulo=documentos&erro=obsoleto');
                }
            } else {
                header('Location: index.php?modulo=documentos&erro=dados_invalidos');
            }
            exit();
        }
    }

    public function restaurarDocumento() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;
            $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;

            if ($id_documento > 0) {
                $documento = $this->model->getDocumentoPorId($id_documento, $this->getPerfilAtual(), $this->getLocalAtual());
                $sucesso = ($documento && $this->podeGerirDocumento($documento)) ? $this->model->restaurarDocumento($id_documento, $id_usuario) : false;
                if ($sucesso) {
                    header('Location: index.php?modulo=documentos&status=1&sucesso=restaurado'); // status 1 = Em Vigor
                } else {
                    header('Location: index.php?modulo=documentos&status=3&erro=restaurar');
                }
            } else {
                header('Location: index.php?modulo=documentos&status=3&erro=dados_invalidos');
            }
            exit();
        }
    }

    public function excluirDocumento() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;
            $status_retorno = isset($_POST['filtro_status_retorno']) ? intval($_POST['filtro_status_retorno']) : 1;

            if ($id_documento > 0) {
                $documento = $this->model->getDocumentoPorId($id_documento, $this->getPerfilAtual(), $this->getLocalAtual());
                if ($documento && $this->podeGerirDocumento($documento)) {
                    $this->model->excluirDocumento($id_documento);
                }
            }

            // Redireciona de volta para a aba correta (Em Vigor ou Obsoletos)
            header('Location: index.php?modulo=documentos&status=' . $status_retorno . '&sucesso=excluido');
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

    private function getPerfilAtual() {
        return isset($_SESSION['id_perfil']) ? intval($_SESSION['id_perfil']) : null;
    }

    private function getLocalAtual() {
        return isset($_SESSION['id_local']) ? intval($_SESSION['id_local']) : null;
    }

    private function podeGerirDocumento($documento) {
        if ($this->getPerfilAtual() !== 2) {
            return true;
        }
        return isset($documento['id_local']) && $documento['id_local'] !== null && (int)$documento['id_local'] === $this->getLocalAtual();
    }

    private function limitarDistribuicaoRq($postData) {
        if ($this->getPerfilAtual() === 2 && $this->getLocalAtual() !== null) {
            $postData['distribuicao'] = array($this->getLocalAtual());
            $postData['numero_manual'] = isset($postData['numero_manual']) && is_array($postData['numero_manual']) && isset($postData['numero_manual'][$this->getLocalAtual()])
                ? array($this->getLocalAtual() => $postData['numero_manual'][$this->getLocalAtual()]) : array();
        }
        return $postData;
    }

    private function salvarMetadadosDoFormulario($idDocumento, $postData) {
        $idLocal = isset($postData['id_local']) ? intval($postData['id_local']) : $this->getLocalAtual();
        $idCategoria = isset($postData['id_categoria']) ? intval($postData['id_categoria']) : 0;
        if ($idDocumento <= 0 || $idLocal <= 0 || $idCategoria <= 0) return;

        $camposConfigurados = $this->configModel->getConfigCampos($idLocal, $idCategoria);
        $metadados = isset($postData['metadados']) && is_array($postData['metadados']) ? $postData['metadados'] : array();
        $mapaValores = array(
            'sufixo_documento' => 'sufixo',
            'controle_documento' => 'tipo_manual'
        );
        foreach ($camposConfigurados as $campo) {
            $nome = $campo['nome_campo_interno'];
            if (isset($mapaValores[$nome])) {
                $origem = $mapaValores[$nome];
                if (isset($postData[$origem])) $metadados[$nome] = $postData[$origem];
            } elseif (isset($postData[$nome])) {
                $metadados[$nome] = $postData[$nome];
            }
        }
        if (isset($postData['distribuicao']) && is_array($postData['distribuicao'])) {
            $metadados['distribuicao'] = implode(',', $postData['distribuicao']);
        }
        if (isset($postData['arquivo_documento']) && is_string($postData['arquivo_documento'])) {
            $metadados['arquivo_documento'] = $postData['arquivo_documento'];
        }
        $this->model->salvarMetadadosDocumento($idDocumento, $metadados);
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