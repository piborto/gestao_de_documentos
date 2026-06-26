<?php
session_start();

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['status_redefinicao'] = array('tipo' => 'danger', 'mensagem' => 'Token inválido ou ausente.');
    header("Location: esqueci_senha.php");
    exit();
}
$token = $_GET['token'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGQ - Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-top: 5px solid #0d6efd;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
        }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="card-body">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-secondary">Crie uma Nova Senha</h4>
        </div>

        <?php if (isset($_SESSION['status_redefinicao'])): ?>
            <div class="alert alert-<?php echo $_SESSION['status_redefinicao']['tipo']; ?>" role="alert">
                <?php echo $_SESSION['status_redefinicao']['mensagem']; unset($_SESSION['status_redefinicao']); ?>
            </div>
        <?php endif; ?>

        <form action="../controllers/AutenticacaoController.php?action=redefinir_senha" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="mb-3">
                <label for="nova_senha" class="form-label text-secondary fw-semibold">Nova Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" required placeholder="Digite a nova senha">
                </div>
            </div>

            <div class="mb-4">
                <label for="confirma_senha" class="form-label text-secondary fw-semibold">Confirmar Nova Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required placeholder="Confirme a nova senha">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Redefinir Senha</button>
        </form>
    </div>
</div>

</body>
</html>