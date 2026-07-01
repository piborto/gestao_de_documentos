<?php
// Usa dirname(__FILE__) para garantir que o caminho seja absoluto e à prova de falhas no PHP 5.2
require_once dirname(__FILE__) . '/config/conexao.php';

$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'inicio';

switch ($modulo) {
    case 'documentos':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        $listaDocumentos = $documentosCtrl->gerenciarListagem();        
        $formData = $documentosCtrl->exibirFormulario(); // CORREÇÃO PHP 5.2: Variável temporária para receber o array antes de pegar a chave
        $listaCategorias = $formData['listaCategorias']; // Pega a lista de categorias para o filtro

        $page_title = 'SGQ - Gestão de Documentos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_content.php';
        break;

    case 'documentos_cadastrar':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        
        $formData = $documentosCtrl->exibirFormulario();
        $listaCategorias = $formData['listaCategorias'];
        $listaLocais = $formData['listaLocais'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentosCtrl->salvarNovoDocumento($_POST, $_FILES); // Reutiliza a instância já criada
        }
        
        $page_title = 'SGQ - Cadastrar Documento';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_form.php';
        break;

    case 'documentos_editar':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentosCtrl->atualizarDocumento($_POST, $_FILES);
        }

        $id_documento = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $formData = $documentosCtrl->exibirFormularioEdicao($id_documento);
        $documento = $formData['documento'];
        $listaCategorias = $formData['listaCategorias'];
        $listaLocais = $formData['listaLocais'];

        $page_title = 'SGQ - Editar Documento';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_form.php';
        break;

    case 'documentos_importar':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentosCtrl->importarCSV($_FILES); // O controller cuida do resto
        }

        $page_title = 'SGQ - Importar Documentos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_importar.php';
        break;

    case 'inicio':
    default:
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $notificacoesCtrl = new DocumentosController($conexao);
        $notificacoes = $notificacoesCtrl->obterNotificacoes();

        $page_title = 'SGQ - Painel Principal';
        $page_content_file = dirname(__FILE__) . '/views/index_content.php';
}

// Inclui o layout principal de forma segura
require_once dirname(__FILE__) . '/views/layout/main.php';
?>