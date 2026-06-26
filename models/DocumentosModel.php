<?php
// models/DocumentosModel.php

class DocumentosModel {
    private $db;

    public function __construct($conexao) {
        // Inicializa o Model repassando a conexão PDO
        $this->db = $conexao;
    }

    /**
     * Busca os documentos com filtros dinâmicos
     */
    public function listarDocumentos($id_status, $id_categoria, $busca) {
        $params = array(':id_status' => $id_status);
        $sql = "SELECT d.*, c.sigla_categoria 
                FROM t_documento d 
                JOIN t_categoria c ON d.id_categoria = c.id_categoria 
                WHERE d.id_status = :id_status";
        
        if ($id_categoria !== null) {
            $sql .= " AND d.id_categoria = :id_categoria";
            $params[':id_categoria'] = $id_categoria;
        }

        if ($busca !== null) {
            $sql .= " AND (d.codigo_documento LIKE :busca OR d.nome_documento LIKE :busca)";
            $params[':busca'] = '%' . $busca . '%';
        }

        $sql .= " ORDER BY d.codigo_documento ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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
     * Salva um novo documento no banco de dados
     */
    public function salvarDocumento($dados) {
        // Mapeia os dados do formulário para as colunas do banco
        // Usando 'isset' para lidar com campos que podem não ser enviados
        $params = array(
            ':id_categoria' => $dados['id_categoria'],
            ':id_status' => 1, // 1 = Ativo (ou o status inicial padrão)
            ':codigo_documento' => isset($dados['codigo_documento']) ? $dados['codigo_documento'] : null,
            ':nome_documento' => isset($dados['nome_documento']) ? utf8_decode($dados['nome_documento']) : null,
            ':ano_documento' => isset($dados['ano']) ? $dados['ano'] : null,
            ':autor_documento' => isset($dados['autor_documento']) ? utf8_decode($dados['autor_documento']) : null,
            ':revisao_documento' => isset($dados['revisao_documento']) ? $dados['revisao_documento'] : null,
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
     * Vincula um documento aos locais de distribuição
     */
    public function vincularLocais($id_documento, $locais, $numeros_copia) {
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
}
