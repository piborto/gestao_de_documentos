<?php
require_once dirname(__FILE__) . '/../../config/conexao.php';
require_once dirname(__FILE__) . '/../../lib/fpdf.php';

if (session_id() == "") {
    session_start();
}

if (!isset($_SESSION['usuario_logado']) || $_SESSION['usuario_logado'] !== true) {
    die("Acesso negado. Por favor, faça o login.");
}

class PDF_ListaMestra extends FPDF {
    public $tituloCategoria;
    public $dataEmissao;
    public $textoRodape;
    public $h_w_cod, $h_w_nome, $h_w_autor, $h_w_rev, $h_w_vigor, $h_w_analise, $h_w_dist;
    public $h_locais;
    public $h_height;

    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(200, 6, utf8_decode('Lista mestra de documentos do sistema de gestão da qualidade'), 0, 0, 'L');

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 1, 'R');

        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 6, utf8_decode(strtoupper($this->tituloCategoria)), 0, 1, 'L');

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(80, 80, 80);
        $texto_info = utf8_decode('Data da última atualização das informações: ' . $this->dataEmissao . '           Controle: RA');
        $this->Cell(0, 4, $texto_info, 0, 1, 'L');
        $this->Ln(2);

        if (isset($this->h_w_cod)) {
            $this->DesenharTabelaCabecalho();
        }
    }

    function DesenharTabelaCabecalho() {
        $x = $this->lMargin;
        $y = $this->GetY();
        $h = $this->h_height;

        $largura_fixas = $this->h_w_cod + $this->h_w_nome + $this->h_w_autor + $this->h_w_rev + $this->h_w_vigor + $this->h_w_analise;
        $largura_dist = count($this->h_locais) * $this->h_w_dist;
        if ($largura_dist == 0) $largura_dist = 30;
        $largura_total = $largura_fixas + $largura_dist;

        $this->SetFillColor(44, 62, 80);
        $this->SetDrawColor(44, 62, 80);
        $this->Rect($x, $y, $largura_total, $h, 'DF');

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetLineWidth(0.1);
        $this->SetDrawColor(200, 210, 220);

        $cx = $x;

        $this->SetXY($cx, $y + ($h / 2) - 3);
        $this->MultiCell($this->h_w_cod, 3, utf8_decode("Código do\ndocumento"), 0, 'C');
        $cx += $this->h_w_cod;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y + ($h / 2) - 1.5);
        $this->Cell($this->h_w_nome, 3, utf8_decode('Nome do Documento'), 0, 0, 'C');
        $cx += $this->h_w_nome;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y + ($h / 2) - 1.5);
        $this->Cell($this->h_w_autor, 3, utf8_decode('Autor'), 0, 0, 'C');
        $cx += $this->h_w_autor;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y + ($h / 2) - 1.5);
        $this->Cell($this->h_w_rev, 3, utf8_decode('Revisão'), 0, 0, 'C');
        $cx += $this->h_w_rev;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y + ($h / 2) - 3);
        $this->MultiCell($this->h_w_vigor, 3, utf8_decode("Em vigor\na partir de"), 0, 'C');
        $cx += $this->h_w_vigor;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y + ($h / 2) - 4.5);
        $this->MultiCell($this->h_w_analise, 3, utf8_decode("Próxima\nanálise\ncrítica"), 0, 'C');
        $cx += $this->h_w_analise;
        $this->Line($cx, $y, $cx, $y + $h);

        $h_topo = 5;
        $h_cols = $h - $h_topo;
        $y_cols = $y + $h_topo;

        $this->SetXY($cx, $y + 1);
        $this->Cell($largura_dist, 3, utf8_decode('Distribuição'), 0, 0, 'C');
        $this->Line($cx, $y_cols, $x + $largura_total, $y_cols);

        $this->SetFont('Arial', '', 7);

        if (count($this->h_locais) > 0) {
            foreach ($this->h_locais as $local) {
                $txt = utf8_decode($local['nome_local']);
                $this->SetFont('Arial', 'B', $this->GetStringWidth($txt) > ($h_cols - 2) ? 6 : 7);
                $this->RotatedText($cx + ($this->h_w_dist / 2) + 1, $y_cols + $h_cols - 2, $txt, 90);
                $cx += $this->h_w_dist;
                if ($cx < ($x + $largura_total - 0.1)) {
                    $this->Line($cx, $y_cols, $cx, $y + $h);
                }
            }
        } else {
            $this->SetXY($cx, $y_cols + ($h_cols / 2) - 1.5);
            $this->Cell(30, 3, '-', 0, 0, 'C');
        }

        $this->SetXY($x, $y + $h);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(0, 0, 0);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->SetDrawColor(200, 200, 200);
        $this->Line($this->lMargin, $this->GetY(), 297 - $this->rMargin, $this->GetY());
        $this->Ln(2);
        $texto_completo = $this->textoRodape . ' - Pagina 1/1 (versao eletronica)';
        $this->Cell(0, 10, utf8_decode($texto_completo), 0, 0, 'L');
    }

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage() {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    function RotatedText($x, $y, $txt, $angle) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if ($c == ' ') $sep = $i;
            $l += isset($cw[$c]) ? $cw[$c] : 0;
            if ($l > $wmax) {
                if ($sep == -1) { if ($i == $j) $i++; } else $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }
}

// --- LÓGICA PRINCIPAL ---

$id_cat_input = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$rodape_input = isset($_GET['rodape']) ? $_GET['rodape'] : "FQ-04.01 (anexo ao PQ-04.02) - RA - revisão 08 de 08/11/21";

$params = array();
$sql_where_cat = "";

$ids_para_pdf = array();
$is_pai = false;

if ($id_cat_input > 0) {
    // Verifica se a categoria selecionada é pai
    $stmt_check_pai = $conexao->prepare("SELECT COUNT(*) FROM t_categoria WHERE id_categoria_pai = ?");
    $stmt_check_pai->execute(array($id_cat_input));
    $is_pai = $stmt_check_pai->fetchColumn() > 0;

    if ($is_pai) {
        $stmt_filhos = $conexao->prepare("SELECT id_categoria FROM t_categoria WHERE id_categoria_pai = ?");
        $stmt_filhos->execute(array($id_cat_input));
        $ids_para_pdf = $stmt_filhos->fetchAll(PDO::FETCH_COLUMN);
        $placeholders = implode(',', array_fill(0, count($ids_para_pdf), '?'));
        $sql_where_cat = "WHERE d.id_categoria IN (" . $placeholders . ")";
        $params = $ids_para_pdf;
    } else {
        $ids_para_pdf[] = $id_cat_input;
        $sql_where_cat = "WHERE d.id_categoria = ?";
        $params[] = $id_cat_input;
    }
} else {
    $sql_where_cat = "WHERE c.sigla_categoria NOT IN ('DO', 'LM', 'MQ', 'MS', 'MA', 'RE', 'CA', 'PR')";
}

// Busca a data de atualização mais recente para o cabeçalho
$sql_max_data = "SELECT MAX(d.data_vigor_documento) as ultima_atualizacao FROM t_documento d JOIN t_categoria c ON d.id_categoria = c.id_categoria " . $sql_where_cat;
$stmt_max = $conexao->prepare($sql_max_data);
$stmt_max->execute($params);
$row_max_data = $stmt_max->fetch(PDO::FETCH_ASSOC);
$data_cabecalho = (!empty($row_max_data['ultima_atualizacao'])) ? date('d/m/Y', strtotime($row_max_data['ultima_atualizacao'])) : date('d/m/Y');

$pdf = new PDF_ListaMestra('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->dataEmissao = $data_cabecalho;
$pdf->textoRodape = $rodape_input;
$pdf->SetAutoPageBreak(true, 20);

// --- MAPEAMENTO DE CATEGORIAS E SUAS COLUNAS ---
function getIdLocal($nome, $mapa) {
    $chave = trim(mb_strtolower($nome, 'UTF-8'));
    return isset($mapa[$chave]) ? $mapa[$chave] : 0;
}

$mapa_locais = array();
$stmt_locais = $conexao->prepare("SELECT id_local, nome_local FROM t_local");
$stmt_locais->execute();
$locais_todos = $stmt_locais->fetchAll(PDO::FETCH_ASSOC);
foreach ($locais_todos as $l) {
    $mapa_locais[trim(mb_strtolower($l['nome_local'], 'UTF-8'))] = $l['id_local'];
}

$categorias_para_processar = array();
if ($id_cat_input > 0) {
    $stmt_cat = $conexao->prepare("SELECT id_categoria, nome_categoria, sigla_categoria, id_categoria_pai FROM t_categoria WHERE id_categoria = ?");
    $stmt_cat->execute(array($id_cat_input));
    $categorias_para_processar = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt_cat = $conexao->prepare("SELECT id_categoria, nome_categoria, sigla_categoria FROM t_categoria WHERE sigla_categoria IN ('PQ', 'IT', 'FQ') ORDER BY FIELD(sigla_categoria, 'PQ', 'IT', 'FQ')");
    $stmt_cat->execute();
    $categorias_para_processar = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($categorias_para_processar as $categoria_atual) {
    $id_cat_atual = $categoria_atual['id_categoria'];
    $sigla_atual = strtoupper(trim($categoria_atual['sigla_categoria']));
    $nome_cat_atual = strtoupper(utf8_encode($categoria_atual['nome_categoria']));

    // 1. Se for Manual, precisamos rodar a geração de tabela DUAS VEZES (Controlado e Não Controlado)
    $tipos_controle = in_array($sigla_atual, array('MA', 'MQ', 'MS')) ? array(1, 0) : array(null);

    foreach ($tipos_controle as $status_controle) {
        $colunas_dist = array();
        $titulo_pdf = $nome_cat_atual;
        $tipo_preenchimento = 'x'; 
        $autor_fixo = null;
        $h_head = 35; 

        if (in_array($sigla_atual, array('MA', 'MQ', 'MS'))) {
            $titulo_pdf = ($status_controle === 1) ? 'MANUAIS CONTROLADOS' : 'MANUAIS NÃO CONTROLADOS';
            $colunas_dist = ($status_controle === 1) ?
                array('DG', 'RA', 'CCQA', 'Cereal Chocotec', 'CTC', 'Tecnolat', 'Cetea', 'Cial', 'Fruthotec', 'Intranet', 'Extranet', 'Área do aluno') :
                array('INMETRO', 'DQS');
            $tipo_preenchimento = 'numero';
            $autor_fixo = 'RA';
            $h_head = 45; 
        } elseif ($sigla_atual == 'FQ') {
            $titulo_pdf = 'FORMULÁRIOS DA QUALIDADE';
            $colunas_dist = array('Intranet', 'Extranet', 'Área do aluno', 'RA-25', 'RA-56', 'Google Drive');
        } elseif (in_array($sigla_atual, array('PQ', 'IT'))) {
            $titulo_pdf = ($sigla_atual == 'PQ') ? 'PROCEDIMENTOS DA QUALIDADE' : 'INSTRUÇÕES DE TRABALHO';
            $colunas_dist = array('Intranet', 'Extranet', 'Área do aluno', 'CCQA', 'Cereal Chocotec', 'Cetea', 'CTC', 'DG', 'Fruthotec', 'Tecnolat', 'RA-25', 'RA-110');
        } elseif ($sigla_atual == 'DO') {
            $titulo_pdf = 'DIRETRIZES ORGANIZACIONAIS';
            $colunas_dist = array('Intranet', 'Site do Ital', 'Manual da Qualidade', 'Treinamento Integração Funcionários', 'Treinamento Integração Estagiários', 'Treinamento Integração Alunos', 'Treinamento Integração Profissional Externo');
        }

        $locais_cabecalho = array();
        foreach ($colunas_dist as $nome_col) {
            $chave_local = trim(mb_strtolower($nome_col, 'UTF-8'));
            $id_local_encontrado = isset($mapa_locais[$chave_local]) ? $mapa_locais[$chave_local] : 0;
            $locais_cabecalho[] = array('id_local' => $id_local_encontrado, 'nome_local' => $nome_col);
        }
        $total_locais = count($locais_cabecalho);

        $w_cod = 30; $w_nome = 68; $w_autor = 38; $w_rev = 23; $w_vigor = 28; $w_analise = 26;
        $w_fixas = $w_cod + $w_nome + $w_autor + $w_rev + $w_vigor + $w_analise;
        $w_disp = (297 - 20) - $w_fixas;
        $w_dist = ($total_locais > 0) ? ($w_disp / $total_locais) : 0;
        if ($w_dist < 6) $w_dist = 6;
        if ($w_dist > 12) $w_dist = 12;

        $largura_real_tabela = $w_fixas + ($w_dist * $total_locais);
        if ($total_locais == 0) $largura_real_tabela = $w_fixas + 30;
        $nova_margem = (297 - $largura_real_tabela) / 2;
        if ($nova_margem < 5) $nova_margem = 5;

        $pdf->SetLeftMargin($nova_margem);
        $pdf->SetRightMargin($nova_margem);

        if (!in_array($sigla_atual, array('MA', 'MQ', 'MS'))) {
            $pdf->SetFont('Arial', '', 7);
            $max_w_text = 0;
            foreach ($colunas_dist as $nome_col) {
                $w_text = $pdf->GetStringWidth(utf8_decode($nome_col));
                if ($w_text > $max_w_text) $max_w_text = $w_text;
            }
            $h_head_calc = $max_w_text + 9;
            $h_head = ($h_head_calc < 35) ? 35 : $h_head_calc;
        }

        // 2. Monta a Query SQL Dinâmica e Correta
        $sql_docs = "SELECT d.*, GROUP_CONCAT(dl.id_local SEPARATOR ',') as locais_ids, GROUP_CONCAT(dl.numero_copia SEPARATOR ',') as copias_nums
                     FROM t_documento d
                     LEFT JOIN t_documento_local dl ON d.id_documento = dl.id_documento
                     WHERE d.id_status = 1"; 

        $params_docs = array();

        if (in_array($sigla_atual, array('MA', 'MQ', 'MS'))) {
            // Força a busca nas categorias filhas de Manuais dinamicamente
            $stmt_filhos_manuais = $conexao->prepare("SELECT id_categoria FROM t_categoria WHERE id_categoria_pai = (SELECT id_categoria FROM t_categoria WHERE sigla_categoria = 'MA') OR sigla_categoria IN ('MA', 'MQ', 'MS')");
            $stmt_filhos_manuais->execute();
            $ids_manuais = $stmt_filhos_manuais->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($ids_manuais) > 0) {
                $placeholders = implode(',', array_fill(0, count($ids_manuais), '?'));
                $sql_docs .= " AND d.id_categoria IN (" . $placeholders . ")";
                $params_docs = $ids_manuais;
            } else {
                $sql_docs .= " AND 1=0"; 
            }
        } else {
            $sql_docs .= " AND d.id_categoria = ?";
            $params_docs[] = $id_cat_atual;
        }

        if ($status_controle !== null) {
            $sql_docs .= " AND d.controle_documento = ?";
            $params_docs[] = $status_controle;
        }

        $sql_docs .= " GROUP BY d.id_documento ORDER BY d.codigo_documento ASC";
        $stmt_docs = $conexao->prepare($sql_docs);
        $stmt_docs->execute($params_docs);

        // 3. Só Adiciona a Página e Desenha o Cabeçalho se Existir Documento!
        if ($stmt_docs->rowCount() == 0) {
            continue; 
        }

        $pdf->tituloCategoria = $titulo_pdf;
        $pdf->h_w_cod = $w_cod; $pdf->h_w_nome = $w_nome; $pdf->h_w_autor = $w_autor; $pdf->h_w_rev = $w_rev;
        $pdf->h_w_vigor = $w_vigor; $pdf->h_w_analise = $w_analise; $pdf->h_w_dist = $w_dist;
        $pdf->h_locais = $locais_cabecalho;
        $pdf->h_height = $h_head;

        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 8);
        $fill = false;

        while ($doc = $stmt_docs->fetch(PDO::FETCH_ASSOC)) {
            $dists_doc_ids = !empty($doc['locais_ids']) ? explode(',', $doc['locais_ids']) : array();
            $copias_doc = array();
            if ($tipo_preenchimento == 'numero' && !empty($doc['copias_nums'])) {
                $copias_array = explode(',', $doc['copias_nums']);
                foreach ($dists_doc_ids as $key => $id_local) {
                    $copias_doc[$id_local] = isset($copias_array[$key]) ? $copias_array[$key] : '1';
                }
            }

            $txt_nome = utf8_encode($doc['nome_documento']);
            $txt_autor = $autor_fixo ? $autor_fixo : utf8_encode($doc['autor_documento']);

            $h_base = 6;
            $nb_nome = $pdf->NbLines($w_nome, $txt_nome);
            $nb_autor = $pdf->NbLines($w_autor, $txt_autor);
            $max_lines = max($nb_nome, $nb_autor, 1);
            $h_real = $h_base * $max_lines;

            if ($pdf->GetY() + $h_real > 185) {
                $pdf->AddPage();
                $fill = false;
            }

            $y_start = $pdf->GetY();
            $x_start = $pdf->GetX();

            $pdf->SetFillColor($fill ? 240 : 255, $fill ? 242 : 255, $fill ? 245 : 255);
            $pdf->Rect($x_start, $y_start, $largura_real_tabela, $h_real, 'F');
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetDrawColor(180, 180, 180);

            $current_x = $x_start;

            $pdf->SetXY($current_x, $y_start);
            $pdf->MultiCell($w_cod, $h_base, utf8_decode($doc['codigo_documento']), 0, 'L');
            $pdf->Rect($current_x, $y_start, $w_cod, $h_real);
            $current_x += $w_cod;

            $pdf->SetXY($current_x, $y_start);
            $pdf->MultiCell($w_nome, $h_base, utf8_decode($txt_nome), 0, 'L');
            $pdf->Rect($current_x, $y_start, $w_nome, $h_real);
            $current_x += $w_nome;

            $pdf->SetXY($current_x, $y_start);
            $pdf->MultiCell($w_autor, $h_base, utf8_decode($txt_autor), 0, 'C');
            $pdf->Rect($current_x, $y_start, $w_autor, $h_real);
            $current_x += $w_autor;

            $pdf->SetXY($current_x, $y_start);
            $rev_formatada = ($sigla_atual == 'LM') ? '-' : str_pad($doc['revisao_documento'], 2, '0', STR_PAD_LEFT);
            $pdf->Cell($w_rev, $h_real, $rev_formatada, 0, 0, 'C');
            $pdf->Rect($current_x, $y_start, $w_rev, $h_real);
            $current_x += $w_rev;

            $pdf->SetXY($current_x, $y_start);
            $data_vig = ($doc['data_vigor_documento']) ? date('d/m/Y', strtotime($doc['data_vigor_documento'])) : '-';
            $pdf->Cell($w_vigor, $h_real, $data_vig, 0, 0, 'C');
            $pdf->Rect($current_x, $y_start, $w_vigor, $h_real);
            $current_x += $w_vigor;

            $pdf->SetXY($current_x, $y_start);
            $data_ana = '-';
            if (!empty($doc['data_analise_documento']) && $doc['data_analise_documento'] != '0000-00-00') {
                $data_ana = date('d/m/Y', strtotime($doc['data_analise_documento']));
            }
            $pdf->Cell($w_analise, $h_real, $data_ana, 0, 0, 'C');
            $pdf->Rect($current_x, $y_start, $w_analise, $h_real);
            $current_x += $w_analise;

            if ($total_locais > 0) {
                foreach ($locais_cabecalho as $local) {
                    $id = $local['id_local'];
                    $texto = '-';
                    $is_bold = false;

                    if ($id > 0 && in_array($id, $dists_doc_ids)) {
                        $texto = ($tipo_preenchimento == 'numero') ? (isset($copias_doc[$id]) ? $copias_doc[$id] : '1') : 'x';
                        $is_bold = true;
                    }

                    $pdf->SetXY($current_x, $y_start);
                    $pdf->SetFont('Arial', $is_bold ? 'B' : '', 8);
                    $pdf->SetTextColor($is_bold ? 0 : 150, 0, 0);
                    $pdf->Cell($w_dist, $h_real, $texto, 0, 0, 'C');
                    $pdf->Rect($current_x, $y_start, $w_dist, $h_real);
                    $current_x += $w_dist;
                }
            } else {
                $pdf->SetXY($current_x, $y_start);
                $pdf->Cell(30, $h_real, '-', 0, 0, 'C');
                $pdf->Rect($current_x, $y_start, 30, $h_real);
            }

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetXY($x_start, $y_start + $h_real);
            $fill = !$fill;
        }
        $stmt_docs->closeCursor();
        $pdf->SetDrawColor(44, 62, 80);
        $pdf->Cell($largura_real_tabela, 0, '', 'T');
    }
}

$nome_arquivo = ($id_cat_input > 0) ? 'Lista_Mestra_Categoria.pdf' : 'Lista_Mestra_Geral.pdf';
$pdf->Output('I', $nome_arquivo);
?>