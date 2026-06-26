<div class="card login-card p-4">
    <div class="card-body">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-secondary">SGQ</h4>
            <p class="text-muted small">Sistema de Gestão da Qualidade</p>
        </div>

        <?php if (isset($_SESSION['erro_login'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['erro_login']; unset($_SESSION['erro_login']); ?>
            </div>
        <?php endif; ?>

        <form action="../controllers/AutenticacaoController.php" method="POST">
            <div class="mb-3">
                <label for="login" class="form-label text-secondary fw-semibold">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="login" name="login" required placeholder="seuemail@exemplo.com">
                </div>
            </div>
            <div class="mb-4">
                <label for="senha" class="form-label text-secondary fw-semibold">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="senha" name="senha" required placeholder="Sua senha">
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                Entrar <i class="bi bi-box-arrow-in-right ms-1"></i>
            </button>
            <div class="text-center mt-3">
                <a href="esqueci_senha.php" class="text-decoration-none small">Esqueci minha senha</a>
            </div>
        </form>
    </div>
</div>