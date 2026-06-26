<?php
if (session_id() == "") {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'SGQ - Sistema de Gestão da Qualidade'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet"> <!-- Corrigido para o caminho correto -->
</head>
<body class="auth-page d-flex flex-column align-items-center justify-content-center">

<div class="text-center mb-4"><img src="../uploads/Logo-Ital.png" alt="Logo ITAL" style="max-width: 200px;"></div> <!-- Corrigido para o caminho correto -->

<?php if (isset($page_content_file) && file_exists($page_content_file)) { include($page_content_file); } ?>

<div class="mt-4 text-center text-muted small">© 2026 Desenvolvido por GTI - Versão 1.0</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>