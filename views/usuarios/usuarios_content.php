<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-people me-2 text-primary"></i>Gestão de Usuários</h3>
    </div>
    <div class="col-auto">
        <a href="index.php?modulo=usuarios_cadastrar" class="btn btn-primary fw-bold"><i class="bi bi-plus-circle me-2"></i>Cadastrar Usuário</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="thead-custom">
                    <tr>
                        <th style="width: 30%;">Nome</th>
                        <th style="width: 25%;">E-mail / Login</th>
                        <th style="width: 15%;">Perfil</th>
                        <th style="width: 15%;">Unidade / Local</th>
                        <th class="text-center" style="width: 5%;">Status</th>
                        <th class="text-end pe-3" style="width: 10%;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaUsuarios)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaUsuarios as $usuario): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($usuario['nome_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nome_perfil']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nome_local']); ?></td>
                                <td class="text-center">
                                    <?php if ($usuario['status_usuario'] == 1): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?modulo=usuarios_editar&id=<?php echo $usuario['id_usuario_qualidade']; ?>" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <?php if ($usuario['status_usuario'] == 1): ?>
                                            <button type="button" class="btn btn-outline-warning" title="Desativar" data-bs-toggle="modal" data-bs-target="#modalStatus" data-id="<?php echo $usuario['id_usuario_qualidade']; ?>" data-nome="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>" data-status="0"><i class="bi bi-person-x"></i></button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success" title="Ativar" data-bs-toggle="modal" data-bs-target="#modalStatus" data-id="<?php echo $usuario['id_usuario_qualidade']; ?>" data-nome="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>" data-status="1"><i class="bi bi-person-check"></i></button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?php echo $usuario['id_usuario_qualidade']; ?>" data-nome="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div class="modal fade" id="modalStatus" tabindex="-1" aria-labelledby="modalStatusLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?modulo=usuarios_status">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStatusLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalStatusText"></p>
                    <div id="justificativa-desativar" class="mb-3" style="display: none;">
                        <label for="justificativa_status" class="form-label">Justificativa <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="justificativa_status" name="justificativa" rows="3" placeholder="Ex: Fim de contrato, mudança de função, etc."></textarea>
                    </div>
                    <input type="hidden" name="id_usuario" id="id-usuario-status">
                    <input type="hidden" name="status" id="novo-status-usuario">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn" id="btn-confirmar-status">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?modulo=usuarios_excluir">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalExcluirLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir permanentemente o usuário <strong id="nome-usuario-excluir"></strong>?</p>
                    <p class="text-danger small"><strong>Atenção:</strong> Esta ação é irreversível e removerá todos os dados do usuário do sistema.</p>
                    <input type="hidden" name="id_usuario" id="id-usuario-excluir">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sim, Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalStatus = document.getElementById('modalStatus');
    if (modalStatus) {
        modalStatus.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');
            const status = button.getAttribute('data-status');

            const modalTitle = modalStatus.querySelector('.modal-title');
            const modalText = modalStatus.querySelector('#modalStatusText');
            const btnConfirmar = modalStatus.querySelector('#btn-confirmar-status');
            const justificativaDiv = modalStatus.querySelector('#justificativa-desativar');
            const justificativaTextarea = modalStatus.querySelector('#justificativa_status');

            modalStatus.querySelector('#id-usuario-status').value = id;
            modalStatus.querySelector('#novo-status-usuario').value = status;

            if (status == '0') { // Desativar
                modalTitle.innerHTML = '<i class="bi bi-person-x me-2 text-warning"></i>Desativar Usuário';
                modalText.innerHTML = `Tem certeza que deseja desativar o usuário <strong>${nome}</strong>?`;
                btnConfirmar.className = 'btn btn-warning';
                btnConfirmar.textContent = 'Sim, Desativar';
                justificativaDiv.style.display = 'block';
                justificativaTextarea.required = true;
            } else { // Ativar
                modalTitle.innerHTML = '<i class="bi bi-person-check me-2 text-success"></i>Ativar Usuário';
                modalText.innerHTML = `Tem certeza que deseja reativar o usuário <strong>${nome}</strong>?`;
                btnConfirmar.className = 'btn btn-success';
                btnConfirmar.textContent = 'Sim, Ativar';
                justificativaDiv.style.display = 'none';
                justificativaTextarea.required = false;
            }
        });
    }

    const modalExcluir = document.getElementById('modalExcluir');
    if (modalExcluir) {
        modalExcluir.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');
            modalExcluir.querySelector('#id-usuario-excluir').value = id;
            modalExcluir.querySelector('#nome-usuario-excluir').textContent = nome;
        });
    }
});
</script>
