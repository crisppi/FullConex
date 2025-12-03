<?php
require_once("vendor/autoload.php");

class CustomizacaoPDF extends TCPDF
{
    public function Header()
    {
        $logoPath = __DIR__ . '/img/LogoFullCare.png';
        $logoWidth = 30;
        $logoHeight = 12;
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 12, $logoWidth);
        }

        $this->SetFont('helvetica', 'B', 11);
        $this->SetXY(15 + $logoWidth + 6, 12);
        $this->Cell(0, 8, 'SOLICITAÇÃO DE CUSTOMIZAÇÃO – SISTEMA FULLCARE CONEXAUD', 0, 1, 'L');
        $this->Ln(3);
    }
}

$pdf = new CustomizacaoPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('Solicitação de Customização');
$pdf->SetMargins(15, 20, 15);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 9);

function addTextField(CustomizacaoPDF $pdf, string $label, string $name, float $width = 80, float $height = 6, bool $multiline = false)
{
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', '', 9);
    $margins = $pdf->getMargins();
    $baseX = $margins['left'] ?? 15;
    $labelWidth = 40;

    if ($label !== '') {
        $pdf->SetX($baseX);
        $pdf->Cell($labelWidth, 6, $label, 0, 1);
    }

    $fieldX = $baseX;
    $fieldY = $pdf->GetY();

    $options = [
        'lineWidth'   => 0.3,
        'borderColor' => [120, 120, 120],
    ];
    if ($multiline) {
        $options['multiline'] = true;
    }

    $pdf->SetXY($fieldX, $fieldY);
    $pdf->TextField($name, $width, $height, $options, [], $fieldX, $fieldY);

    $pdf->SetY($fieldY + $height + 4);
}

function addCheckbox(CustomizacaoPDF $pdf, string $label, string $name)
{
    $size = 4.5;
    $margins = $pdf->getMargins();
    $x = $margins['left'] ?? 15;
    $pdf->SetX($x);
    $pdf->CheckBox($name, $size);
    $pdf->SetX($pdf->GetX() + 2);
    $pdf->Cell(0, 5, $label, 0, 1, 'L');
    $pdf->Ln(1);
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '1. Identificação do Solicitante', 0, 1);

$pdf->SetFont('helvetica', '', 9);
addTextField($pdf, 'Nome:', 'nome', 120);
addTextField($pdf, 'Empresa:', 'empresa', 120);
addTextField($pdf, 'Cargo:', 'cargo', 120);
addTextField($pdf, 'E-mail:', 'email', 120);
addTextField($pdf, 'Telefone:', 'telefone', 80);
addTextField($pdf, 'Data da solicitação:', 'data_solicitacao', 50);

$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '2. Módulo a ser customizado', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$modules = [
    'Internação', 'Paciente', 'Hospital', 'Auditoria',
    'Financeiro', 'Relatórios', 'Outro'
];
foreach ($modules as $index => $module) {
    addCheckbox($pdf, $module . ($module === 'Outro' ? ': ____________' : ''), 'mod_' . $index);
}

$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '3. Tipo de Solicitação', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$tipos = [
    'Novo recurso',
    'Alteração de recurso existente',
    'Correção de erro',
    'Integração com outro sistema',
    'Layout/Visual',
    'Relatório/Exportação'
];
foreach ($tipos as $index => $tipo) {
    addCheckbox($pdf, $tipo, 'tipo_' . $index);
}

$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '4. Descrição objetiva da necessidade', 0, 1);
addTextField($pdf, '', 'descricao', 170, 30, true);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '5. Como funciona hoje (problema atual)', 0, 1);
addTextField($pdf, '', 'problema_atual', 170, 30, true);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '6. Como deve funcionar (resultado esperado)', 0, 1);
addTextField($pdf, '', 'resultado_esperado', 170, 30, true);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '7. Impacto se não for feito', 0, 1);
$pdf->SetFont('helvetica', '', 9);
addCheckbox($pdf, 'Baixo', 'impacto_baixo');
addCheckbox($pdf, 'Médio', 'impacto_medio');
addCheckbox($pdf, 'Alto', 'impacto_alto');
addTextField($pdf, 'Descrição do impacto:', 'descricao_impacto', 160, 20, true);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '8. Prioridade', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$prioridades = ['Urgente', 'Alta', 'Média', 'Baixa'];
foreach ($prioridades as $index => $prioridade) {
    addCheckbox($pdf, $prioridade, 'prioridade_' . $index);
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '9. Prazo desejado', 0, 1);
addTextField($pdf, 'Data estimada:', 'prazo_desejado', 70);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '10. Anexos (se houver)', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$anexos = ['Prints', 'Arquivos', 'Exemplos externos', 'Documentos'];
foreach ($anexos as $index => $anexo) {
    addCheckbox($pdf, $anexo, 'anexo_' . $index);
}

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '11. Aprovação', 0, 1);
$pdf->SetFont('helvetica', '', 9);
addTextField($pdf, 'Nome do responsável:', 'responsavel', 120);
addTextField($pdf, 'Assinatura:', 'assinatura', 120);
addTextField($pdf, 'Data:', 'data_aprovacao', 60);

$pdf->Ln(4);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, '12. Resposta da equipe FullCare/ConexAud', 0, 1);
$pdf->SetFont('helvetica', '', 9);
addTextField($pdf, 'Prazo estimado:', 'prazo_resposta', 70);
addTextField($pdf, 'Precificação/Estimativa de custo:', 'precificacao', 140, 12, true);
addTextField($pdf, 'Observações / Ajustes propostos:', 'observacoes_resposta', 140, 20, true);
addTextField($pdf, 'Aprovação final (responsável):', 'aprovacao_resposta', 120);
addTextField($pdf, 'Data:', 'data_resposta', 60);

$pdf->Ln(6);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->MultiCell(0, 5, 'Preencha os campos diretamente no PDF e encaminhe para o canal oficial de atendimento.', 0, 'L');

$pdf->Output('solicitacao_customizacao.pdf', 'I');
