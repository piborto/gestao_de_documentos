<?php
class SiglasModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    public function getDb() {
        return $this->db;
    }

    public function listarSiglas($busca = null, $id_status = 1) {
        $sql = "SELECT * FROM t_sigla WHERE id_status = :id_status";
        $params = array(':id_status' => $id_status);

        if (!empty($busca)) {
            $sql .= " AND (nome_sigla LIKE :busca OR definicao_sigla LIKE :busca)";
            $params[':busca'] = '%' . utf8_decode($busca) . '%';
        }

        // A ordenação agora é sempre alfabética, independente do número
        $sql .= " ORDER BY nome_sigla ASC, definicao_sigla ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $siglas = $stmt->fetchAll();

        $siglas_utf8 = array();
        foreach ($siglas as $sigla) {
            $sigla['nome_sigla'] = utf8_encode($sigla['nome_sigla']);
            $sigla['definicao_sigla'] = utf8_encode($sigla['definicao_sigla']);
            $sigla['referencia_sigla'] = utf8_encode($sigla['referencia_sigla']);
            $siglas_utf8[] = $sigla;
        }
        return $siglas_utf8;
    }

    public function getSiglaPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM t_sigla WHERE id_sigla = :id");
        $stmt->execute(array(':id' => $id));
        $sigla = $stmt->fetch();

        if ($sigla) {
            $sigla['nome_sigla'] = utf8_encode($sigla['nome_sigla']);
            $sigla['definicao_sigla'] = utf8_encode($sigla['definicao_sigla']);
            $sigla['referencia_sigla'] = utf8_encode($sigla['referencia_sigla']);
        }
        return $sigla;
    }

    public function salvarSigla($dados) {
        $sql = "INSERT INTO t_sigla (id_status, nome_sigla, definicao_sigla, referencia_sigla, data_sigla) 
                VALUES (:id_status, :nome_sigla, :definicao_sigla, :referencia_sigla, :data_sigla)";
        
        $params = array(
            ':id_status' => $dados['id_status'],
            ':nome_sigla' => utf8_decode($dados['nome_sigla']),
            ':definicao_sigla' => utf8_decode($dados['definicao_sigla']),
            ':referencia_sigla' => utf8_decode($dados['referencia_sigla']),
            ':data_sigla' => $dados['data_sigla']
        );

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao salvar sigla: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarSigla($id, $dados) {
        $sql = "UPDATE t_sigla SET 
                    id_status = :id_status,
                    nome_sigla = :nome_sigla, 
                    definicao_sigla = :definicao_sigla, 
                    referencia_sigla = :referencia_sigla, 
                    data_sigla = :data_sigla 
                WHERE id_sigla = :id_sigla";

        $params = array(
            ':id_sigla' => $id,
            ':id_status' => $dados['id_status'],
            ':nome_sigla' => utf8_decode($dados['nome_sigla']),
            ':definicao_sigla' => utf8_decode($dados['definicao_sigla']),
            ':referencia_sigla' => utf8_decode($dados['referencia_sigla']),
            ':data_sigla' => $dados['data_sigla']
        );

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar sigla: " . $e->getMessage());
            return false;
        }
    }

    public function excluirSigla($id) {
        $stmt = $this->db->prepare("DELETE FROM t_sigla WHERE id_sigla = :id");
        return $stmt->execute(array(':id' => $id));
    }

    public function renumerarSiglas() {
        // 1. Pega todas as siglas em ordem alfabética
        $stmt = $this->db->prepare("SELECT id_sigla FROM t_sigla ORDER BY nome_sigla ASC, definicao_sigla ASC");
        $stmt->execute();
        $siglas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 2. Inicia uma transação para garantir a consistência
        $this->db->beginTransaction();
        try {
            $numero = 1;
            foreach ($siglas as $id_sigla) {
                $stmt_update = $this->db->prepare("UPDATE t_sigla SET numero_sigla = :numero WHERE id_sigla = :id");
                $stmt_update->execute(array(':numero' => $numero, ':id' => $id_sigla));
                $numero++;
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao renumerar siglas: " . $e->getMessage());
            return false;
        }
    }

    public function siglaExiste($nome_sigla) {
        $stmt = $this->db->prepare("SELECT id_sigla FROM t_sigla WHERE nome_sigla = :nome_sigla LIMIT 1");
        $stmt->execute(array(':nome_sigla' => utf8_decode($nome_sigla)));
        return $stmt->fetchColumn() !== false;
    }

    public function salvarSiglaImportada($dados) {
        $sql = "INSERT INTO t_sigla (id_status, nome_sigla, definicao_sigla, referencia_sigla, data_sigla) 
                VALUES (:id_status, :nome_sigla, :definicao_sigla, :referencia_sigla, :data_sigla)";
        
        $params = array(
            ':id_status' => $dados['id_status'],
            ':nome_sigla' => utf8_decode($dados['nome_sigla']),
            ':definicao_sigla' => utf8_decode($dados['definicao_sigla']),
            ':referencia_sigla' => utf8_decode($dados['referencia_sigla']),
            ':data_sigla' => !empty($dados['data_sigla']) ? $dados['data_sigla'] : date('Y-m-d')
        );

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro MySQL: " . $e->getMessage());
        }
    }

    /**
     * Busca o histórico completo de uma sigla específica.
     */
    public function listarHistoricoPorSigla($id_sigla) {
        $sql = "SELECT h.*, u.nome_usuario 
                FROM t_historico h
                JOIN t_usuario_qualidade u ON h.qualidade_id = u.id_usuario_qualidade
                WHERE h.id_sigla = :id_sigla
                ORDER BY h.data_historico DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(':id_sigla' => $id_sigla));
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
     * Busca a data de publicação mais recente entre as siglas ativas.
     */
    public function getUltimaAtualizacao() {
        $stmt = $this->db->prepare("SELECT MAX(data_sigla) as ultima_atualizacao FROM t_sigla WHERE id_status = 1");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['ultima_atualizacao'] : null;
    }
}
?>