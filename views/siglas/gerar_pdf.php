<?php
// As inclusões do Model e FPDF são gerenciadas pelo index.php na rota 'siglas_pdf'
if (!class_exists('FPDF')) {
    // Se a classe FPDF não foi carregada, interrompe para evitar erro fatal.
    die('Erro: A biblioteca FPDF não foi carregada. Verifique a rota no index.php.');
}

// O objeto $conexao já está disponível a partir do index.php
$siglasModel = new SiglasModel($conexao);

// 1. Busca a data da última atualização, conforme a regra de negócio
$ultima_atualizacao = $siglasModel->getUltimaAtualizacao();
$data_cabecalho = $ultima_atualizacao ? date('d/m/Y', strtotime($ultima_atualizacao)) : date('d/m/Y');

// 2. Pega o texto do rodapé (padrão ou customizado via GET)
$texto_padrao_rodape = "FQ-04.11 (Anexo à IT-04.02.01) - RA - revisão 01 de 09/04/26";
$rodape_input = isset($_GET['rodape']) && !empty($_GET['rodape']) ? $_GET['rodape'] : $texto_padrao_rodape;

class PDF_Siglas extends FPDF {
    var $dataEmissao;
    var $textoRodape;

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if($w==0) $w=$this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if($c==' ') $sep = $i;
            $l += isset($cw[$c]) ? $cw[$c] : 0;
            if($l>$wmax) {
                if($sep==-1) { if($i==$j) $i++; } else $i = $sep+1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }

    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(44, 62, 80);
        $this->Cell(200, 6, utf8_decode('DEFINIÇÕES E SIGLAS'), 0, 0, 'L');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, utf8_decode('Página ' . $this->PageNo() . '/{nb}'), 0, 1, 'R');

        $this->Ln(2);

        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(80, 80, 80);
        $info_line = 'Atualizado em: ' . $this->dataEmissao . '            Responsavel: RA' . '            Aplicacao: PQ/IT/MQ/MS';
        $this->Cell(0, 4, utf8_decode($info_line), 0, 1, 'L');

        $this->Ln(3);

        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(44, 62, 80);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(44, 62, 80);
        $this->SetLineWidth(0.1);

        $h = 7;
        $x = $this->GetX();
        $y = $this->GetY();

        $w2 = 60; $w3 = 145; $w4 = 72;
        $w_total = $w2 + $w3 + $w4;

        $this->Rect($x, $y, $w_total, $h, 'DF');
        $this->SetDrawColor(200, 210, 220);

        $cx = $x;

        $this->SetXY($cx, $y);
        $this->Cell($w2, $h, utf8_decode('Palavra/Sigla'), 0, 0, 'C');
        $cx += $w2;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y);
        $this->Cell($w3, $h, utf8_decode('Definição'), 0, 0, 'C');
        $cx += $w3;
        $this->Line($cx, $y, $cx, $y + $h);

        $this->SetXY($cx, $y);
        $this->Cell($w4, $h, utf8_decode('Referência'), 0, 0, 'C');

        $this->Ln($h);

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

        $texto_completo = $this->textoRodape . ' - Página 1/1 (versão eletrônica)';
        $this->Cell(0, 10, utf8_decode($texto_completo), 0, 0, 'L');
    }
}

// 3. Inicia o PDF
$pdf = new PDF_Siglas('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->dataEmissao = $data_cabecalho;
$pdf->textoRodape = $rodape_input;
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

// 4. Busca os dados e preenche o PDF
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : null;
$data_de = isset($_GET['data_de']) && !empty($_GET['data_de']) ? $_GET['data_de'] : null;
$data_ate = isset($_GET['data_ate']) && !empty($_GET['data_ate']) ? $_GET['data_ate'] : null;

// Passa todos os filtros para a busca, garantindo que o PDF reflita a tela
$listaSiglas = $siglasModel->listarSiglas($busca, 1, $data_de, $data_ate); // Força status 1 (Em Vigor) para o PDF de siglas

$pdf->SetFont('Arial', '', 9);
$pdf->SetLineWidth(0.1);

$w_sigla = 60; $w_def = 145; $w_ref = 72;
$w_total = $w_sigla + $w_def + $w_ref;

$fill = false;

foreach ($listaSiglas as $sigla) {
    // Ignora siglas agendadas (status 2)
    if ($sigla['id_status'] != 1) {
        continue;
    }

    $txt_sigla = utf8_decode($sigla['nome_sigla']);
    $txt_def   = utf8_decode($sigla['definicao_sigla']);
    $txt_ref   = utf8_decode($sigla['referencia_sigla']);

    $nb_def = $pdf->NbLines($w_def, $txt_def);
    $nb_ref = $pdf->NbLines($w_ref, $txt_ref);
    $nb_sigla = $pdf->NbLines($w_sigla, $txt_sigla);

    $max_lines = max($nb_def, $nb_ref, $nb_sigla, 1);
    $h_line = 5;
    $h_row = $h_line * $max_lines;

    if ($pdf->GetY() + $h_row > 185) {
        $pdf->AddPage();
        $fill = false;
    }

    $x_start = $pdf->GetX();
    $y_start = $pdf->GetY();

    if ($fill) {
        $pdf->SetFillColor(240, 242, 245);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->Rect($x_start, $y_start, $w_total, $h_row, 'F');

    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetDrawColor(180, 180, 180);

    $cx = $x_start;
    $pdf->SetXY($cx, $y_start);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->MultiCell($w_sigla, $h_line, $txt_sigla, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Rect($cx, $y_start, $w_sigla, $h_row);

    $cx += $w_sigla;
    $pdf->SetXY($cx, $y_start);
    $pdf->MultiCell($w_def, $h_line, $txt_def, 0, 'L');
    $pdf->Rect($cx, $y_start, $w_def, $h_row);

    $cx += $w_def;
    $pdf->SetXY($cx, $y_start);
    $pdf->MultiCell($w_ref, $h_line, $txt_ref, 0, 'L');
    $pdf->Rect($cx, $y_start, $w_ref, $h_row);

    $pdf->SetXY($x_start, $y_start + $h_row);
    $fill = !$fill;
}

$pdf->SetDrawColor(44, 62, 80);
$pdf->Cell($w_total, 0, '', 'T');

$pdf->Output('I', 'Siglas_e_Definicoes.pdf');
?>
