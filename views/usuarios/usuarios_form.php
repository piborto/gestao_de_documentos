<?php
$is_edit = isset($usuario) && !empty($usuario);
$form_action = $is_edit ? 'usuarios_editar' : 'usuarios_cadastrar';
$page_subtitle = $is_edit ? 'Editar Usuário' : 'Cadastrar Usuário';
?>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-person-fill-gear me-2 text-primary"></i><?php echo $page_subtitle; ?></h3>
        <p class="text-muted">Preencha os campos abaixo para <?php echo $is_edit ? 'atualizar o' : 'adicionar um novo'; ?> usuário.</p>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=usuarios" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Voltar para a Lista</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="index.php?modulo=<?php echo $form_action; ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id_usuario" value="<?php echo $is_edit ? htmlspecialchars($usuario['id_usuario_qualidade']) : ''; ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="nome_usuario" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome_usuario" name="nome_usuario" value="<?php echo $is_edit ? htmlspecialchars($usuario['nome_usuario']) : ''; ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="login_usuario" class="form-label">Nome Usuário <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="login_usuario" name="login_usuario" value="<?php echo $is_edit ? htmlspecialchars($usuario['login_usuario']) : ''; ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="email_usuario" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email_usuario" name="email_usuario" value="<?php echo $is_edit ? htmlspecialchars($usuario['email_usuario']) : ''; ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="senha_usuario" class="form-label">Senha <?php if (!$is_edit) echo '<span class="text-danger">*</span>'; ?></label>
                    <input type="password" class="form-control" id="senha_usuario" name="senha_usuario" <?php echo !$is_edit ? 'required' : ''; ?>>
                    <?php if ($is_edit): ?>
                        <div class="form-text text-muted small"><i class="bi bi-info-circle"></i> Deixe em branco para não alterar a senha.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="id_perfil" class="form-label">Perfil de Acesso <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_perfil" name="id_perfil" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($listaPerfis as $perfil): ?>
                            <option value="<?php echo $perfil['id_perfil']; ?>" <?php echo ($is_edit && $perfil['id_perfil'] == $usuario['id_perfil']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($perfil['nome_perfil']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="id_local" class="form-label">Unidade / Local</label>
                    <select class="form-select" id="id_local" name="id_local">
                        <option value="">Nenhum / Geral</option>
                        <?php foreach ($listaLocais as $local): ?>
                            <option value="<?php echo $local['id_local']; ?>" <?php echo ($is_edit && isset($usuario['id_local']) && $local['id_local'] == $usuario['id_local']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($local['nome_local']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="status_usuario" name="status_usuario" value="1" <?php echo (!$is_edit || ($is_edit && $usuario['status_usuario'] == 1)) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="status_usuario">Usuário Ativo</label>
                    </div>
                </div>

                <?php if ($is_edit): ?>
                <div class="col-12">
                    <hr>
                    <label for="justificativa" class="form-label fw-bold text-danger">Justificativa da Alteração <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="justificativa" name="justificativa" rows="3" required placeholder="Ex: Atualização de perfil, correção de e-mail, etc."></textarea>
                </div>
                <?php endif; ?>

                <div class="col-12 text-end">
                    <hr>
                    <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-circle me-2"></i>Salvar Usuário</button>
                </div>
            </div>
        </form>
    </div>
</div>