<?php
class UsuariosModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    public function getDb() {
        return $this->db;
    }

    /**
     * Lista todos os usuários do sistema.
     */
    public function listarUsuarios($idLocal = null) {
        $sql = "SELECT u.*, p.nome_perfil, l.nome_local 
            FROM t_usuario_qualidade u
            JOIN t_perfil p ON u.id_perfil = p.id_perfil
            LEFT JOIN t_local l ON u.id_local = l.id_local";
        $params = array();
        if ($idLocal !== null) {
            $sql .= " WHERE u.id_local = :id_local";
            $params[':id_local'] = $idLocal;
        }
        $sql .= " ORDER BY u.data_cadastro_usuario ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Converte os campos de texto para UTF-8
        $usuarios_utf8 = array();
        foreach ($usuarios as $usuario) {
            $usuario['nome_usuario'] = utf8_encode($usuario['nome_usuario']);
            $usuario['nome_perfil'] = utf8_encode($usuario['nome_perfil']);
            $usuario['nome_local'] = isset($usuario['nome_local']) ? utf8_encode($usuario['nome_local']) : 'N/A';
            $usuarios_utf8[] = $usuario;
        }
        return $usuarios_utf8;
    }

    public function getUsuarioPorId($id, $idLocal = null) {
        $sql = "SELECT * FROM t_usuario_qualidade WHERE id_usuario_qualidade = :id";
        $params = array(':id' => $id);
        if ($idLocal !== null) {
            $sql .= " AND id_local = :id_local";
            $params[':id_local'] = $idLocal;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $usuario['nome_usuario'] = utf8_encode($usuario['nome_usuario']);
        }
        return $usuario;
    }

    public function listarPerfis() {
        $stmt = $this->db->prepare("SELECT * FROM t_perfil ORDER BY nome_perfil ASC");
        $stmt->execute();
        $perfis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $perfis_utf8 = array();
        foreach ($perfis as $perfil) {
            $perfil['nome_perfil'] = utf8_encode($perfil['nome_perfil']);
            $perfis_utf8[] = $perfil;
        }
        return $perfis_utf8;
    }

    public function listarLocais($idLocal = null) {
        if ($idLocal !== null) {
            $stmt = $this->db->prepare("SELECT * FROM t_local WHERE id_local = :id_local ORDER BY nome_local ASC");
            $stmt->execute(array(':id_local' => $idLocal));
            $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $locais_utf8 = array();
            foreach ($locais as $local) {
                $local['nome_local'] = utf8_encode($local['nome_local']);
                $locais_utf8[] = $local;
            }
            return $locais_utf8;
        }

        $unidades_permitidas_ids = array(2, 3, 4, 6, 7, 10, 18, 32);
        
        $placeholders = implode(',', array_fill(0, count($unidades_permitidas_ids), '?'));

        $sql = "SELECT * FROM t_local WHERE id_local IN ($placeholders)";
        
        $sql .= " ORDER BY nome_local ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($unidades_permitidas_ids);
        $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $locais_utf8 = array();
        foreach ($locais as $local) {
            $local['nome_local'] = utf8_encode($local['nome_local']);
            $locais_utf8[] = $local;
        }
        return $locais_utf8;
    }

    public function salvarUsuario($dados, $idLocalForcado = null) {
        $sql = "INSERT INTO t_usuario_qualidade (nome_usuario, login_usuario, email_usuario, senha_usuario, id_perfil, id_local, status_usuario, data_cadastro_usuario) 
            VALUES (:nome, :login, :email, :senha, :id_perfil, :id_local, :status, NOW())";
        
        $params = array(
            ':nome' => utf8_decode($dados['nome_usuario']),
            ':login' => $dados['login_usuario'],
            ':email' => $dados['email_usuario'],
            ':senha' => hash('sha256', $dados['senha_usuario']),
            ':id_perfil' => $dados['id_perfil'],
            ':id_local' => $idLocalForcado !== null ? $idLocalForcado : (!empty($dados['id_local']) ? $dados['id_local'] : null),
            ':status' => isset($dados['status_usuario']) ? 1 : 0
        );

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao salvar usuário: " . $e->getMessage());
            return false;
        }
    }

    public function atualizarUsuario($id, $dados, $idLocalForcado = null) {
        $sql_parts = array(
            "nome_usuario = :nome",
            "login_usuario = :login",
            "email_usuario = :email",
            "id_perfil = :id_perfil",
            "id_local = :id_local",
            "status_usuario = :status"
        );

        $params = array(
            ':id' => $id,
            ':nome' => utf8_decode($dados['nome_usuario']),
            ':login' => $dados['login_usuario'],
            ':email' => $dados['email_usuario'],
            ':id_perfil' => $dados['id_perfil'],
            ':id_local' => $idLocalForcado !== null ? $idLocalForcado : (!empty($dados['id_local']) ? $dados['id_local'] : null),
            ':status' => isset($dados['status_usuario']) ? 1 : 0
        );

        if (!empty($dados['senha_usuario'])) {
            $sql_parts[] = "senha_usuario = :senha";
            $params[':senha'] = hash('sha256', $dados['senha_usuario']);
        }

        $sql = "UPDATE t_usuario_qualidade SET " . implode(', ', $sql_parts) . " WHERE id_usuario_qualidade = :id";
        if ($idLocalForcado !== null) {
            $sql .= " AND id_local = :scope_local";
            $params[':scope_local'] = $idLocalForcado;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }

    public function alterarStatusUsuario($id, $status, $idLocal = null) {
        $sql = "UPDATE t_usuario_qualidade SET status_usuario = :status WHERE id_usuario_qualidade = :id";
        $params = array(':status' => $status, ':id' => $id);
        if ($idLocal !== null) {
            $sql .= " AND id_local = :id_local";
            $params[':id_local'] = $idLocal;
        }
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluirUsuario($id, $idLocal = null) {
        // Adicionar verificação para não excluir o próprio usuário ou o admin principal
        if ($id == $_SESSION['usuario_id'] || $id == 1) {
            return false;
        }
        $sql = "DELETE FROM t_usuario_qualidade WHERE id_usuario_qualidade = :id";
        $params = array(':id' => $id);
        if ($idLocal !== null) {
            $sql .= " AND id_local = :id_local";
            $params[':id_local'] = $idLocal;
        }
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
?>
