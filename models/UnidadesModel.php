<?php
class UnidadesModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    /**
     * Lista todas as unidades e os documentos associados a cada uma.
     */
    public function getUnidadesComDocumentos() {
        // 1. Pega apenas as unidades permitidas, replicando a regra do cadastro de usuários.
        $unidades_permitidas_ids = array(2, 3, 4, 6, 7, 10, 18);
        
        // Cria os placeholders para a query IN (...) de forma segura
        $placeholders = implode(',', array_fill(0, count($unidades_permitidas_ids), '?'));

        $sql = "SELECT id_local, nome_local FROM t_local WHERE id_local IN ($placeholders) ORDER BY nome_local ASC";
        $stmt_locais = $this->db->prepare($sql);
        $stmt_locais->execute($unidades_permitidas_ids);
        $locais = $stmt_locais->fetchAll(PDO::FETCH_ASSOC);

        $unidades = array();

        // 2. Para cada unidade, busca os documentos vinculados
        foreach ($locais as $local) {
            $id_local = $local['id_local'];
            
            // Documentos vinculados pela tabela de distribuição (t_documento_local)
            // OU diretamente pelo campo id_local na t_documento
            $sql_docs = "SELECT d.id_documento, d.codigo_documento, d.nome_documento, c.sigla_categoria
                         FROM t_documento d
                         JOIN t_categoria c ON d.id_categoria = c.id_categoria
                         WHERE d.id_status = 1 AND (
                               d.id_local = :id_local 
                               OR 
                               d.id_documento IN (SELECT id_documento FROM t_documento_local WHERE id_local = :id_local_in)
                         )
                         ORDER BY c.sigla_categoria, d.codigo_documento";

            $stmt_docs = $this->db->prepare($sql_docs);
            $stmt_docs->execute(array(':id_local' => $id_local, ':id_local_in' => $id_local));
            $documentos = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

            $unidades[] = array(
                'id_local' => $id_local,
                'nome_local' => utf8_encode($local['nome_local']),
                'documentos' => $documentos
            );
        }
        return $unidades;
    }

    public function getUnidadesPermitidas() {
        $unidades_permitidas_ids = array(2, 3, 4, 6, 7, 10, 18);
        $placeholders = implode(',', array_fill(0, count($unidades_permitidas_ids), '?'));
        $sql = "SELECT id_local, nome_local FROM t_local WHERE id_local IN ($placeholders) ORDER BY nome_local ASC";
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
    
    /**
     * Retorna as categorias que podem ter campos customizados.
     */
    public function getCategoriasConfiguraveis() {
        $sql = "SELECT id_categoria, nome_categoria, sigla_categoria FROM t_categoria WHERE escopo_categoria = 'SGQ UNIDADE' OR escopo_categoria IS NULL ORDER BY nome_categoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca os campos configurados para uma unidade e categoria.
     * Se não houver configuração, cria uma padrão com base nos campos existentes.
     */
    public function getCamposConfigurados($id_local, $id_categoria) {
        $sql = "SELECT * FROM t_config_campos_unidade 
                WHERE id_local = :id_local AND id_categoria = :id_categoria 
                ORDER BY ordem ASC, nome_campo_interno ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(':id_local' => $id_local, ':id_categoria' => $id_categoria));
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Lista mestre de todos os campos possíveis no sistema
        $campos_mestre = array(
            'codigo_documento' => 'Código do Documento',
            'nome_documento' => 'Nome do Documento',
            'autor_documento' => 'Autor',
            'revisao_documento' => 'Revisão',
            'sufixo_documento' => 'Sufixo/Idioma',
            'data_vigor_documento' => 'Data de Vigor',
            'data_analise_documento' => 'Próxima Análise Crítica',
            'id_local' => 'Unidade Responsável',
            'controle_documento' => 'Tipo de Manual (Controlado/Não)',
            'distribuicao' => 'Distribuição',
            'arquivo_documento' => 'Anexar Arquivo'
        );

        $campos_configurados = array();
        foreach ($campos as $campo_db) {
            $campos_configurados[$campo_db['nome_campo_interno']] = $campo_db;
        }

        $resultado_final = array();
        $ordem = 0;

        // Itera sobre a lista mestre para garantir que todos os campos apareçam
        foreach ($campos_mestre as $nome_interno => $rotulo_padrao) {
            if (isset($campos_configurados[$nome_interno])) {
                // Se já existe configuração, usa ela
                $resultado_final[] = $campos_configurados[$nome_interno];
            } else {
                // Se não existe, cria uma configuração padrão para exibição
                $resultado_final[] = array(
                    'id_config' => 'new_' . $nome_interno,
                    'id_local' => $id_local,
                    'id_categoria' => $id_categoria,
                    'nome_campo_interno' => $nome_interno,
                    'rotulo_personalizado' => $rotulo_padrao,
                    'tipo_campo' => 'text',
                    'opcoes_select' => null,
                    'visivel' => 1,
                    'obrigatorio' => 1,
                    'ordem' => $ordem
                );
            }
            $ordem++;
        }
        return $resultado_final;
    }
    
    /**
     * Salva/Atualiza a configuração dos campos para uma unidade/categoria.
     */
    public function salvarCampos($id_local, $id_categoria, $dados) {
        $sql = "INSERT INTO t_config_campos_unidade 
                    (id_local, id_categoria, nome_campo_interno, rotulo_personalizado, visivel, obrigatorio, ordem) 
                VALUES 
                    (:id_local, :id_categoria, :nome_campo, :rotulo, :visivel, :obrigatorio, :ordem)
                ON DUPLICATE KEY UPDATE 
                    rotulo_personalizado = VALUES(rotulo_personalizado), 
                    visivel = VALUES(visivel), 
                    obrigatorio = VALUES(obrigatorio), 
                    ordem = VALUES(ordem)";
        $stmt = $this->db->prepare($sql);
    
        foreach ($dados['campos'] as $nome_campo => $config) {
            $stmt->execute(array(
                ':id_local' => $id_local, ':id_categoria' => $id_categoria,
                ':nome_campo' => $nome_campo, ':rotulo' => utf8_decode($config['rotulo']),
                ':visivel' => isset($config['visivel']) ? 1 : 0, ':obrigatorio' => isset($config['obrigatorio']) ? 1 : 0,
                ':ordem' => intval($config['ordem'])
            ));
        }
        return true;
    }
}
?>