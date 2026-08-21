<?php
class ConfigCamposModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    public function listarUnidades($idLocal = null) {
        $ids = array(2, 3, 4, 6, 7, 10, 18, 32);
        if ($idLocal !== null) {
            $ids = array($idLocal);
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id_local, nome_local FROM t_local WHERE id_local IN ($placeholders) ORDER BY nome_local ASC");
        $stmt->execute($ids);
        $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($locais as &$local) {
            $local['nome_local'] = utf8_encode($local['nome_local']);
        }
        return $locais;
    }

    public function listarCategorias($idLocal = null) {
        $sql = "SELECT id_categoria, nome_categoria, sigla_categoria, id_local FROM t_categoria WHERE escopo_categoria = :escopo";
        $params = array(':escopo' => 'SGQ UNIDADE');
        if ($idLocal !== null) {
            $sql .= " AND (id_local IS NULL OR id_local = :id_local)";
            $params[':id_local'] = $idLocal;
        }
        $sql .= " ORDER BY nome_categoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categorias as &$categoria) {
            $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
        }
        return $categorias;
    }

    private function catalogoPadrao() {
        return array(
            'codigo_documento' => 'Código do Documento',
            'nome_documento' => 'Nome do Documento',
            'autor_documento' => 'Autor',
            'revisao_documento' => 'Revisão',
            'sufixo_documento' => 'Sufixo/Idioma',
            'ano_documento' => 'Ano',
            'data_vigor_documento' => 'Data de Vigor',
            'data_analise_documento' => 'Próxima Análise Crítica',
            'id_local' => 'Unidade Responsável',
            'controle_documento' => 'Tipo de Manual',
            'distribuicao' => 'Distribuição',
            'arquivo_documento' => 'Anexar Arquivo'
        );
    }

    private function listarMetadados() {
        return array();
    }

    private function nomeDoMetadado($metadado) {
        foreach (array('nome_campo_interno', 'campo_interno', 'nome_campo', 'campo', 'chave_metadado') as $chave) {
            if (isset($metadado[$chave]) && trim($metadado[$chave]) !== '') return trim($metadado[$chave]);
        }
        return '';
    }

    private function tipoCampoPadrao($nome) {
        if (strpos($nome, 'data_') === 0) return 'date';
        if ($nome === 'revisao_documento' || $nome === 'ano_documento') return 'number';
        if ($nome === 'arquivo_documento') return 'file';
        return 'text';
    }

    public function getConfigCampos($idLocal, $idCategoria) {
        $padrao = $this->catalogoPadrao();
        foreach ($this->listarMetadados() as $metadado) {
            $nome = $this->nomeDoMetadado($metadado);
            if ($nome === '') continue;
            $rotulo = '';
            foreach (array('rotulo_padrao', 'rotulo', 'label', 'nome_metadado', 'descricao') as $chave) {
                if (isset($metadado[$chave]) && trim($metadado[$chave]) !== '') {
                    $rotulo = trim($metadado[$chave]);
                    break;
                }
            }
            $padrao[$nome] = $rotulo !== '' ? utf8_encode($rotulo) : $nome;
        }

        $stmt = $this->db->prepare('SELECT * FROM t_config_campos_unidade WHERE id_local = :local AND id_categoria = :categoria ORDER BY ordem ASC, nome_campo_interno ASC');
        $stmt->execute(array(':local' => $idLocal, ':categoria' => $idCategoria));
        $existentes = array();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $campo) {
            $existentes[$campo['nome_campo_interno']] = $campo;
        }

        $resultado = array();
        $ordem = 0;
        foreach ($padrao as $nome => $rotulo) {
            if (isset($existentes[$nome])) {
                $campo = $existentes[$nome];
                $campo['rotulo_personalizado'] = utf8_encode($campo['rotulo_personalizado']);
                if (empty($campo['tipo_campo'])) $campo['tipo_campo'] = $this->tipoCampoPadrao($nome);
            } else {
                $campo = array('id_local' => $idLocal, 'id_categoria' => $idCategoria, 'nome_campo_interno' => $nome,
                    'rotulo_personalizado' => $rotulo, 'tipo_campo' => $this->tipoCampoPadrao($nome), 'visivel' => 1, 'obrigatorio' => 0, 'ordem' => $ordem);
            }
            $campo['ordem'] = $ordem++;
            $resultado[] = $campo;
        }
        return $resultado;
    }

    public function listarCamposConfigurados($idLocal, $idCategoria) {
        return $this->getConfigCampos($idLocal, $idCategoria);
    }

    public function salvarConfiguracao($idLocal, $idCategoria, $campos) {
        $permitidos = $this->catalogoPadrao();
        foreach ($this->listarMetadados() as $metadado) {
            $nome = $this->nomeDoMetadado($metadado);
            if ($nome !== '') $permitidos[$nome] = true;
        }
        $sql = 'INSERT INTO t_config_campos_unidade (id_local, id_categoria, nome_campo_interno, rotulo_personalizado, visivel, obrigatorio, ordem)
                VALUES (:local, :categoria, :nome, :rotulo, :visivel, :obrigatorio, :ordem)
                ON DUPLICATE KEY UPDATE rotulo_personalizado = VALUES(rotulo_personalizado), visivel = VALUES(visivel), obrigatorio = VALUES(obrigatorio), ordem = VALUES(ordem)';
        $stmt = $this->db->prepare($sql);
        $this->db->beginTransaction();
        try {
            foreach ($campos as $nome => $config) {
                if (!isset($permitidos[$nome])) continue;
                $stmt->execute(array(':local' => $idLocal, ':categoria' => $idCategoria, ':nome' => $nome,
                    ':rotulo' => utf8_decode(trim($config['rotulo'])), ':visivel' => isset($config['visivel']) ? 1 : 0,
                    ':obrigatorio' => isset($config['obrigatorio']) ? 1 : 0, ':ordem' => intval($config['ordem'])));
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Erro ao salvar configuração de campos: ' . $e->getMessage());
            return false;
        }
    }

    public function criarCategoria($nome, $sigla, $idLocal = null) {
        $stmt = $this->db->prepare('INSERT INTO t_categoria (nome_categoria, sigla_categoria, escopo_categoria, id_local) VALUES (:nome, :sigla, :escopo, :id_local)');
        try {
            return $stmt->execute(array(':nome' => utf8_decode(trim($nome)), ':sigla' => strtoupper(trim($sigla)), ':escopo' => 'SGQ UNIDADE', ':id_local' => $idLocal));
        } catch (PDOException $e) {
            error_log('Erro ao criar categoria customizada: ' . $e->getMessage());
            return false;
        }
    }

    public function categoriaDisponivelParaLocal($idCategoria, $idLocal) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM t_categoria WHERE id_categoria = :id_categoria AND escopo_categoria = 'SGQ UNIDADE' AND (id_local IS NULL OR id_local = :id_local)");
        $stmt->execute(array(':id_categoria' => $idCategoria, ':id_local' => $idLocal));
        return $stmt->fetchColumn() > 0;
    }

    public function listarCategoriasDaUnidade($idLocal) {
        $stmt = $this->db->prepare("SELECT id_categoria, nome_categoria, sigla_categoria FROM t_categoria WHERE escopo_categoria = 'SGQ UNIDADE' AND id_local = :id_local ORDER BY nome_categoria ASC");
        $stmt->execute(array(':id_local' => $idLocal));
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categorias as &$categoria) $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
        return $categorias;
    }

    public function listarCategoriasComConfiguracao($idLocal) {
        $stmt = $this->db->prepare("SELECT c.id_categoria, c.nome_categoria, c.sigla_categoria, c.id_local,
                    COUNT(cc.nome_campo_interno) AS total_configurados
                FROM t_categoria c
                LEFT JOIN t_config_campos_unidade cc
                    ON cc.id_categoria = c.id_categoria
                   AND cc.id_local = :config_local
                   AND cc.nome_campo_interno <> '__categoria_owner__'
                WHERE c.escopo_categoria = 'SGQ UNIDADE'
                  AND (c.id_local IS NULL OR c.id_local = :categoria_local)
                GROUP BY c.id_categoria, c.nome_categoria, c.sigla_categoria, c.id_local
                ORDER BY c.nome_categoria ASC");
        $stmt->execute(array(':config_local' => $idLocal, ':categoria_local' => $idLocal));
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categorias as &$categoria) $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
        return $categorias;
    }

    public function listarCamposSalvos($idLocal, $idCategoria) {
        $stmt = $this->db->prepare("SELECT nome_campo_interno, rotulo_personalizado, visivel, obrigatorio, ordem
                FROM t_config_campos_unidade
                WHERE id_local = :id_local
                  AND id_categoria = :id_categoria
                  AND nome_campo_interno <> '__categoria_owner__'
                ORDER BY ordem ASC, nome_campo_interno ASC");
        $stmt->execute(array(':id_local' => $idLocal, ':id_categoria' => $idCategoria));
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($campos as &$campo) $campo['rotulo_personalizado'] = utf8_encode($campo['rotulo_personalizado']);
        return $campos;
    }

    public function getCategoriaDaUnidade($idCategoria, $idLocal) {
        $stmt = $this->db->prepare("SELECT id_categoria, nome_categoria, sigla_categoria FROM t_categoria WHERE id_categoria = :id_categoria AND escopo_categoria = 'SGQ UNIDADE' AND id_local = :id_local");
        $stmt->execute(array(':id_categoria' => $idCategoria, ':id_local' => $idLocal));
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($categoria) $categoria['nome_categoria'] = utf8_encode($categoria['nome_categoria']);
        return $categoria;
    }

    public function atualizarCategoriaDaUnidade($idCategoria, $idLocal, $nome, $sigla) {
        $stmt = $this->db->prepare("UPDATE t_categoria SET nome_categoria = :nome, sigla_categoria = :sigla WHERE id_categoria = :id_categoria AND escopo_categoria = 'SGQ UNIDADE' AND id_local = :id_local");
        return $stmt->execute(array(':nome' => utf8_decode(trim($nome)), ':sigla' => strtoupper(trim($sigla)), ':id_categoria' => $idCategoria, ':id_local' => $idLocal));
    }

    public function excluirCategoriaDaUnidade($idCategoria, $idLocal) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM t_config_campos_unidade WHERE id_categoria = :id_categoria AND id_local = :id_local");
            $stmt->execute(array(':id_categoria' => $idCategoria, ':id_local' => $idLocal));
            $stmt = $this->db->prepare("DELETE FROM t_categoria WHERE id_categoria = :id_categoria AND escopo_categoria = 'SGQ UNIDADE' AND id_local = :id_local");
            $stmt->execute(array(':id_categoria' => $idCategoria, ':id_local' => $idLocal));
            $sucesso = $stmt->rowCount() > 0;
            $this->db->commit();
            return $sucesso;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Erro ao excluir categoria da unidade: ' . $e->getMessage());
            return false;
        }
    }
}
?>