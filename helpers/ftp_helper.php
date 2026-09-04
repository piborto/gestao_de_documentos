<?php

if (!defined('FTP_HOST')) {
    $ftpConfig = array();
    $ftpConfigPath = dirname(__FILE__) . '/../config/ftp.php';
    if (is_file($ftpConfigPath)) {
        $configuracaoLocal = require $ftpConfigPath;
        if (is_array($configuracaoLocal)) {
            $ftpConfig = $configuracaoLocal;
        }
    }

    $ftpHost = getenv('FTP_HOST');
    $ftpUser = getenv('FTP_USER');
    $ftpPass = getenv('FTP_PASS');
    $ftpBasePath = getenv('FTP_BASE_PATH');
    $ftpDebug = getenv('FTP_DEBUG');

    define('FTP_HOST', ($ftpHost !== false && trim($ftpHost) !== '') ? $ftpHost : (isset($ftpConfig['host']) ? $ftpConfig['host'] : 'ftp.example.invalid'));
    define('FTP_USER', ($ftpUser !== false && trim($ftpUser) !== '') ? $ftpUser : (isset($ftpConfig['user']) ? $ftpConfig['user'] : 'change-me'));
    define('FTP_PASS', ($ftpPass !== false) ? $ftpPass : (isset($ftpConfig['pass']) ? $ftpConfig['pass'] : 'change-me'));
    define('FTP_BASE_PATH', ($ftpBasePath !== false && trim($ftpBasePath) !== '') ? $ftpBasePath : (isset($ftpConfig['base_path']) ? $ftpConfig['base_path'] : 'documents/'));
    define('FTP_DEBUG', ($ftpDebug !== false) ? filter_var($ftpDebug, FILTER_VALIDATE_BOOLEAN) : (isset($ftpConfig['debug']) ? (bool)$ftpConfig['debug'] : false));
}

function registrarDebugFtp($mensagem) {
    if (FTP_DEBUG) {
        $linha = date('Y-m-d H:i:s') . ' - ' . $mensagem . PHP_EOL;
        @file_put_contents(dirname(__FILE__) . '/ftp_debug.log', $linha, FILE_APPEND);
    }
}

function criarPastasFtpRecursivo($conexaoFtp, $caminhoCompleto) {
    $caminhoCompleto = trim(str_replace('\\', '/', (string)$caminhoCompleto), '/');
    if ($caminhoCompleto === '' || preg_match('/(^|\/)(\.\.?)(\/|$)/', $caminhoCompleto)) {
        registrarDebugFtp('FALHA: caminho de pastas FTP invalido.');
        return false;
    }

    $partes = explode('/', $caminhoCompleto);

    foreach ($partes as $parte) {
        if ($parte === '') {
            continue;
        }

        if (!@ftp_chdir($conexaoFtp, $parte)) {
            registrarDebugFtp('Nivel FTP ausente, criando: ' . $parte . '.');
            if (@ftp_mkdir($conexaoFtp, $parte) === false || !@ftp_chdir($conexaoFtp, $parte)) {
                registrarDebugFtp('FALHA: nao foi possivel criar ou acessar o nivel FTP ' . $parte . '.');
                return false;
            }
            registrarDebugFtp('Pasta FTP criada: ' . $parte . '.');
        }
    }

    return true;
}

function montarCaminhoDestinoFtp($contexto) {
    $siglaCategoria = isset($contexto['sigla_categoria']) ? $contexto['sigla_categoria'] : '';
    $escopo = isset($contexto['escopo_categoria']) ? $contexto['escopo_categoria'] : '';
    $nomeLocal = isset($contexto['nome_local']) ? $contexto['nome_local'] : '';

    $siglaCategoria = preg_replace('/[^a-zA-Z0-9_-]/', '', $siglaCategoria);
    if ($siglaCategoria === '') {
        return false;
    }

    if (strtoupper(trim($escopo)) === 'SGQ UNIDADE') {
        $nomeLocal = strtolower(strtr($nomeLocal, array(
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c', 'Á' => 'a', 'À' => 'a', 'Ã' => 'a',
            'Â' => 'a', 'Ä' => 'a', 'É' => 'e', 'Ê' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ú' => 'u', 'Ç' => 'c'
        )));
        $nomeLocal = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $nomeLocal));
        if ($nomeLocal === '') {
            registrarDebugFtp('FALHA: nome da unidade ausente para destino FTP.');
            return false;
        }
        return $nomeLocal . '/' . $siglaCategoria . '/';
    }

    return 'sgq_ital/' . $siglaCategoria . '/';
}

/**
 * Envia um arquivo local para a pasta base do servidor FTP.
 */
function enviarArquivoFtp($arquivoLocal, $nomeDestino, $contexto = array()) {
    $pastaDestino = montarCaminhoDestinoFtp($contexto);
    if ($pastaDestino === false) {
        registrarDebugFtp('FALHA: contexto FTP sem sigla de categoria valida.');
        return false;
    }

    registrarDebugFtp('Iniciando envio: ' . $arquivoLocal . ' -> ' . FTP_BASE_PATH . $pastaDestino . $nomeDestino);

    if (!function_exists('ftp_connect') || !is_file($arquivoLocal) || basename($nomeDestino) !== $nomeDestino) {
        registrarDebugFtp('FALHA: extensao FTP ausente, arquivo local inexistente ou nome de destino invalido.');
        return false;
    }

    $conexaoFtp = ftp_connect(FTP_HOST, 21, 30);
    if ($conexaoFtp === false) {
        registrarDebugFtp('FALHA: nao foi possivel conectar ao servidor FTP.');
        return false;
    }
    registrarDebugFtp('Conexao FTP estabelecida.');

    $autenticado = ftp_login($conexaoFtp, FTP_USER, FTP_PASS);
    if (!$autenticado) {
        registrarDebugFtp('FALHA: autenticacao FTP recusada.');
        ftp_close($conexaoFtp);
        return false;
    }
    registrarDebugFtp('Autenticacao FTP realizada.');

    // O modo passivo deve ser ativado apos o login e antes de qualquer operacao de dados.
    if (!@ftp_pasv($conexaoFtp, true)) {
        registrarDebugFtp('FALHA: nao foi possivel ativar o modo passivo.');
        ftp_close($conexaoFtp);
        return false;
    }
    registrarDebugFtp('Modo passivo ativado.');

    if (!criarPastasFtpRecursivo($conexaoFtp, FTP_BASE_PATH . $pastaDestino)) {
        ftp_close($conexaoFtp);
        return false;
    }

    $enviado = ftp_put($conexaoFtp, $nomeDestino, $arquivoLocal, FTP_BINARY);
    registrarDebugFtp($enviado ? 'SUCESSO: arquivo enviado via FTP.' : 'FALHA: ftp_put nao enviou o arquivo.');
    ftp_close($conexaoFtp);
    registrarDebugFtp('Conexao FTP fechada.');

    return $enviado ? FTP_BASE_PATH . $pastaDestino . $nomeDestino : false;
}

function enviarDocumentoFtpParaNavegador($caminhoArquivo, $contexto = array()) {
    $caminhoInformado = normalizarCaminhoFtp($caminhoArquivo);
    if ($caminhoInformado === false || $caminhoInformado === '') {
        registrarDebugFtp('FALHA: caminho de documento FTP invalido para leitura.');
        return false;
    }

    $nomeArquivo = basename($caminhoInformado);
    $conexaoFtp = conectarFtpAdministrativo();
    if ($conexaoFtp === false) return false;

    $caminhosPossiveis = array($caminhoInformado);
    $idLocal = isset($contexto['id_local']) ? (int)$contexto['id_local'] : 0;
    $siglaCategoria = isset($contexto['sigla_categoria']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $contexto['sigla_categoria']) : '';
    $nomeLocal = isset($contexto['nome_local']) ? strtolower(strtr($contexto['nome_local'], array(
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c', 'Á' => 'a', 'À' => 'a', 'Ã' => 'a',
        'Â' => 'a', 'Ä' => 'a', 'É' => 'e', 'Ê' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ú' => 'u', 'Ç' => 'c'
    ))) : '';
    $nomeLocal = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $nomeLocal));
    $ehUnidade = isset($contexto['escopo_categoria']) && strtoupper(trim($contexto['escopo_categoria'])) === 'SGQ UNIDADE';
    if ($ehUnidade) {
        if ($nomeLocal === '' || $siglaCategoria === '') {
            registrarDebugFtp('FALHA: nome da unidade ou sigla ausente para leitura FTP.');
            ftp_close($conexaoFtp);
            return false;
        }
        $caminhosPossiveis = array($nomeLocal . '/' . $siglaCategoria . '/' . $nomeArquivo);
    } elseif (!$ehUnidade) {
        $caminhosPossiveis = array($caminhoInformado);
    }
    $caminhosPossiveis = array_unique($caminhosPossiveis);

    $caminhoFtp = false;
    foreach ($caminhosPossiveis as $caminhoPossivel) {
        $tamanhoFtp = @ftp_size($conexaoFtp, $caminhoPossivel);
        $arquivoExiste = $tamanhoFtp >= 0;
        if (!$arquivoExiste) {
            $diretorioArquivo = dirname($caminhoPossivel);
            $itensDiretorio = @ftp_nlist($conexaoFtp, $diretorioArquivo === '.' ? '.' : $diretorioArquivo);
            if ($itensDiretorio !== false) {
                foreach ($itensDiretorio as $itemDiretorio) {
                    if (basename(str_replace('\\', '/', $itemDiretorio)) === $nomeArquivo) {
                        $arquivoExiste = true;
                        break;
                    }
                }
            }
        }
        if ($arquivoExiste) {
            $caminhoFtp = $caminhoPossivel;
            registrarDebugFtp('Arquivo FTP localizado: ' . FTP_BASE_PATH . $caminhoFtp . '.');
            break;
        }
        registrarDebugFtp('Arquivo FTP nao localizado no caminho: ' . FTP_BASE_PATH . $caminhoPossivel . '.');
    }
    if ($caminhoFtp === false) {
        if ($ehUnidade) {
            $pastaCategoria = $nomeLocal . '/' . $siglaCategoria;
            $itensCategoria = @ftp_nlist($conexaoFtp, $pastaCategoria);
            $codigoBusca = isset($contexto['codigo_documento']) ? strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $contexto['codigo_documento'])) : '';
            $revisaoBusca = isset($contexto['revisao_documento']) ? (int)$contexto['revisao_documento'] : 0;
            if ($itensCategoria !== false && $codigoBusca !== '') {
                foreach ($itensCategoria as $itemCategoria) {
                    $nomeItem = basename(str_replace('\\', '/', $itemCategoria));
                    $nomeItemNormalizado = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($nomeItem, PATHINFO_FILENAME)));
                    $temCodigo = strpos($nomeItemNormalizado, $codigoBusca) !== false;
                    $temRevisao = preg_match('/REV0*' . $revisaoBusca . '/', $nomeItemNormalizado);
                    if ($temCodigo && $temRevisao) {
                        $caminhoFtp = $pastaCategoria . '/' . $nomeItem;
                        $nomeArquivo = $nomeItem;
                        registrarDebugFtp('Arquivo FTP localizado por codigo/revisao: ' . FTP_BASE_PATH . $caminhoFtp . '.');
                        break;
                    }
                }
            }
        }
        if ($caminhoFtp === false) {
            ftp_close($conexaoFtp);
            return false;
        }
    }

    $arquivoTemporario = tmpfile();
    if ($arquivoTemporario === false || !@ftp_fget($conexaoFtp, $arquivoTemporario, $caminhoFtp, FTP_BINARY)) {
        registrarDebugFtp('FALHA: nao foi possivel ler o documento FTP ' . FTP_BASE_PATH . $caminhoFtp . '.');
        if ($arquivoTemporario !== false) fclose($arquivoTemporario);
        ftp_close($conexaoFtp);
        return false;
    }

    $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
    $tipos = array(
        'pdf' => 'application/pdf', 'txt' => 'text/plain', 'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
        'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
    );
    $tipo = isset($tipos[$extensao]) ? $tipos[$extensao] : 'application/octet-stream';
    $tamanho = fstat($arquivoTemporario);

    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: ' . $tipo);
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $nomeArquivo) . '"');
    if ($tamanho && isset($tamanho['size'])) header('Content-Length: ' . $tamanho['size']);
    header('X-Content-Type-Options: nosniff');
    rewind($arquivoTemporario);
    fpassthru($arquivoTemporario);
    fclose($arquivoTemporario);
    ftp_close($conexaoFtp);
    return true;
}

function normalizarCaminhoFtp($caminho) {
    $caminho = trim(str_replace('\\', '/', (string)$caminho), '/');
    $raizFtp = trim(FTP_BASE_PATH, '/');
    if ($caminho === $raizFtp) {
        $caminho = '';
    } elseif (strpos($caminho, $raizFtp . '/') === 0) {
        $caminho = substr($caminho, strlen($raizFtp) + 1);
    }
    $partes = $caminho === '' ? array() : explode('/', $caminho);
    $resultado = array();

    foreach ($partes as $parte) {
        if ($parte === '' || $parte === '.') {
            continue;
        }
        if ($parte === '..' || preg_match('/[^a-zA-Z0-9._-]/', $parte)) {
            return false;
        }
        $resultado[] = $parte;
    }

    return implode('/', $resultado);
}

function conectarFtpAdministrativo() {
    if (!function_exists('ftp_connect')) {
        registrarDebugFtp('FALHA: extensao FTP ausente na operacao administrativa.');
        return false;
    }

    $conexaoFtp = ftp_connect(FTP_HOST, 21, 30);
    if ($conexaoFtp === false || !ftp_login($conexaoFtp, FTP_USER, FTP_PASS) || !ftp_pasv($conexaoFtp, true)) {
        if ($conexaoFtp !== false) ftp_close($conexaoFtp);
        registrarDebugFtp('FALHA: conexao administrativa FTP nao estabelecida.');
        return false;
    }

    if (!ftp_chdir($conexaoFtp, trim(FTP_BASE_PATH, '/'))) {
        ftp_close($conexaoFtp);
        registrarDebugFtp('FALHA: pasta base FTP inexistente.');
        return false;
    }

    return $conexaoFtp;
}

function listarConteudoFtpDiretorio($conexaoFtp, $caminhoAtual) {
    $conteudo = array();
    $linhas = @ftp_rawlist($conexaoFtp, '.');

    if ($linhas === false) {
        return $conteudo;
    }

    foreach ($linhas as $linha) {
        $partes = preg_split('/\s+/', trim($linha), 9);
        if (count($partes) < 9 || $partes[8] === '.' || $partes[8] === '..') {
            continue;
        }

        $nome = $partes[8];
        $ehDiretorio = substr($partes[0], 0, 1) === 'd';
        $item = array(
            'nome' => $nome,
            'caminho' => trim($caminhoAtual . '/' . $nome, '/'),
            'tipo' => $ehDiretorio ? 'diretorio' : 'arquivo',
            'tamanho' => $ehDiretorio ? null : (int)$partes[4],
            'data' => $partes[5] . ' ' . $partes[6] . ' ' . $partes[7]
        );
        $conteudo[] = $item;

    }
    return $conteudo;
}

function listarConteudoFtp($diretorio = FTP_BASE_PATH) {
    $diretorio = normalizarCaminhoFtp($diretorio);
    if ($diretorio === false) return false;
    $conexaoFtp = conectarFtpAdministrativo();
    if ($conexaoFtp === false || ($diretorio !== '' && !@ftp_chdir($conexaoFtp, $diretorio))) {
        if ($conexaoFtp !== false) ftp_close($conexaoFtp);
        registrarDebugFtp('FALHA: diretorio FTP inexistente: ' . $diretorio);
        return false;
    }

    $conteudo = listarConteudoFtpDiretorio($conexaoFtp, $diretorio === '' ? trim(FTP_BASE_PATH, '/') : $diretorio);
    ftp_close($conexaoFtp);
    registrarDebugFtp('Listagem FTP concluida: ' . count($conteudo) . ' itens.');

    return $conteudo;
}

function excluirConteudoFtp($conexaoFtp, $nome) {
    $linhas = @ftp_rawlist($conexaoFtp, $nome);
    if ($linhas !== false) {
        $diretorioAtual = ftp_pwd($conexaoFtp);
        if (@ftp_chdir($conexaoFtp, $nome)) {
            foreach (listarConteudoFtpDiretorio($conexaoFtp, $nome) as $item) {
                excluirConteudoFtp($conexaoFtp, $item['nome']);
            }
            ftp_chdir($conexaoFtp, $diretorioAtual);
            return @ftp_rmdir($conexaoFtp, $nome);
        }
    }
    return @ftp_delete($conexaoFtp, $nome);
}

function apagarItemFtp($caminho) {
    $caminho = normalizarCaminhoFtp($caminho);
    if ($caminho === false || $caminho === '') return false;
    $conexaoFtp = conectarFtpAdministrativo();
    if ($conexaoFtp === false) return false;
    $partes = explode('/', $caminho);
    $nome = array_pop($partes);
    if (!empty($partes) && !ftp_chdir($conexaoFtp, implode('/', $partes))) {
        ftp_close($conexaoFtp);
        return false;
    }
    $resultado = excluirConteudoFtp($conexaoFtp, $nome);
    ftp_close($conexaoFtp);
    registrarDebugFtp($resultado ? 'SUCESSO: item FTP removido: ' . $caminho : 'FALHA: item FTP nao removido: ' . $caminho);
    return $resultado;
}

function criarPastaFtp($diretorio, $nomePasta) {
    registrarDebugFtp('Iniciando criacao de pasta: diretorio=' . $diretorio . ', nome=' . $nomePasta);
    $diretorio = normalizarCaminhoFtp($diretorio);
    $nomePasta = normalizarCaminhoFtp($nomePasta);
    if ($diretorio === false || $nomePasta === false || strpos($nomePasta, '/') !== false || $nomePasta === '') {
        registrarDebugFtp('FALHA: diretorio ou nome de pasta invalido.');
        return false;
    }
    $conexaoFtp = conectarFtpAdministrativo();
    if ($conexaoFtp === false) return false;
    $caminhoNovo = ($diretorio === '' ? '' : $diretorio . '/') . $nomePasta;
    if (@ftp_mkdir($conexaoFtp, $caminhoNovo) === false) {
        registrarDebugFtp('FALHA: ftp_mkdir recusou o caminho ' . $caminhoNovo . ' no diretorio atual ' . ftp_pwd($conexaoFtp) . '.');
        ftp_close($conexaoFtp);
        return false;
    }
    ftp_close($conexaoFtp);
    registrarDebugFtp('SUCESSO: pasta FTP criada: ' . $caminhoNovo . '.');
    return true;
}

function renomearItemFtp($caminho, $novoNome) {
    $caminho = normalizarCaminhoFtp($caminho);
    $novoNome = normalizarCaminhoFtp($novoNome);
    if ($caminho === false || $novoNome === false || strpos($novoNome, '/') !== false || $caminho === '' || $novoNome === '') return false;
    $conexaoFtp = conectarFtpAdministrativo();
    if ($conexaoFtp === false) return false;
    $resultado = @ftp_rename($conexaoFtp, $caminho, dirname($caminho) === '.' ? $novoNome : dirname($caminho) . '/' . $novoNome);
    ftp_close($conexaoFtp);
    return $resultado;
}

// Executa um teste visual apenas quando este arquivo e acessado diretamente.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    header('Content-Type: text/plain; charset=utf-8');

    if (!function_exists('ftp_connect')) {
        registrarDebugFtp('TESTE: extensao FTP do PHP nao esta habilitada.');
        echo "FALHA: a extensao FTP do PHP nao esta habilitada.\n";
        exit();
    }

    echo "Testando conexao com " . FTP_HOST . "...\n";
    $conexaoFtp = ftp_connect(FTP_HOST, 21, 30);
    if ($conexaoFtp === false) {
        registrarDebugFtp('TESTE: falha ao conectar ao servidor FTP.');
        echo "FALHA: nao foi possivel conectar ao servidor FTP.\n";
        exit();
    }
    echo "OK: conexao estabelecida.\n";

    if (!ftp_login($conexaoFtp, FTP_USER, FTP_PASS)) {
        registrarDebugFtp('TESTE: falha na autenticacao FTP.');
        ftp_close($conexaoFtp);
        echo "FALHA: usuario ou senha recusados.\n";
        exit();
    }
    echo "OK: autenticacao realizada.\n";

    if (!ftp_pasv($conexaoFtp, true)) {
        registrarDebugFtp('TESTE: falha ao ativar o modo passivo.');
        ftp_close($conexaoFtp);
        echo "FALHA: nao foi possivel ativar o modo passivo.\n";
        exit();
    }

    registrarDebugFtp('TESTE: conexao, autenticacao e modo passivo funcionando.');
    ftp_close($conexaoFtp);
    echo "OK: modo passivo ativado e conexao fechada corretamente.\n";
}
?>