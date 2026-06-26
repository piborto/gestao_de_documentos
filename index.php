<?php

// Inclui a conexão com o banco de dados no início de tudo.
// Isso garante que os cabeçalhos e a sessão sejam iniciados antes de qualquer HTML.
require_once 'config/conexao.php';

$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'inicio';

switch ($modulo) {
    case 'documentos':
        // Carrega o controller e os dados ANTES de incluir o layout
        require_once 'controllers/DocumentosController.php';
        
        $page_title = 'SGQ - Gestão de Documentos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_content.php';
        break;

    case 'documentos_cadastrar':
        require_once 'controllers/DocumentosController.php';
        $page_title = 'SGQ - Cadastrar Documento';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_form.php';
        break;
    
    // Adicione outros módulos aqui no futuro
    // case 'siglas':
    //     ...
    //     break;

    default:
        $page_title = 'SGQ - Painel Principal';
        $page_content_file = dirname(__FILE__) . '/views/contents/index_content.php';
}

// Inclui o layout principal
require_once 'views/layout/main.php';