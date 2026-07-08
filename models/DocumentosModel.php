<?php
// models/DocumentosModel.php

class DocumentosModel {
    private $db;

    public function __construct($conexao) {
        // Inicializa o Model repassando a conexão PDO
        $this->db = $conexao;
    }

    /**
     * Busca um documento pelo seu código para evitar duplicatas.
     */
    public function documentoExistePorCodigo($codigo) {
        // BYPASS DE IMPORTAÇÃO: Retornando false para forçar a entrada de tudo
        // Descomente o código abaixo futuramente se quiser voltar a bloquear repetições manuais
        /*
        $stmt = $this->db->prepare("SELECT id_documento FROM t_documento WHERE codigo_documento = :codigo LIMIT 1");
        $stmt->execute(array(':codigo' => $codigo));
        return $stmt->fetchColumn() !== false;
        */
        return false;
    }

    /**
     * Busca um documento pela combinação de código e nome de arquivo.
     */
    public function documentoExistePorCodigoEArquivo($codigo, $arquivo) {
        // BYPASS DE IMPORTAÇÃO: Retornando false para forçar a entrada dos obsoletos idênticos
        /*
        $stmt = $this->db->prepare("SELECT id_documento FROM t_documento WHERE codigo_documento = :codigo AND arquivo_documento = :arquivo LIMIT 1");
        $stmt->execute(array(':codigo' => $codigo, ':arquivo' => $arquivo));
        return $stmt->fetchColumn() !== false;
        */
        return false;
    }

    /**
     * Busca o ID de um documento pela combinação de código e nome de arquivo.
     */
    public function getDocumentoIdPorCodigoEArquivo($codigo, $arquivo) {
        // BYPASS DE IMPORTAÇÃO: Retornando false para forçar a entrada dos 13 arquivos "clones"
        /*
        $stmt = $this->db->prepare("SELECT id_documento FROM t_documento WHERE codigo_documento = :codigo AND arquivo_documento = :arquivo LIMIT 1");
        $stmt->execute(array(':codigo' => $codigo, ':arquivo' => $arquivo));
        return $stmt->fetchColumn();
        */
        return false;
    }

    /**
     * Busca os documentos com filtros dinâmicos
     */
    public function listarDocumentos($id_status, $ids_categoria, $busca, $id_distribuicao) {
        $params = array(':id_status' => $id_status);

        $sql_select_base = "SELECT d.*, c.sigla_categoria, GROUP_CONCAT(l.nome_local ORDER BY l.nome_local ASC SEPARATOR ', ') AS locais_distribuicao";
        $sql_from_base = "FROM t_documento d JOIN t_categoria c ON d.id_categoria = c.id_categoria";

        // Se for busca de obsoletos, junta com a tabela de histórico para pegar os detalhes
        if ($id_status == 3) {
            $sql_select_base .= ", h.data_historico AS data_obsoleto, h.justificativa_historico AS motivo_obsoleto, u.nome_usuario AS responsavel_obsoleto";
            
            // Subquery para pegar apenas o registro de histórico mais recente de obsolescência para cada documento
            $sql_from_base .= " LEFT JOIN (
                                    SELECT h1.* FROM t_historico h1
                                    INNER JOIN (
                                        SELECT id_documento, MAX(id_historico) as max_id
                                        FROM t_historico
                                        WHERE acao_historico LIKE '%Obsoleto%'
                                        GROUP BY id_documento
                                    ) h2 ON h1.id_documento = h2.id_documento AND h1.id_historico = h2.max_id
                                ) h ON d.id_documento = h.id_documento
                                LEFT JOIN t_usuario_qualidade u ON h.qualidade_id = u.id_usuario_qualidade";
        }

        $sql = $sql_select_base . " " . $sql_from_base . " LEFT JOIN t_documento_local dl ON d.id_documento = dl.id_documento
                LEFT JOIN t_local l ON dl.id_local = l.id_local
                WHERE d.id_status = :id_status";

        if (!empty($ids_categoria)) {
            // Usa placeholders nomeados para evitar misturar com os posicionais
            $cat_placeholders = array();
            foreach ($ids_categoria as $i => $id) {
                $key = ':cat_' . $i;
                $cat_placeholders[] = $key;
                $params[$key] = $id;
            }
            $sql .= " AND d.id_categoria IN (" . implode(',', $cat_placeholders) . ")";
        }
        
        if (!empty($busca)) {
            $sql .= " AND (d.codigo_documento LIKE :busca1 OR d.nome_documento LIKE :busca2)";
            $params[':busca1'] = '%' . utf8_decode($busca) . '%';
            $params[':busca2'] = '%' . utf8_decode($busca) . '%';
        }

        if (!empty($id_distribuicao)) {
            // Adiciona uma subquery para garantir que o documento está vinculado ao local de distribuição
            $sql .= " AND d.id_documento IN (SELECT id_documento FROM t_documento_local WHERE id_local = :id_distribuicao)";
            $params[':id_distribuicao'] = $id_distribuicao;
        }

        // GROUP BY é obrigatório agora para não duplicar linhas na tabela visual
        $sql .= " GROUP BY d.id_documento ORDER BY d.codigo_documento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $documentos = $stmt->fetchAll();

        // Converte os campos de texto para UTF-8
        $documentos_utf8 = array();
        foreach ($documentos as $doc) {
            $doc['nome_documento'] = utf8_encode($doc['nome_documento']);
            
            // Adicionamos a conversão UTF-8 para o Autor também:
            $doc['autor_documento'] = $doc['autor_documento'] ? utf8_encode($doc['autor_documento']) : null;
            
            $doc['locais_distribuicao'] = $doc['locais_distribuicao'] ? utf8_encode($doc['locais_distribuicao']) : '-';
            if (isset($doc['responsavel_obsoleto'])) {
                $doc['responsavel_obsoleto'] = utf8_encode($doc['responsavel_obsoleto']);
                $doc['motivo_obsoleto'] = utf8_encode($doc['motivo_obsoleto']);
            }
            $documentos_utf8[] = $doc;
        }
        return $documentos_utf8;
    }

    /**
     * Busca todas as categorias de documentos
     */
    public function listarCategorias() {
        $stmt = $this->db->prepare("SELECT * FROM t_categoria ORDER BY nome_categoria ASC");
        $stmt->execute();
        $categorias = $stmt->fetchAll();

        // Converte os nomes para UTF-8, já que o banco está em latin1
        $categorias_utf8 = array();
        foreach ($categorias as $categoria) {
            $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
            $categorias_utf8[] = $categoria;
        }
        return $categorias_utf8;
    }

    /**
     * Verifica se uma categoria é pai (possui filhos).
     */
    public function isParentCategory($id_categoria) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM t_categoria WHERE id_categoria_pai = :id");
        $stmt->execute(array(':id' => (int)$id_categoria));
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Retorna um array com os IDs das categorias filhas de um pai.
     */
    public function getChildCategoryIds($id_categoria_pai) {
        $stmt = $this->db->prepare("SELECT id_categoria FROM t_categoria WHERE id_categoria_pai = :id_pai");
        $stmt->execute(array(':id_pai' => (int)$id_categoria_pai));
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        // Garante que o retorno seja sempre um array, mesmo que vazio.
        return $result ? $result : array();
    }

    /**
     * Busca todos os locais de distribuição
     */
    public function listarLocais() {
        $stmt = $this->db->prepare("SELECT * FROM t_local ORDER BY nome_local ASC");
        $stmt->execute();
        $locais = $stmt->fetchAll();

        // Converte os nomes para UTF-8, pois o banco está em latin1
        $locais_utf8 = array();
        foreach ($locais as $local) {
            $local['nome_local'] = utf8_encode($local['nome_local']);
            $locais_utf8[] = $local;
        }
        return $locais_utf8;
    }

    /**
     * Busca o nome de um local pelo ID
     */
    public function getNomeLocalPorId($id_local) {
        $stmt = $this->db->prepare("SELECT nome_local FROM t_local WHERE id_local = :id");
        $stmt->execute(array(':id' => (int)$id_local));
        $local = $stmt->fetch();
        // Retorna o nome do local já com a codificação correta
        return $local ? utf8_encode($local['nome_local']) : null;
    }

    /**
     * Busca uma categoria pela sigla. Se não existir, cria uma nova.
     */
    public function getOrCreateCategoriaPorSigla($sigla) {
        if (empty($sigla)) $sigla = 'INDEFINIDA';

        $stmt = $this->db->prepare("SELECT id_categoria FROM t_categoria WHERE sigla_categoria = :sigla");
        $stmt->execute(array(':sigla' => $sigla));
        $id = $stmt->fetchColumn();

        if ($id) {
            return $id;
        } else {
            // Cria uma nova categoria com nome igual à sigla
            $stmt_insert = $this->db->prepare("INSERT INTO t_categoria (sigla_categoria, nome_categoria) VALUES (:sigla, :nome)");
            $stmt_insert->execute(array(':sigla' => $sigla, ':nome' => $sigla));
            return $this->db->lastInsertId();
        }
    }

    /**
     * Busca um local pelo nome. Se não existir, cria um novo.
     */
    public function getOrCreateLocalPorNome($nome) {
        if (empty($nome)) return null;
        $nome_latin1 = utf8_decode($nome); // Converte para o charset do banco

        $stmt = $this->db->prepare("SELECT id_local FROM t_local WHERE nome_local = :nome");
        $stmt->execute(array(':nome' => $nome_latin1));
        $id = $stmt->fetchColumn();

        if ($id) {
            return $id;
        } else {
            $stmt_insert = $this->db->prepare("INSERT INTO t_local (nome_local) VALUES (:nome)");
            $stmt_insert->execute(array(':nome' => $nome_latin1));
            return $this->db->lastInsertId();
        }
    }

    /**
     * Busca uma categoria pelo ID.
     */
    public function getCategoriaPorId($id_categoria) {
        $stmt = $this->db->prepare("SELECT * FROM t_categoria WHERE id_categoria = :id");
        $stmt->execute(array(':id' => (int)$id_categoria));
        $categoria = $stmt->fetch();
        if ($categoria) {
            $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
        }
        return $categoria;
    }

    public function getDocumentoPorId($id_documento) {
        $stmt = $this->db->prepare("SELECT d.*, c.sigla_categoria 
                                    FROM t_documento d
                                    JOIN t_categoria c ON d.id_categoria = c.id_categoria
                                    WHERE d.id_documento = :id");
        $stmt->execute(array(':id' => $id_documento));
        $documento = $stmt->fetch();

        // Fecha o cursor da consulta anterior para permitir que novas consultas sejam executadas.
        $stmt->closeCursor();

        if ($documento) {
            // Converte para UTF-8
            $documento['nome_documento'] = utf8_encode($documento['nome_documento']);
            $documento['autor_documento'] = utf8_encode($documento['autor_documento']);
            $documento['sufixo_documento'] = utf8_encode($documento['sufixo_documento']);

            // Busca os locais de distribuição vinculados
            $stmt_locais = $this->db->prepare("SELECT id_local, numero_copia FROM t_documento_local WHERE id_documento = :id");
            $stmt_locais->execute(array(':id' => $id_documento));
            
            $locais_vinculados = $stmt_locais->fetchAll();
            $documento['locais_distribuicao_ids'] = array();
            foreach ($locais_vinculados as $local) {
                $documento['locais_distribuicao_ids'][$local['id_local']] = $local['numero_copia'];
            }
        }
        return $documento;
    }

    public function getDocumentoPorNomeArquivo($nome_arquivo) {
        $stmt = $this->db->prepare("SELECT d.id_documento, d.codigo_documento, c.sigla_categoria 
                                    FROM t_documento d
                                    JOIN t_categoria c ON d.id_categoria = c.id_categoria
                                    WHERE d.arquivo_documento = :nome_arquivo 
                                    LIMIT 1");
        $stmt->execute(array(':nome_arquivo' => $nome_arquivo));
        return $stmt->fetch();
    }

    public function getDocumentoPorCodigo($codigo_documento) {
        if (empty($codigo_documento)) {
            return false;
        }
        // CORREÇÃO: Adicionado campos essenciais para a geração do nome do arquivo.
        $stmt = $this->db->prepare("SELECT d.id_documento, d.codigo_documento, d.nome_documento, 
                                           d.revisao_documento, d.data_vigor_documento, d.sufixo_documento,
                                           c.sigla_categoria 
                                    FROM t_documento d
                                    JOIN t_categoria c ON d.id_categoria = c.id_categoria
                                    WHERE d.codigo_documento = :codigo_documento
                                    LIMIT 1");
        $stmt->execute(array(':codigo_documento' => $codigo_documento));
        return $stmt->fetch();
    }

    public function sincronizarArquivo($id_documento, $nome_arquivo) {
        // Esta função é um alias para a atualização, mas focada em apenas um campo.
        // Poderia ser uma query mais simples, mas reutilizar a lógica de atualização é seguro.
        $sql = "UPDATE t_documento SET arquivo_documento = :nome_arquivo WHERE id_documento = :id_documento";
        $params = array(
            ':nome_arquivo' => $nome_arquivo,
            ':id_documento' => $id_documento
        );
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao sincronizar arquivo: " . $e->getMessage());
            return false;
        }
    }

    public function vincularArquivo($id_documento, $nome_arquivo) {
        if ($id_documento > 0 && !empty($nome_arquivo)) {
            $sql = "UPDATE t_documento SET arquivo_documento = :nome_arquivo WHERE id_documento = :id_documento";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array(':nome_arquivo' => $nome_arquivo, ':id_documento' => $id_documento));
            return $stmt->rowCount() > 0;
        }
        return false;
    }

    public function atualizarDocumento($id_documento, $dados) {
        $params = array(
            ':id_documento' => $id_documento,
            ':id_categoria' => isset($dados['id_categoria']) ? $dados['id_categoria'] : null,
            ':codigo_documento' => isset($dados['codigo_documento']) ? $dados['codigo_documento'] : null,
            ':nome_documento' => isset($dados['nome_documento']) ? utf8_decode($dados['nome_documento']) : null,
            ':autor_documento' => isset($dados['autor_documento']) ? utf8_decode($dados['autor_documento']) : null,
            ':revisao_documento' => (isset($dados['revisao_documento']) && $dados['revisao_documento'] !== '') ? $dados['revisao_documento'] : null,
            ':data_vigor_documento' => isset($dados['data_vigor_documento']) ? $dados['data_vigor_documento'] : null,
            ':data_analise_documento' => isset($dados['data_analise_documento']) ? $dados['data_analise_documento'] : null,
            ':arquivo_documento' => isset($dados['arquivo_documento']) ? $dados['arquivo_documento'] : null,
            ':sufixo_documento' => isset($dados['sufixo']) ? utf8_decode($dados['sufixo']) : null,
            ':controle_documento' => isset($dados['controle_documento']) ? $dados['controle_documento'] : 0
        );

        $sql = "UPDATE t_documento SET
                    id_categoria = :id_categoria,
                    codigo_documento = :codigo_documento,
                    nome_documento = :nome_documento,
                    autor_documento = :autor_documento,
                    revisao_documento = :revisao_documento,
                    data_vigor_documento = :data_vigor_documento,
                    data_analise_documento = :data_analise_documento,
                    arquivo_documento = :arquivo_documento,
                    sufixo_documento = :sufixo_documento,
                    controle_documento = :controle_documento
                WHERE id_documento = :id_documento";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar documento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um novo documento no banco de dados
     */
    public function salvarDocumento($dados) {
        // Mapeia os dados do formulário para as colunas do banco
        // Usando 'isset' para lidar com campos que podem não ser enviados
        $params = array(
            ':id_categoria' => isset($dados['id_categoria']) ? $dados['id_categoria'] : null,
            ':id_status' => isset($dados['id_status']) ? $dados['id_status'] : 1, // 1 = Ativo por padrão
            ':codigo_documento' => isset($dados['codigo_documento']) ? $dados['codigo_documento'] : null,
            ':nome_documento' => isset($dados['nome_documento']) ? utf8_decode($dados['nome_documento']) : null,
            ':ano_documento' => isset($dados['ano_documento']) ? $dados['ano_documento'] : null,
            ':autor_documento' => isset($dados['autor_documento']) ? utf8_decode($dados['autor_documento']) : null,
            ':revisao_documento' => (isset($dados['revisao_documento']) && $dados['revisao_documento'] !== '') ? $dados['revisao_documento'] : null,
            ':sufixo_documento' => isset($dados['sufixo']) ? $dados['sufixo'] : null,
            ':data_vigor_documento' => isset($dados['data_vigor_documento']) ? $dados['data_vigor_documento'] : null,
            ':data_analise_documento' => isset($dados['data_analise_documento']) ? $dados['data_analise_documento'] : null,
            ':arquivo_documento' => isset($dados['arquivo_documento']) ? $dados['arquivo_documento'] : null,
            ':controle_documento' => (isset($dados['tipo_manual']) && $dados['tipo_manual'] === 'Controlado') ? 1 : 0
        );

        $sql = "INSERT INTO t_documento (
                    id_categoria, id_status, codigo_documento, nome_documento, ano_documento, 
                    autor_documento, revisao_documento, sufixo_documento, data_vigor_documento, 
                    data_analise_documento, arquivo_documento, controle_documento
                ) VALUES (
                    :id_categoria, :id_status, :codigo_documento, :nome_documento, :ano_documento, 
                    :autor_documento, :revisao_documento, :sufixo_documento, :data_vigor_documento, 
                    :data_analise_documento, :arquivo_documento, :controle_documento
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $this->db->lastInsertId(); // Retorna o ID do documento inserido
        } catch (PDOException $e) {
            // Em um ambiente de produção, seria bom logar o erro em vez de exibi-lo
            error_log("Erro ao salvar documento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Salva um documento vindo da importação CSV.
     */
    public function salvarDocumentoImportado($dados) {
        $params = array(
            ':id_categoria' => isset($dados['id_categoria']) ? $dados['id_categoria'] : null,
            ':id_status' => isset($dados['id_status']) ? $dados['id_status'] : 1,
            ':codigo_documento' => isset($dados['codigo_documento']) ? $dados['codigo_documento'] : null,
            ':nome_documento' => isset($dados['nome_documento']) ? utf8_decode($dados['nome_documento']) : null,
            ':autor_documento' => isset($dados['autor_documento']) ? utf8_decode($dados['autor_documento']) : null,
            ':revisao_documento' => (isset($dados['revisao_documento']) && $dados['revisao_documento'] !== '') ? $dados['revisao_documento'] : null,
            ':data_vigor_documento' => !empty($dados['data_vigor_documento']) ? $dados['data_vigor_documento'] : date('Y-m-d'),
            ':data_analise_documento' => isset($dados['data_analise_documento']) ? $dados['data_analise_documento'] : null,
            ':arquivo_documento' => isset($dados['arquivo_documento']) ? $dados['arquivo_documento'] : null,
            ':controle_documento' => isset($dados['controle_documento']) ? $dados['controle_documento'] : 0
        );

        $sql = "INSERT INTO t_documento (
                    id_categoria, id_status, codigo_documento, nome_documento, 
                    autor_documento, revisao_documento, data_vigor_documento, 
                    data_analise_documento, arquivo_documento, controle_documento
                ) VALUES (
                    :id_categoria, :id_status, :codigo_documento, :nome_documento, 
                    :autor_documento, :revisao_documento, :data_vigor_documento, 
                    :data_analise_documento, :arquivo_documento, :controle_documento
                )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Em vez de retornar false calado, jogamos o erro real do MySQL direto 
            // para o Controller imprimir na tela de resultados do usuário!
            throw new Exception("Erro MySQL: " . $e->getMessage());
        }
    }

    /**
     * Vincula um documento aos locais de distribuição
     */
    public function vincularLocais($id_documento, $locais, $numeros_copia) {
        // Primeiro, remove todos os vínculos existentes para este documento para evitar duplicatas
        $stmt_delete = $this->db->prepare("DELETE FROM t_documento_local WHERE id_documento = :id_documento");
        $stmt_delete->execute(array(':id_documento' => $id_documento));

        $sql = "INSERT INTO t_documento_local (id_documento, id_local, numero_copia) VALUES (:id_documento, :id_local, :numero_copia)";
        $stmt = $this->db->prepare($sql);

        foreach ($locais as $id_local) {
            $stmt->execute(array(
                ':id_documento' => $id_documento,
                ':id_local' => $id_local,
                // Pega o número da cópia se for um manual, senão insere NULL
                ':numero_copia' => isset($numeros_copia[$id_local]) ? $numeros_copia[$id_local] : null
            ));
        }
    }

    /**
     * Busca documentos com data de análise vencida.
     * Considera documentos com status Ativo (1) ou Em Revisão (2).
     */
    public function listarDocumentosVencidos() {
        $sql = "SELECT codigo_documento, nome_documento, data_analise_documento
                FROM t_documento
                WHERE data_analise_documento < CURDATE()
                  AND data_analise_documento > '0000-00-00'
                  AND data_analise_documento IS NOT NULL
                  AND id_status IN (1, 2)
                ORDER BY data_analise_documento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca documentos com data de análise nos próximos 6 meses.
     * Considera documentos com status Ativo (1) ou Em Revisão (2).
     */
    public function listarDocumentosProximosVencimento() {
        $sql = "SELECT codigo_documento, nome_documento, data_analise_documento
                FROM t_documento
                WHERE data_analise_documento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
                  AND data_analise_documento IS NOT NULL
                  AND id_status IN (1, 2)
                ORDER BY data_analise_documento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca documentos e siglas agendados para os próximos 30 dias.
     */
    public function listarItensAgendados() {
        $sql = "(SELECT 
                    'Documento' as tipo, 
                    codigo_documento as codigo, 
                    nome_documento as nome, 
                    data_vigor_documento as data_vigor 
                FROM t_documento 
                WHERE id_status = 2 AND data_vigor_documento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
                UNION
                (SELECT 
                    'Sigla' as tipo, 
                    nome_sigla as codigo, 
                    definicao_sigla as nome, 
                    data_sigla as data_vigor 
                FROM t_sigla 
                WHERE id_status = 2 AND data_sigla BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
                ORDER BY data_vigor ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        // A conversão para UTF-8 já é feita no controller que chama esta função.
        return $stmt->fetchAll();
    }


    /**
     * Registra uma ação no histórico geral (t_historico).
     * @param string $acao Ação realizada (ex: "Documento Obsoleto Importado").
     * @param string $justificativa Detalhes da ação.
     * @param int $id_usuario ID do usuário que realizou a ação.
     * @param int|null $id_documento ID do documento relacionado.
     * @param int|null $id_sigla ID da sigla relacionada.
     * @return bool Sucesso ou falha na inserção.
     */
    public function logHistorico($acao, $justificativa, $id_usuario, $id_documento = null, $id_sigla = null) {
        $sql = "INSERT INTO t_historico (acao_historico, justificativa_historico, data_historico, qualidade_id, id_documento, id_sigla)
                VALUES (:acao, :justificativa, NOW(), :id_usuario, :id_documento, :id_sigla)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(array(
            ':acao' => utf8_decode($acao),
            ':justificativa' => utf8_decode($justificativa),
            ':id_usuario' => $id_usuario,
            ':id_documento' => $id_documento,
            ':id_sigla' => $id_sigla
        ));
    }

    /**
     * Busca o histórico completo de um documento específico.
     */
    public function listarHistoricoPorDocumento($id_documento) {
        $sql = "SELECT h.*, u.nome_usuario 
                FROM t_historico h
                JOIN t_usuario_qualidade u ON h.qualidade_id = u.id_usuario_qualidade
                WHERE h.id_documento = :id_documento
                ORDER BY h.data_historico DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(':id_documento' => $id_documento));
        $historico = $stmt->fetchAll();

        // Converte para UTF-8
        $historico_utf8 = array();
        foreach ($historico as $item) {
            $item['acao_historico'] = utf8_encode($item['acao_historico']);
            $item['justificativa_historico'] = utf8_encode($item['justificativa_historico']);
            $item['nome_usuario'] = utf8_encode($item['nome_usuario']);
            $historico_utf8[] = $item;
        }
        return $historico_utf8;
    }

    /**
     * Altera o status de um documento para obsoleto e registra no histórico.
     */
    public function tornarObsoleto($id_documento, $justificativa, $id_usuario) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE t_documento SET id_status = 3 WHERE id_documento = :id");
            $stmt->execute(array(':id' => $id_documento));

            $this->logHistorico("Documento Tornou-se Obsoleto", $justificativa, $id_usuario, $id_documento);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao tornar obsoleto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Altera o status de um documento para Em Vigor (1) e registra no histórico.
     */
    public function restaurarDocumento($id_documento, $id_usuario) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE t_documento SET id_status = 1 WHERE id_documento = :id");
            $stmt->execute(array(':id' => $id_documento));

            $this->logHistorico("Documento Restaurado", "O documento foi restaurado do status obsoleto para 'Em Vigor'.", $id_usuario, $id_documento);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao restaurar documento: " . $e->getMessage());
            return false;
        }
    }

    public function excluirDocumento($id_documento) {
        $stmt = $this->db->prepare("DELETE FROM t_documento WHERE id_documento = :id");
        return $stmt->execute(array(':id' => $id_documento));
    }
}