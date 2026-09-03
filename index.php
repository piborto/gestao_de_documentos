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

if (isset($_SESSION['id_perfil']) && (int)$_SESSION['id_perfil'] === 2 && strpos($modulo, 'siglas') === 0) {
    header('Location: index.php?acesso=negado');
    exit();
}

switch ($modulo) {
    case 'documentos':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        $total_documentos_vigor = $documentosCtrl->getTotalDocumentosEmVigor(); // Movido para cá
        $dados_listagem = $documentosCtrl->gerenciarListagem();
        
        $listaDocumentos = $dados_listagem['documentos'];
        $listaCategorias = $dados_listagem['listaCategorias'];
        $listaLocais = $dados_listagem['listaLocais'];
        
        $page_title = 'SGQ - Gestão de Documentos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_content.php';
        break;

    case 'documentos_campos_ajax':
        if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('sucesso' => false, 'campos' => array()));
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        $documentosCtrl->obterCamposConfiguradosAjax();
        exit();

    case 'visualizar_documento':
        if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        $documentosCtrl->visualizarDocumento(isset($_GET['id']) ? intval($_GET['id']) : 0);
        exit();

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
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) { header('Location: index.php?acesso=negado'); exit(); }
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentosCtrl->importarCSV($_FILES); // O controller cuida do resto
        }

        $page_title = 'SGQ - Importar Documentos';
        $page_content_file = dirname(__FILE__) . '/views/documentos/documentos_importar.php';
        break;

    case 'documentos_sincronizar':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) { header('Location: index.php?acesso=negado'); exit(); }
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
        $total_siglas_vigor = $siglasCtrl->getTotalSiglasEmVigor(); // Movido para cá
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
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 4))) { header('Location: index.php?acesso=negado'); exit(); }
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        $listaUsuarios = $usuariosCtrl->gerenciarListagem();

        $page_title = 'SGQ - Gestão de Usuários';
        $page_content_file = dirname(__FILE__) . '/views/usuarios/usuarios_content.php';
        break;

    case 'usuarios_cadastrar':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 4))) { header('Location: index.php?acesso=negado'); exit(); }
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
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 4))) { header('Location: index.php?acesso=negado'); exit(); }
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
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 4))) { header('Location: index.php?acesso=negado'); exit(); }
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        $usuariosCtrl->alterarStatus(); // Lida com POST e redireciona
        break;

    case 'usuarios_excluir':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 4))) { header('Location: index.php?acesso=negado'); exit(); }
        require_once dirname(__FILE__) . '/controllers/UsuariosController.php';
        $usuariosCtrl = new UsuariosController($conexao);
        $usuariosCtrl->excluirUsuario(); // Lida com POST e redireciona
        break;

    case 'dashboard_unidades':
        // Proteção de Página: Apenas RA-Ital (1) e Auditor (5) podem acessar
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 5))) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $dashboardCtrl = new DocumentosController($conexao);
        $dados_dashboard = $dashboardCtrl->obterDadosDashboardUnidades();
        $page_title = 'SGQ - Dashboard de Unidades';
        $page_content_file = dirname(__FILE__) . '/views/unidades/dashboard_unidades_content.php';
        break;

    case 'unidades':
        // Proteção de Página: Apenas RA-Ital (1) e Admin (4) podem acessar
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/UnidadesController.php';
        $unidadesCtrl = new UnidadesController($conexao);
        $listaUnidades = $unidadesCtrl->listarUnidadesComDocumentos();

        $page_title = 'SGQ - Gestão de Unidades';
        $page_content_file = dirname(__FILE__) . '/views/unidades/unidades_content.php';
        break;

    case 'unidades_configurar_campos':
        // Proteção de Página: Apenas RA-Ital (1) e Admin (4) podem acessar
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/UnidadesController.php';
        $unidadesCtrl = new UnidadesController($conexao);
        // O controller irá lidar com a lógica de buscar e salvar as configurações
        $dados_view = $unidadesCtrl->gerenciarConfiguracaoCampos();

        $page_title = 'SGQ - Configurar Campos por Unidade';
        $page_content_file = dirname(__FILE__) . '/views/unidades/configurar_campos.php';
        break;

    case 'unidades_get_campos_ajax':
        // Rota AJAX para buscar os campos de configuração. Não carrega o layout principal.
        require_once dirname(__FILE__) . '/views/unidades/campos_config_ajax.php';
        exit();
        break;

    case 'configurar_campos':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 2, 4))) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/ConfigCamposController.php';
        $configCtrl = new ConfigCamposController($conexao);
        $dados = $configCtrl->gerenciarConfiguracao();
        $listaUnidades = $dados['listaUnidades'];
        $listaCategorias = $dados['listaCategorias'];
        $configsAtuais = $dados['configsAtuais'];
        $todasConfiguracoes = $dados['todasConfiguracoes'];
        $idLocalSelecionado = $dados['idLocalSelecionado'];
        $idCategoriaSelecionada = $dados['idCategoriaSelecionada'];
        $page_title = 'SGQ - Configuração de Campos e Categorias';
        $page_content_file = dirname(__FILE__) . '/views/config/config_campos_content.php';
        break;

    case 'ftp_explorer':
        if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true || !isset($_SESSION['id_perfil']) || (int)$_SESSION['id_perfil'] !== 4) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/helpers/ftp_helper.php';
        $pastaFtp = isset($_GET['pasta']) ? $_GET['pasta'] : '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acaoFtp = isset($_POST['acao_ftp']) ? $_POST['acao_ftp'] : '';
            $pastaFtpPost = isset($_POST['pasta_ftp']) ? $_POST['pasta_ftp'] : '';
            $resultadoFtp = false;
            if ($acaoFtp === 'criar_pasta') {
                $resultadoFtp = criarPastaFtp($pastaFtpPost, isset($_POST['nome_pasta']) ? $_POST['nome_pasta'] : '');
            } elseif ($acaoFtp === 'apagar') {
                $resultadoFtp = apagarItemFtp(isset($_POST['caminho_ftp']) ? $_POST['caminho_ftp'] : '');
            } elseif ($acaoFtp === 'renomear') {
                $resultadoFtp = renomearItemFtp(isset($_POST['caminho_ftp']) ? $_POST['caminho_ftp'] : '', isset($_POST['novo_nome']) ? $_POST['novo_nome'] : '');
            }
            $pastaFtp = $pastaFtpPost;
            header('Location: index.php?modulo=ftp_explorer&pasta=' . urlencode($pastaFtp) . '&' . ($resultadoFtp ? 'sucesso=1' : 'erro=1'));
            exit();
        }
        $pastaFtp = normalizarCaminhoFtp($pastaFtp);
        if ($pastaFtp === false) {
            header('Location: index.php?modulo=ftp_explorer&erro=1');
            exit();
        }
        $conteudoFtp = listarConteudoFtp($pastaFtp);
        $page_title = 'SGQ - Explorador FTP';
        $page_content_file = dirname(__FILE__) . '/views/config/ftp_explorer.php';
        break;

    case 'alertas':
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $documentosCtrl = new DocumentosController($conexao);
        $notificacoes = $documentosCtrl->obterNotificacoes();
        
        $page_title = 'SGQ - Alertas e Notificações';
        $page_content_file = dirname(__FILE__) . '/views/alertas/alertas_content.php';
        break;

    case 'categorias':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) {
            header('Location: index.php?acesso=negado');
            exit();
        }
        require_once dirname(__FILE__) . '/controllers/CategoriasController.php';
        $categoriasCtrl = new CategoriasController($conexao);
        $listaCategorias = $categoriasCtrl->gerenciarListagem();
        $page_title = 'SGQ - Gestão de Categorias';
        $page_content_file = dirname(__FILE__) . '/views/categorias/categorias_content.php';
        break;

    case 'categorias_cadastrar':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) { header('Location: index.php?acesso=negado'); exit(); }
        require_once dirname(__FILE__) . '/controllers/CategoriasController.php';
        $categoriasCtrl = new CategoriasController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoriasCtrl->salvarNovaCategoria($_POST);
        }
        $page_title = 'SGQ - Cadastrar Categoria';
        $page_content_file = dirname(__FILE__) . '/views/categorias/categorias_form.php';
        break;

    case 'categorias_editar':
        if (!isset($_SESSION['id_perfil']) || !in_array($_SESSION['id_perfil'], array(1, 4))) { header('Location: index.php?acesso=negado'); exit(); }
        require_once dirname(__FILE__) . '/controllers/CategoriasController.php';
        $categoriasCtrl = new CategoriasController($conexao);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoriasCtrl->atualizarCategoria($_POST);
        }
        $id_categoria = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $categoria = $categoriasCtrl->exibirFormularioEdicao($id_categoria);
        $page_title = 'SGQ - Editar Categoria';
        $page_content_file = dirname(__FILE__) . '/views/categorias/categorias_form.php';
        break;

    case 'inicio':
    default:
        require_once dirname(__FILE__) . '/controllers/DocumentosController.php';
        $notificacoesCtrl = new DocumentosController($conexao);
        $notificacoes = $notificacoesCtrl->obterNotificacoes();

        $page_title = 'SGQ - Painel Principal';
        $page_content_file = dirname(__FILE__) . '/views/index_content.php';
}

// Calcula o total de notificações para exibir no badge do menu
$total_notificacoes = 0;
if (isset($notificacoes)) {
    $total_notificacoes = count($notificacoes['vencidos']) + count($notificacoes['proximos']) + count($notificacoes['agendados']);
}

// Inclui o layout principal de forma segura
require_once dirname(__FILE__) . '/views/layout/main.php';
?>