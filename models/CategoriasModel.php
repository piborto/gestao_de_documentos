<?php
class CategoriasModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    public function listarCategorias() {
        $sql = "SELECT * FROM t_categoria ORDER BY nome_categoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categorias_utf8 = array();
        foreach ($categorias as $categoria) {
            $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
            $categorias_utf8[] = $categoria;
        }
        return $categorias_utf8;
    }

    public function getCategoriaPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM t_categoria WHERE id_categoria = :id");
        $stmt->execute(array(':id' => $id));
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($categoria) {
            $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
        }
        return $categoria;
    }

    public function salvarCategoria($dados) {
        $sql = "INSERT INTO t_categoria (nome_categoria, sigla_categoria, escopo_categoria) 
                VALUES (:nome, :sigla, :escopo)";
        
        $params = array(
            ':nome' => utf8_decode($dados['nome_categoria']),
            ':sigla' => strtoupper($dados['sigla_categoria']),
            ':escopo' => !empty($dados['escopo_categoria']) ? $dados['escopo_categoria'] : null,
        );

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao salvar categoria: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarCategoria($id, $dados) {
        $sql = "UPDATE t_categoria SET 
                    nome_categoria = :nome, 
                    sigla_categoria = :sigla, 
                    escopo_categoria = :escopo
                WHERE id_categoria = :id_categoria";

        $params = array(
            ':id_categoria' => $id,
            ':nome' => utf8_decode($dados['nome_categoria']),
            ':sigla' => strtoupper($dados['sigla_categoria']),
            ':escopo' => !empty($dados['escopo_categoria']) ? $dados['escopo_categoria'] : null,
        );

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            return false;
        }
    }
}
?>