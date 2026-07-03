<?php
// Garante que a sessão seja iniciada apenas uma vez, no ponto de entrada principal.
if (session_id() == "") {
    session_start();
}
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

    case 'documentos_sincronizar':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // A lógica de sincronização será chamada aqui e retornará os resultados
            $resultados = $documentosCtrl->sincronizarArquivos();
        }
        $page_title = 'SGQ - Sincronizar Arquivos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_sincronizar.php';
        break;

    case 'siglas':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        $listaSiglas = $siglasCtrl->gerenciarListagem();
        $page_title = 'SGQ - Gestão de Siglas';
        $page_content_file = dirname(__FILE__) . '/views/siglas/siglas_content.php';
        break;

    case 'siglas_cadastrar':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siglasCtrl->salvarNovaSigla($_POST);
        }
        $page_title = 'SGQ - Cadastrar Sigla';
        $page_content_file = dirname(__FILE__) . '/views/siglas/siglas_form.php';
        break;

    case 'siglas_editar':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siglasCtrl->atualizarSigla($_POST);
        }
        $id_sigla = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $formData = $siglasCtrl->exibirFormularioEdicao($id_sigla);
        $sigla = $formData['sigla'];
        $page_title = 'SGQ - Editar Sigla';
        $page_content_file = dirname(__FILE__) . '/views/siglas/siglas_form.php';
        break;

    case 'siglas_excluir':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        $siglasCtrl->excluirSigla(); // O método já lida com o POST e redireciona
        break;

    case 'siglas_importar':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siglasCtrl->importarCSV($_FILES);
        }
        $page_title = 'SGQ - Importar Siglas';
        $page_content_file = dirname(__FILE__) . '/views/siglas/siglas_importar.php';
        break;

    case 'siglas_sincronizar':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultados = $siglasCtrl->atualizarPDFMestre($_FILES);
        }
        $page_title = 'SGQ - Atualizar PDF de Siglas';
        $page_content_file = dirname(__FILE__) . '/views/siglas/siglas_sincronizar.php';
        break;

    case 'siglas_historico_ajax':
        // Esta rota não carrega o layout principal, apenas o conteúdo do histórico.
        require_once dirname(__FILE__) . '/views/siglas/siglas_historico_ajax.php';
        exit(); // Interrompe a execução para não carregar o resto da página
        break;

    case 'siglas_pdf':
        // Rota para gerar o PDF de siglas. Não carrega o layout principal.
        require_once dirname(__FILE__) . '/models/SiglasModel.php';
        require_once dirname(__FILE__) . '/fpdf/fpdf.php';
        require_once dirname(__FILE__) . '/views/siglas/gerar_pdf.php';
        exit();
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