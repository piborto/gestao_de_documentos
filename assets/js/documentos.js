document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-documento');
    if (!form) return;

    const categoriaSelect = document.getElementById('id_categoria');
    
    // Mapeamento de campos para siglas
    const camposPorSigla = {
        'MQ': ['tipo_manual', 'codigo', 'nome', 'revisao', 'vigor', 'analise', 'distribuicao_manual', 'arquivo'],
        'MS': ['tipo_manual', 'codigo', 'nome', 'revisao', 'vigor', 'analise', 'distribuicao_manual', 'arquivo'],
        'RE': ['nome', 'ano', 'vigor', 'arquivo'],
        'CA': ['nome', 'ano', 'vigor', 'arquivo'],
        'PR': ['nome', 'ano', 'vigor', 'arquivo'],
        'DEFAULT': ['codigo', 'nome', 'autor', 'revisao', 'sufixo', 'vigor', 'analise', 'distribuicao', 'arquivo']
    };

    // Mapeamento de locais para siglas
    const locaisManuais = ['Área do aluno', 'CCQA', 'Cereal Chocotec', 'Cetea', 'Cial', 'CTC', 'DG', 'DQS', 'Extranet', 'Fruthotec', 'INMETRO', 'Intranet', 'RA', 'Tecnolat'];

    function toggleCampos() {
        const selectedOption = categoriaSelect.options[categoriaSelect.selectedIndex];
        const sigla = selectedOption.getAttribute('data-sigla') || 'DEFAULT';

        // Pega a lista de campos a serem exibidos. Se a sigla não for encontrada, usa o padrão.
        const camposVisiveis = camposPorSigla[sigla] || camposPorSigla['DEFAULT'];

        // Esconde todos os campos dinâmicos primeiro
        document.querySelectorAll('.campo-dinamico').forEach(div => {
            div.style.display = 'none';
            // Desabilita inputs para não serem enviados com o form se estiverem escondidos
            div.querySelectorAll('input, select').forEach(input => input.disabled = true);
        });

        // Mostra apenas os campos necessários
        camposVisiveis.forEach(nomeCampo => {
            const div = document.getElementById(`div-${nomeCampo}`);
            if (div) {
                div.style.display = '';
                // Habilita os inputs para envio
                div.querySelectorAll('input, select').forEach(input => input.disabled = false);
            }
        });

        // Lógica específica para distribuição de manuais
        const areaDistribuicao = document.getElementById('div-distribuicao');
        const areaDistribuicaoManual = document.getElementById('div-distribuicao_manual');

        if (areaDistribuicaoManual.style.display !== 'none') {
            filtrarLocaisManuais();
        } else if (areaDistribuicao.style.display !== 'none') {
            mostrarTodosLocais();
        }
    }

    function filtrarLocaisManuais() {
        document.querySelectorAll('.div-checkbox-loc').forEach(div => {
            const nomeLocal = div.getAttribute('data-nome');
            if (locaisManuais.includes(nomeLocal)) {
                div.style.display = '';
            } else {
                div.style.display = 'none';
            }
        });
    }

    function mostrarTodosLocais() {
        document.querySelectorAll('.div-checkbox-loc').forEach(div => {
            div.style.display = '';
        });
    }

    // Adiciona listener para o campo de categoria
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', toggleCampos);
    }

    // Adiciona listeners para os checkboxes de distribuição de manuais
    document.querySelectorAll('.check-dist-manual').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const numInput = this.closest('.input-group').querySelector('.input-num-manual');
            if (this.checked) {
                numInput.style.display = 'block';
                numInput.disabled = false;
            } else {
                numInput.style.display = 'none';
                numInput.disabled = true;
            }
        });
    });

    // Inicializa o formulário no carregamento da página
    toggleCampos();
});