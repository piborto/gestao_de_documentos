<?php
// Garante que a sessão seja iniciada apenas uma vez, no ponto de entrada principal.
if (session_id() == "") {
    session_start();
}
// Usa dirname(__FILE__) para garantir que o caminho seja absoluto e à prova de falhas no PHP 5.2
require_once dirname(__FILE__) . '/config/conexao.php';

// Executa rotinas de verificação (ex: ativar documentos agendados)
require_once dirname(__FILE__) . '/config/rotinas_diarias.php';

$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'inicio';

switch ($modulo) {
    case 'documentos':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        $dados_listagem = $documentosCtrl->gerenciarListagem();
        
        $listaDocumentos = $dados_listagem['documentos'];
        $listaCategorias = $dados_listagem['listaCategorias'];
        $listaLocais = $dados_listagem['listaLocais'];
        
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
        // O controller lida com o POST e retorna os resultados para a view
        $resultados = $documentosCtrl->sincronizarArquivos();
        $page_title = 'SGQ - Sincronizar Arquivos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_sincronizar.php';
        break;

    case 'documentos_obsoleto':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        // O método já lida com o POST e redireciona
        $documentosCtrl->tornarObsoleto();
        break;

    case 'documentos_excluir':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        // O método já lida com o POST e redireciona
        $documentosCtrl->excluirDocumento();
        break;

    case 'documentos_restaurar':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        // O método já lida com o POST e redireciona
        $documentosCtrl->restaurarDocumento();
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

    case 'siglas_obsoleto':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        $siglasCtrl->tornarObsoleto(); // O método já lida com o POST e redireciona
        break;

    case 'siglas_restaurar':
        require_once dirname(__FILE__) . '/controllers/SiglasController.php';
        $siglasCtrl = new SiglasController($conexao);
        $siglasCtrl->restaurarSigla(); // O método já lida com o POST e redireciona
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

    case 'usuarios':
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        $listaUsuarios = $usuariosCtrl->gerenciarListagem();

        $page_title = 'SGQ - Gestão de Usuários';
        $page_content_file = dirname(__FILE__) . '/views/usuarios/usuarios_content.php';
        break;

    case 'usuarios_cadastrar':
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuariosCtrl->salvarNovoUsuario($_POST);
        }
        $formData = $usuariosCtrl->exibirFormulario();
        $listaPerfis = $formData['listaPerfis'];
        $listaLocais = $formData['listaLocais'];
        $page_title = 'SGQ - Cadastrar Usuário';
        $page_content_file = dirname(__FILE__) . '/views/usuarios/usuarios_form.php';
        break;

    case 'usuarios_editar':
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuariosCtrl->atualizarUsuario($_POST);
        }
        $id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $formData = $usuariosCtrl->exibirFormularioEdicao($id_usuario);
        $usuario = $formData['usuario'];
        $listaPerfis = $formData['listaPerfis'];
        $listaLocais = $formData['listaLocais'];
        $page_title = 'SGQ - Editar Usuário';
        $page_content_file = dirname(__FILE__) . '/views/usuarios/usuarios_form.php';
        break;

    case 'usuarios_status':
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        $usuariosCtrl->alterarStatus(); // Lida com POST e redireciona
        break;

    case 'usuarios_excluir':
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        $usuariosCtrl->excluirUsuario(); // Lida com POST e redireciona
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