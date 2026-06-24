-- phpMyAdmin SQL Dump
-- version 3.4.0
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 24/06/2026 às 14h54min
-- Versão do Servidor: 5.0.32
-- Versão do PHP: 5.2.0-8+etch16

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: `qualidade_teste`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_categoria`
--

CREATE TABLE IF NOT EXISTS `t_categoria` (
  `id_categoria` int(11) NOT NULL auto_increment,
  `nome_categoria` varchar(100) NOT NULL,
  `sigla_categoria` varchar(10) NOT NULL,
  PRIMARY KEY  (`id_categoria`),
  UNIQUE KEY `sigla_categoria` (`sigla_categoria`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Extraindo dados da tabela `t_categoria`
--

INSERT INTO `t_categoria` (`id_categoria`, `nome_categoria`, `sigla_categoria`) VALUES
(1, 'Relatórios', 'RE'),
(2, 'Calendário de Atividades', 'CA'),
(3, 'Diretrizes Organizacionais', 'DO'),
(4, 'Manual da Qualidade', 'MQ'),
(5, 'Manual de Segurança', 'MS'),
(6, 'Formulários da Qualidade', 'FQ'),
(7, 'Instruções de Trabalho', 'IT'),
(8, 'Procedimentos da Qualidade', 'PQ'),
(9, 'Processos', 'PR');

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_documento`
--

CREATE TABLE IF NOT EXISTS `t_documento` (
  `id_documento` int(11) NOT NULL auto_increment,
  `id_categoria` int(11) NOT NULL,
  `id_status` int(11) NOT NULL default '1',
  `codigo_documento` varchar(100) NOT NULL,
  `nome_documento` varchar(255) NOT NULL,
  `autor_documento` varchar(150) NOT NULL,
  `revisao_documento` varchar(50) NOT NULL,
  `data_vigor_documento` date NOT NULL,
  `data_analise_documento` date NOT NULL,
  `data_saida_documento` date default NULL,
  `arquivo_documento` varchar(255) NOT NULL,
  `controle_documento` tinyint(1) default '0',
  PRIMARY KEY  (`id_documento`),
  UNIQUE KEY `codigo_documento` (`codigo_documento`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_status` (`id_status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_documento_local`
--

CREATE TABLE IF NOT EXISTS `t_documento_local` (
  `id_documento_local` int(11) NOT NULL auto_increment,
  `id_documento` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `numero_copia` varchar(50) default NULL,
  PRIMARY KEY  (`id_documento_local`),
  KEY `id_documento` (`id_documento`),
  KEY `id_local` (`id_local`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_historico`
--

CREATE TABLE IF NOT EXISTS `t_historico` (
  `id_historico` int(11) NOT NULL auto_increment,
  `acao_historico` varchar(150) NOT NULL,
  `justificativa_historico` text NOT NULL,
  `data_historico` datetime NOT NULL,
  `qualidade_id` int(11) NOT NULL,
  `id_documento` int(11) default NULL,
  `id_sigla` int(11) default NULL,
  PRIMARY KEY  (`id_historico`),
  KEY `id_documento` (`id_documento`),
  KEY `id_sigla` (`id_sigla`),
  KEY `fk_historico_usuario` (`qualidade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_local`
--

CREATE TABLE IF NOT EXISTS `t_local` (
  `id_local` int(11) NOT NULL auto_increment,
  `nome_local` varchar(100) NOT NULL,
  `logo_url` varchar(255) default NULL,
  PRIMARY KEY  (`id_local`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Extraindo dados da tabela `t_local`
--

INSERT INTO `t_local` (`id_local`, `nome_local`, `logo_url`) VALUES
(1, 'Área do aluno', NULL),
(2, 'CCQA', NULL),
(3, 'Cereal Chocotec', NULL),
(4, 'Cetea', NULL),
(5, 'Cial', NULL),
(6, 'CTC', NULL),
(7, 'DG', NULL),
(8, 'DQS', NULL),
(9, 'Extranet', NULL),
(10, 'Fruthotec', NULL),
(11, 'Google Drive', NULL),
(12, 'INMETRO', NULL),
(13, 'Intranet', NULL),
(14, 'Manual da Qualidade', NULL),
(15, 'RA', NULL),
(16, 'RA-110', NULL),
(17, 'Site do Ital', NULL),
(18, 'Tecnolat', NULL),
(19, 'Treinamento Integração Alunos', NULL),
(20, 'Treinamento Integração Estagiários', NULL),
(21, 'Treinamento Integração Funcionários', NULL),
(22, 'Treinamento Integração Profissional Externo', NULL),
(23, 'RA-25', NULL),
(24, 'RA-56', NULL),
(25, 'RA-22', NULL),
(26, 'RA-111', NULL),
(27, 'RA-22', NULL),
(28, 'RA-111', NULL),
(29, 'CAPD', NULL),
(30, 'GEPC', NULL),
(31, 'POS', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_perfil`
--

CREATE TABLE IF NOT EXISTS `t_perfil` (
  `id_perfil` int(11) NOT NULL auto_increment,
  `nome_perfil` varchar(100) NOT NULL,
  PRIMARY KEY  (`id_perfil`),
  UNIQUE KEY `nome_perfil` (`nome_perfil`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Extraindo dados da tabela `t_perfil`
--

INSERT INTO `t_perfil` (`id_perfil`, `nome_perfil`) VALUES
(1, 'RA-Ital'),
(2, 'RQ da Unidade'),
(3, 'Responsável pelo controle'),
(4, 'Colaborador'),
(5, 'Administrador');

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_sigla`
--

CREATE TABLE IF NOT EXISTS `t_sigla` (
  `id_sigla` int(11) NOT NULL auto_increment,
  `id_status` int(11) NOT NULL default '2',
  `numero_sigla` int(11) NOT NULL,
  `nome_sigla` varchar(100) NOT NULL,
  `definicao_sigla` text NOT NULL,
  `referencia_sigla` varchar(255) default NULL,
  `data_sigla` date NOT NULL,
  `data_saida_sigla` date default NULL,
  PRIMARY KEY  (`id_sigla`),
  KEY `id_status` (`id_status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_status`
--

CREATE TABLE IF NOT EXISTS `t_status` (
  `id_status` int(11) NOT NULL,
  `nome_status` varchar(50) NOT NULL,
  PRIMARY KEY  (`id_status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `t_status`
--

INSERT INTO `t_status` (`id_status`, `nome_status`) VALUES
(1, 'Agendado'),
(2, 'Em Vigor'),
(3, 'Obsoleto');

-- --------------------------------------------------------

--
-- Estrutura da tabela `t_usuario_qualidade`
--

CREATE TABLE IF NOT EXISTS `t_usuario_qualidade` (
  `id_usuario_qualidade` int(11) NOT NULL auto_increment,
  `id_perfil` int(11) NOT NULL,
  `id_local` int(11) default NULL,
  `nome_usuario` varchar(150) NOT NULL,
  `email_usuario` varchar(150) NOT NULL,
  `login_usuario` varchar(50) NOT NULL,
  `senha_usuario` varchar(255) NOT NULL,
  `status_usuario` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id_usuario_qualidade`),
  UNIQUE KEY `email_usuario` (`email_usuario`),
  UNIQUE KEY `login_usuario` (`login_usuario`),
  KEY `id_perfil` (`id_perfil`),
  KEY `id_local` (`id_local`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
