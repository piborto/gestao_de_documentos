<?php
if (session_id() == "") {
    session_start();
}
require_once dirname(__FILE__) . '/../config/conexao.php';
require_once dirname(__FILE__) . '/../classes/class.phpmailer.php';

class AutenticacaoController {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    public function logar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Limpa qualquer resquício de sessão de erro anterior para garantir uma tentativa limpa
            if (isset($_SESSION['erro_login'])) {
                unset($_SESSION['erro_login']);
            }

            $login = isset($_POST['login']) ? trim($_POST['login']) : '';
            $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';

            if (empty($login) || empty($senha)) {
                $_SESSION['erro_login'] = "Por favor, preencha todos os campos.";
                header("Location: ../views/login/login.php");
                exit();
            }

            $sql = "SELECT u.*, p.nome_perfil, l.nome_local 
                    FROM t_usuario_qualidade u
                    INNER JOIN t_perfil p ON u.id_perfil = p.id_perfil
                    LEFT JOIN t_local l ON u.id_local = l.id_local
                    WHERE u.email_usuario = :login AND u.status_usuario = 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array(':login' => $login));
            $usuario = $stmt->fetch();

            // Validação da senha com o mesmo hash usado no banco (SHA-256)
            if ($usuario && (hash('sha256', $senha) === $usuario['senha_usuario'])) {
                unset($usuario['senha_usuario']);

                $_SESSION['usuario_logado'] = true;
                $_SESSION['usuario_id']     = $usuario['id_usuario_qualidade']; // Linha adicionada
                $_SESSION['usuario_nome']   = utf8_encode($usuario['nome_usuario']);
                $_SESSION['usuario_login']  = $usuario['login_usuario'];
                $_SESSION['usuario_perfil'] = $usuario['nome_perfil'];
                $_SESSION['id_perfil']      = $usuario['id_perfil'];
                $_SESSION['id_local']       = $usuario['id_local'];
                $_SESSION['unidade_nome']   = (isset($usuario['nome_local']) && $usuario['nome_local'] !== null) ? $usuario['nome_local'] : 'Geral/RA-Ital';

                if (isset($_SESSION['erro_login'])) {
                    unset($_SESSION['erro_login']);
                }

                header("Location: ../index.php?modulo=inicio");
                exit();
            } else {
                $_SESSION['erro_login'] = "Usuário ou senha incorretos.";
                header("Location: ../views/login/login.php");
                exit();
            }
        }
    }

    public function solicitarRedefinicao() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) { // Validação de segurança
            header("Location: ../views/login/login.php");
            exit();
        }

        $email = trim($_POST['email']);

        $sql = "SELECT id_usuario_qualidade, nome_usuario FROM t_usuario_qualidade WHERE email_usuario = :email AND status_usuario = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(':email' => $email));
        $usuario = $stmt->fetch();

        // Fecha o cursor para que a próxima consulta possa ser executada.
        $stmt->closeCursor();

        if (!$usuario) {
            $_SESSION['status_redefinicao'] = array('tipo' => 'warning', 'mensagem' => 'Se o e-mail estiver em nossa base, um link de recuperacao foi enviado.');
            header("Location: ../views/login/esqueci_senha.php");
            exit();
        }

        $token = bin2hex($this->generateRandomToken(32)); // Gera um token seguro
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora

        $sql_update = "UPDATE t_usuario_qualidade SET reset_token = :token, reset_token_expires_at = :expires WHERE id_usuario_qualidade = :id";
        $stmt_update = $this->db->prepare($sql_update);
        $stmt_update->execute(array(
            ':token' => $token,
            ':expires' => $expires_at,
            ':id' => $usuario['id_usuario_qualidade']
        ));

        // Envio do e-mail
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        // Assumindo configuração para usar a função mail() do PHP.
        // Se precisar de SMTP, descomente e configure as linhas abaixo.
        // $mail->isSMTP();
        // $mail->Host = 'smtp.example.com';
        // $mail->SMTPAuth = true;
        // $mail->Username = 'user@example.com';
        // $mail->Password = 'secret';
        // $mail->SMTPSecure = 'tls';
        // $mail->Port = 587;

        $mailFrom = getenv('MAIL_FROM_ADDRESS');
        $mailFromName = getenv('MAIL_FROM_NAME');
        $mail->setFrom(
            ($mailFrom !== false && trim($mailFrom) !== '') ? $mailFrom : 'no-reply@example.invalid',
            ($mailFromName !== false && trim($mailFromName) !== '') ? $mailFromName : 'Document Management'
        );
        $mail->addAddress($email, $usuario['nome_usuario']);
        $mail->Subject = 'Redefinição de Senha - Sistema de Documentos';

        $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/../views/login/redefinir_senha.php?token=" . $token;
        $mail->Body = "Olá, " . $usuario['nome_usuario'] . ".\n\n";
        $mail->Body .= "Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para criar uma nova senha:\n";
        $mail->Body .= $link . "\n\n";
        $mail->Body .= "Se você não solicitou isso, por favor, ignore este e-mail.\n";
        $mail->Body .= "Este link é válido por 1 hora.\n\n";
        $mail->Body .= "Atenciosamente,\nEquipe de Documentos";

        if (!$mail->send()) {
            // Em um ambiente de produção, seria bom logar o erro: error_log('Mailer Error: ' . $mail->ErrorInfo);
            $_SESSION['status_redefinicao'] = array('tipo' => 'danger', 'mensagem' => 'Nao foi possivel enviar o e-mail. Tente novamente mais tarde.');
        } else {
            $_SESSION['status_redefinicao'] = array('tipo' => 'success', 'mensagem' => 'Se o e-mail estiver em nossa base, um link de recuperacao foi enviado.');
        }

        header("Location: ../views/login/esqueci_senha.php");
        exit();
    }

    public function redefinirSenha() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/login/login.php");
            exit();
        }

        $token = $_POST['token'];
        $nova_senha = $_POST['nova_senha'];
        $confirma_senha = $_POST['confirma_senha'];

        if ($nova_senha !== $confirma_senha) {
            $_SESSION['status_redefinicao'] = array('tipo' => 'danger', 'mensagem' => 'As senhas nao coincidem.');
            header("Location: ../views/login/redefinir_senha.php?token=" . $token);
            exit();
        }

        $sql = "SELECT id_usuario_qualidade FROM t_usuario_qualidade WHERE reset_token = :token AND reset_token_expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(':token' => $token));
        $usuario = $stmt->fetch();

        // Fecha o cursor para que a próxima consulta possa ser executada.
        $stmt->closeCursor();

        if (!$usuario) {
            $_SESSION['status_redefinicao'] = array('tipo' => 'danger', 'mensagem' => 'Token invalido ou expirado. Por favor, solicite um novo link.');
            header("Location: ../views/login/esqueci_senha.php");
            exit();
        }

        $nova_senha_hash = hash('sha256', $nova_senha);
        $sql_update = "UPDATE t_usuario_qualidade SET senha_usuario = :senha, reset_token = NULL, reset_token_expires_at = NULL WHERE id_usuario_qualidade = :id";
        $stmt_update = $this->db->prepare($sql_update);
        $stmt_update->execute(array(':senha' => $nova_senha_hash, ':id' => $usuario['id_usuario_qualidade']));

        $_SESSION['erro_login'] = "Sua senha foi redefinida com sucesso! Você já pode fazer o login."; // Usando a session de erro para mostrar msg de sucesso no login
        header("Location: ../views/login/login.php");
        exit();
    }

    private function generateRandomToken($length = 32) {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }
        // Fallback para versões mais antigas do PHP
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= chr(mt_rand(0, 255));
        }
        return $token;
    }

    public function deslogar() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: ../views/login/login.php");
        exit();
    }
}

$auth = new AutenticacaoController($conexao);
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth->deslogar();
} elseif (isset($_GET['action']) && $_GET['action'] === 'solicitar_redefinicao') {
    $auth->solicitarRedefinicao();
} elseif (isset($_GET['action']) && $_GET['action'] === 'redefinir_senha') {
    $auth->redefinirSenha();
} else {
    $auth->logar();
}