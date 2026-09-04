# Sistema de Gestão de Documentos (SGQ)

Sistema web desenvolvido para o gerenciamento, controle e distribuição de documentos do Sistema de Gestão da Qualidade (SGQ), permitindo o controle de revisões, perfis de acesso por unidades, histórico de alterações e armazenamento remoto via FTP.

## 🚀 Tecnologias Utilizadas

* **Backend:** PHP (Legado/Compatível com PHP 5.2+)
* **Banco de Dados:** MySQL / MariaDB (com manipulação via PDO)
* **Armazenamento:** Integração com Servidor FTP para arquivos físicos
* **Frontend:** HTML5, CSS3, JavaScript e Bootstrap

## ⚙️ Principais Funcionalidades

* **Controle de Acessos por Perfil:** Diferenciação de permissões entre administradores, responsáveis da qualidade (RQ) por unidade e colaboradores.
* **Gestão de Documentos:** Cadastro, edição, controle de vigência, obsolescência e restauração de documentos (Procedimentos, Instruções de Trabalho, Manuais, Formulários, etc.).
* **Integração FTP Segura:** Rotinas automatizadas para envio, criação recursiva de pastas e visualização/download seguro de arquivos na rede.
* **Campos Dinâmicos:** Configuração flexível de campos por unidade e categoria de documentos.
* **Trilha de Auditoria:** Histórico completo de ações realizadas em cada documento.

## 📦 Como Configurar o Projeto Localmente

1. Clone o repositório para o seu ambiente local:
   ```bash
   git clone [https://github.com/piborto/gestao_de_documentos.git](https://github.com/piborto/gestao_de_documentos.git)
