<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGQ - Recuperar Senha</title>
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
            <h4 class="fw-bold text-secondary">Recuperar Senha</h4>
            <p class="text-muted small">Informe seu e-mail para receber o link de redefinição.</p>
        </div>

        <?php if (isset($_SESSION['status_redefinicao'])): ?>
            <div class="alert alert-<?php echo isset($_SESSION['status_redefinicao']['tipo']) ? $_SESSION['status_redefinicao']['tipo'] : 'info'; ?>" role="alert">
                <?php echo $_SESSION['status_redefinicao']['mensagem']; unset($_SESSION['status_redefinicao']); ?>
            </div>
        <?php endif; ?>

        <form action="../controllers/AutenticacaoController.php?action=solicitar_redefinicao" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label text-secondary fw-semibold">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Digite seu e-mail de cadastro">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                Enviar Link de Recuperação
            </button>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small">Voltar para o login</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>