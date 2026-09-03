-- Execute uma vez no banco da aplicacao.
ALTER TABLE t_documento
    ADD COLUMN caminho_arquivo VARCHAR(500) NULL AFTER arquivo_documento;
