<?php
if (!function_exists('configCamposEsc')) {
    function configCamposEsc($valor) {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>Configuração de Campos e Categorias</h3>
        <p class="text-muted">Defina os campos exibidos nos documentos de cada unidade e categoria.</p>
    </div>
</div>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Operação realizada com sucesso.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (isset($_GET['erro'])): ?><div class="alert alert-danger">Não foi possível concluir a operação.</div><?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="modulo" value="configurar_campos">
                    <div class="col-md-6">
                        <label for="id_local" class="form-label fw-bold">Unidade</label>
                        <select id="id_local" name="id_local" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($listaUnidades as $unidade): ?>
                                <option value="<?php echo $unidade['id_local']; ?>" <?php echo ($idLocalSelecionado == $unidade['id_local']) ? 'selected' : ''; ?>><?php echo configCamposEsc($unidade['nome_local']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="id_categoria" class="form-label fw-bold">Categoria</label>
                        <select id="id_categoria" name="id_categoria" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($listaCategorias as $categoria): ?>
                                <option value="<?php echo $categoria['id_categoria']; ?>" <?php echo ($idCategoriaSelecionada == $categoria['id_categoria']) ? 'selected' : ''; ?>><?php echo configCamposEsc($categoria['sigla_categoria'] . ' - ' . $categoria['nome_categoria']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 text-end"><button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>Carregar campos</button></div>
                </form>

                <?php if (!empty($configsAtuais)): ?>
                    <hr class="my-4">
                    <form method="POST" action="index.php?modulo=configurar_campos">
                        <input type="hidden" name="id_local" value="<?php echo $idLocalSelecionado; ?>">
                        <input type="hidden" name="id_categoria" value="<?php echo $idCategoriaSelecionada; ?>">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="thead-custom"><tr><th>Campo</th><th>Rótulo</th><th class="text-center">Visível</th><th class="text-center">Obrigatório</th></tr></thead>
                                <tbody>
                                <?php foreach ($configsAtuais as $campo): ?>
                                    <?php $nomeCampo = $campo['nome_campo_interno']; ?>
                                    <tr>
                                        <td><code><?php echo configCamposEsc($nomeCampo); ?></code><input type="hidden" name="campos[<?php echo configCamposEsc($nomeCampo); ?>][ordem]" value="<?php echo intval($campo['ordem']); ?>"></td>
                                        <td><input type="text" class="form-control form-control-sm" name="campos[<?php echo configCamposEsc($nomeCampo); ?>][rotulo]" value="<?php echo configCamposEsc($campo['rotulo_personalizado']); ?>" required></td>
                                        <td class="text-center"><input class="form-check-input" type="checkbox" name="campos[<?php echo configCamposEsc($nomeCampo); ?>][visivel]" <?php echo !empty($campo['visivel']) ? 'checked' : ''; ?>></td>
                                        <td class="text-center"><input class="form-check-input" type="checkbox" name="campos[<?php echo configCamposEsc($nomeCampo); ?>][obrigatorio]" <?php echo !empty($campo['obrigatorio']) ? 'checked' : ''; ?>></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end"><button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Salvar configurações</button></div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light border mt-4 mb-0">Selecione uma unidade e uma categoria para configurar os campos.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold"><i class="bi bi-tag me-2 text-primary"></i>Nova categoria da unidade</h5>
                <p class="small text-muted">A categoria ficará disponível no escopo SGQ UNIDADE.</p>
                <form method="POST" action="index.php?modulo=configurar_campos">
                    <input type="hidden" name="acao" value="categoria">
                    <input type="hidden" name="id_local" value="<?php echo intval($idLocalSelecionado); ?>">
                    <div class="mb-3"><label for="nome_categoria" class="form-label">Nome</label><input type="text" id="nome_categoria" name="nome_categoria" class="form-control" maxlength="100" required></div>
                    <div class="mb-3"><label for="sigla_categoria" class="form-label">Sigla</label><input type="text" id="sigla_categoria" name="sigla_categoria" class="form-control" maxlength="10" required></div>
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-plus-circle me-2"></i>Criar categoria</button>
                </form>
            </div>
        </div>

        <?php if (!empty($categoriasComConfiguracao)): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h5 class="fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Configurações salvas por categoria</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="thead-custom"><tr><th>Categoria</th><th>Escopo</th><th class="text-center">Campos salvos</th><th class="text-end">Ações</th></tr></thead>
                        <tbody>
                        <?php foreach ($categoriasComConfiguracao as $categoriaConfig): ?>
                            <tr>
                                <td><strong><?php echo configCamposEsc($categoriaConfig['sigla_categoria']); ?></strong> - <?php echo configCamposEsc($categoriaConfig['nome_categoria']); ?></td>
                                <td><?php echo $categoriaConfig['id_local'] === null ? 'Global da unidade' : 'Minha unidade'; ?></td>
                                <td class="text-center"><span class="badge <?php echo intval($categoriaConfig['total_configurados']) > 0 ? 'bg-success' : 'bg-secondary'; ?>"><?php echo intval($categoriaConfig['total_configurados']); ?></span></td>
                                <td class="text-end">
                                    <a class="btn btn-outline-primary btn-sm" title="Gerir campos" href="index.php?modulo=configurar_campos&id_local=<?php echo $idLocalSelecionado; ?>&id_categoria=<?php echo $categoriaConfig['id_categoria']; ?>"><i class="bi bi-sliders"></i></a>
                                    <?php if (isset($_SESSION['id_perfil']) && (int)$_SESSION['id_perfil'] === 2 && $categoriaConfig['id_local'] !== null): ?>
                                        <button type="button" class="btn btn-outline-primary btn-sm" title="Editar categoria" data-bs-toggle="modal" data-bs-target="#modalEditarCategoria" data-id="<?php echo $categoriaConfig['id_categoria']; ?>" data-nome="<?php echo configCamposEsc($categoriaConfig['nome_categoria']); ?>" data-sigla="<?php echo configCamposEsc($categoriaConfig['sigla_categoria']); ?>"><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" title="Excluir categoria" data-bs-toggle="modal" data-bs-target="#modalExcluirCategoria" data-id="<?php echo $categoriaConfig['id_categoria']; ?>" data-nome="<?php echo configCamposEsc($categoriaConfig['nome_categoria']); ?>"><i class="bi bi-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($camposSalvosPorCategoria[$categoriaConfig['id_categoria']])): ?>
                            <tr>
                                <td colspan="4" class="bg-light p-0">
                                    <div class="p-3">
                                        <div class="small fw-bold text-muted mb-2">Campos configurados</div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead><tr><th>Campo</th><th>Rótulo</th><th>Visível</th><th>Obrigatório</th></tr></thead>
                                                <tbody>
                                                <?php foreach ($camposSalvosPorCategoria[$categoriaConfig['id_categoria']] as $campoSalvo): ?>
                                                    <tr>
                                                        <td><code><?php echo configCamposEsc($campoSalvo['nome_campo_interno']); ?></code></td>
                                                        <td><?php echo configCamposEsc($campoSalvo['rotulo_personalizado']); ?></td>
                                                        <td><?php echo !empty($campoSalvo['visivel']) ? 'Sim' : 'Não'; ?></td>
                                                        <td><?php echo !empty($campoSalvo['obrigatorio']) ? 'Sim' : 'Não'; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['id_perfil']) && (int)$_SESSION['id_perfil'] === 2): ?>
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?modulo=configurar_campos">
                <div class="modal-header"><h5 class="modal-title">Editar categoria</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="acao" value="editar_categoria">
                    <input type="hidden" name="id_categoria" id="editar-categoria-id">
                    <div class="mb-3"><label for="editar-categoria-nome" class="form-label">Nome</label><input type="text" class="form-control" name="nome_categoria" id="editar-categoria-nome" required></div>
                    <div class="mb-3"><label for="editar-categoria-sigla" class="form-label">Sigla</label><input type="text" class="form-control" name="sigla_categoria" id="editar-categoria-sigla" maxlength="10" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalExcluirCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?modulo=configurar_campos">
                <div class="modal-header bg-danger text-white"><h5 class="modal-title">Excluir categoria</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="acao" value="excluir_categoria">
                    <input type="hidden" name="id_categoria" id="excluir-categoria-id">
                    <p>Excluir a categoria <strong id="excluir-categoria-nome"></strong> e suas configurações de campos?</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Excluir</button></div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editar = document.getElementById('modalEditarCategoria');
    if (editar) editar.addEventListener('show.bs.modal', function (event) {
        var botao = event.relatedTarget;
        document.getElementById('editar-categoria-id').value = botao.getAttribute('data-id');
        document.getElementById('editar-categoria-nome').value = botao.getAttribute('data-nome');
        document.getElementById('editar-categoria-sigla').value = botao.getAttribute('data-sigla');
    });
    var excluir = document.getElementById('modalExcluirCategoria');
    if (excluir) excluir.addEventListener('show.bs.modal', function (event) {
        var botao = event.relatedTarget;
        document.getElementById('excluir-categoria-id').value = botao.getAttribute('data-id');
        document.getElementById('excluir-categoria-nome').textContent = botao.getAttribute('data-nome');
    });
});
</script>
<?php endif; ?>