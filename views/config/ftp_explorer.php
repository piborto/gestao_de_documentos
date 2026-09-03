<?php
function ftpExplorerEsc($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function ftpExplorerTamanho($tamanho) {
    if ($tamanho === null) return '-';
    if ($tamanho < 1024) return $tamanho . ' B';
    if ($tamanho < 1048576) return number_format($tamanho / 1024, 1, ',', '.') . ' KB';
    return number_format($tamanho / 1048576, 1, ',', '.') . ' MB';
}
?>
<?php $pastaAtual = isset($pastaFtp) ? trim($pastaFtp, '/') : ''; ?>
<?php $conteudoFtp = isset($conteudoFtp) ? $conteudoFtp : false; ?>
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold text-dark"><i class="bi bi-hdd-network me-2 text-primary"></i>Explorador FTP</h3>
        <p class="text-muted">Conteúdo de <code>sistema_documentos/<?php echo ftpExplorerEsc($pastaAtual); ?></code></p>
    </div>
    <div class="col-auto">
        <?php if ($pastaAtual !== ''): ?><a href="index.php?modulo=ftp_explorer&amp;pasta=<?php echo urlencode(dirname($pastaAtual) === '.' ? '' : dirname($pastaAtual)); ?>" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left me-2"></i>Voltar</a><?php endif; ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaPasta"><i class="bi bi-folder-plus me-2"></i>Nova pasta</button>
    </div>
</div>

<?php if (isset($_GET['sucesso'])): ?><div class="alert alert-success">Operação FTP realizada com sucesso.</div><?php elseif (isset($_GET['erro'])): ?><div class="alert alert-danger">Não foi possível concluir a operação FTP.</div><?php endif; ?>

<?php if ($conteudoFtp === false): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Não foi possível consultar o servidor FTP. Verifique o log em <code>helpers/ftp_debug.log</code>.</div>
<?php elseif (empty($conteudoFtp)): ?>
    <div class="alert alert-light border">Esta pasta está vazia.</div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-custom"><tr><th>Tipo</th><th>Nome</th><th class="text-end">Tamanho</th><th>Data</th><th class="text-end">Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($conteudoFtp as $item): ?>
                        <?php $profundidade = substr_count($item['caminho'], '/'); ?>
                        <tr>
                            <td style="width: 90px;"><i class="bi <?php echo $item['tipo'] === 'diretorio' ? 'bi-folder-fill text-warning' : 'bi-file-earmark text-secondary'; ?> me-2"></i><?php echo $item['tipo'] === 'diretorio' ? 'Pasta' : 'Arquivo'; ?></td>
                            <td><?php if ($item['tipo'] === 'diretorio'): ?><a href="index.php?modulo=ftp_explorer&amp;pasta=<?php echo urlencode($item['caminho']); ?>" style="padding-left: <?php echo min($profundidade, 8) * 22; ?>px;"><i class="bi bi-box-arrow-in-right me-1"></i><?php echo ftpExplorerEsc($item['nome']); ?>/</a><?php else: ?><span style="padding-left: <?php echo min($profundidade, 8) * 22; ?>px;"><?php echo ftpExplorerEsc($item['nome']); ?></span><?php endif; ?><div class="small text-muted"><?php echo ftpExplorerEsc($item['caminho']); ?></div></td>
                            <td class="text-end text-nowrap"><?php echo ftpExplorerTamanho($item['tamanho']); ?></td>
                            <td class="text-nowrap"><?php echo ftpExplorerEsc($item['data']); ?></td>
                            <td class="text-end text-nowrap"><button type="button" class="btn btn-sm btn-outline-secondary" title="Renomear" onclick="renomearFtp('<?php echo ftpExplorerEsc($item['caminho']); ?>', '<?php echo ftpExplorerEsc($item['nome']); ?>')"><i class="bi bi-pencil"></i></button> <form method="POST" class="d-inline" onsubmit="return confirm('Apagar este item do FTP?');"><input type="hidden" name="acao_ftp" value="apagar"><input type="hidden" name="pasta_ftp" value="<?php echo ftpExplorerEsc($pastaAtual); ?>"><input type="hidden" name="caminho_ftp" value="<?php echo ftpExplorerEsc($item['caminho']); ?>"><button class="btn btn-sm btn-outline-danger" title="Apagar"><i class="bi bi-trash"></i></button></form></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="modalNovaPasta" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5 class="modal-title">Criar nova pasta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="acao_ftp" value="criar_pasta"><input type="hidden" name="pasta_ftp" value="<?php echo ftpExplorerEsc($pastaAtual); ?>"><label class="form-label" for="nome_pasta">Nome da pasta</label><input class="form-control" id="nome_pasta" name="nome_pasta" maxlength="80" pattern="[A-Za-z0-9._-]+" required></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Criar pasta</button></div></form></div></div></div>

<form method="POST" id="formRenomearFtp" class="d-none"><input type="hidden" name="acao_ftp" value="renomear"><input type="hidden" name="pasta_ftp" value="<?php echo ftpExplorerEsc($pastaAtual); ?>"><input type="hidden" name="caminho_ftp" id="caminhoRenomearFtp"><input type="hidden" name="novo_nome" id="novoNomeFtp"></form>
<script>
function renomearFtp(caminho, nomeAtual) {
    var novoNome = window.prompt('Novo nome:', nomeAtual);
    if (novoNome && novoNome !== nomeAtual && /^[A-Za-z0-9._-]+$/.test(novoNome)) {
        document.getElementById('caminhoRenomearFtp').value = caminho;
        document.getElementById('novoNomeFtp').value = novoNome;
        document.getElementById('formRenomearFtp').submit();
    } else if (novoNome) {
        window.alert('Use apenas letras, numeros, ponto, sublinhado ou hifen.');
    }
}
</script>