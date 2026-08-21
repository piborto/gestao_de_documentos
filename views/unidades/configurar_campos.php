
<?php
$id_local_get = isset($_GET['id_local']) ? intval($_GET['id_local']) : '';
$id_categoria_get = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : '';
?>
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-ui-checks-grid me-2 text-primary"></i>Configurar Campos de Formulário</h3>
        <p class="text-muted">Personalize os campos que aparecem no formulário de cadastro de documentos para cada unidade e categoria.</p>
    </div>
</div>

<?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">Configurações salvas com sucesso!</div>
<?php elseif (isset($_GET['erro'])): ?>
    <div class="alert alert-danger">Ocorreu um erro ao salvar as configurações.</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="selectUnidade" class="form-label fw-bold small text-muted">1. Selecione a Unidade</label>
                <select id="selectUnidade" class="form-select">
                    <option value="">-- Selecione uma unidade --</option>
                    <?php foreach ($dados_view['listaUnidades'] as $unidade): ?>
                        <option value="<?php echo $unidade['id_local']; ?>" <?php echo ($unidade['id_local'] == $id_local_get) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($unidade['nome_local']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="selectCategoria" class="form-label fw-bold small text-muted">2. Selecione a Categoria</label>
                <select id="selectCategoria" class="form-select" <?php echo empty($id_local_get) ? 'disabled' : ''; ?>>
                    <option value="">-- Selecione uma unidade primeiro --</option>
                     <?php foreach ($dados_view['listaCategorias'] as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>" <?php echo ($categoria['id_categoria'] == $id_categoria_get) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(utf8_encode($categoria['nome_categoria'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr id="separator" class="my-4" style="display: none;">

        <div id="config-fields-container" class="mt-3" style="display: none;">
            <form id="form-config-campos" method="POST" action="index.php?modulo=unidades_configurar_campos">
                <input type="hidden" name="id_local" id="config_id_local">
                <input type="hidden" name="id_categoria" id="config_id_categoria">

                <div id="fields-list">
                    <!-- A lista de campos será carregada aqui via AJAX -->
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <!-- <button type="button" class="btn btn-outline-success me-auto"><i class="bi bi-plus-circle me-2"></i>Adicionar Novo Campo</button> -->
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-save me-2"></i>Salvar Configurações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selectUnidade = document.getElementById('selectUnidade');
    const selectCategoria = document.getElementById('selectCategoria');
    const container = document.getElementById('config-fields-container');
    const fieldsList = document.getElementById('fields-list');
    const separator = document.getElementById('separator');
    const configIdLocal = document.getElementById('config_id_local');
    const configIdCategoria = document.getElementById('config_id_categoria');

    selectUnidade.addEventListener('change', function() {
        if (this.value) {
            selectCategoria.disabled = false;
            selectCategoria.value = ''; // Limpa a seleção anterior
        } else {
            selectCategoria.disabled = true;
        }
    });

    function carregarCampos() {
        const idLocal = selectUnidade.value;
        const idCategoria = selectCategoria.value;

        if (idLocal && idCategoria) {            
            // Atualiza a URL sem recarregar a página
            const url = new URL(window.location);
            url.searchParams.set('id_local', idLocal);
            url.searchParams.set('id_categoria', idCategoria);
            window.history.pushState({}, '', url);
            
            // Mostra o container e o loading
            container.style.display = 'block';
            separator.style.display = 'block';
            fieldsList.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
            
            // Seta os valores nos campos hidden do formulário
            configIdLocal.value = idLocal;
            configIdCategoria.value = idCategoria;

            // Chamada AJAX para buscar os campos
            fetch(`index.php?modulo=unidades_get_campos_ajax&id_local=${idLocal}&id_categoria=${idCategoria}`)
                .then(response => response.text())
                .then(html => {
                    fieldsList.innerHTML = html;
                })
                .catch(error => {
                    fieldsList.innerHTML = '<div class="alert alert-danger">Erro ao carregar os campos.</div>';
                    console.error('Error:', error);
                });
        }
    }

    selectCategoria.addEventListener('change', carregarCampos);

    // Carrega os campos se os selects já tiverem valores (via GET)
    if (selectUnidade.value && selectCategoria.value) {
        carregarCampos();
    }
});
</script>
