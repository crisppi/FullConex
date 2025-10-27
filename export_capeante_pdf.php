<?php

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

/**
 * ==========================================================
 *  export_capeante_pdf.php
 *  Gera PDF no layout RAH a partir do Capeante
 *  JOIN: tb_capeante + tb_internacao + tb_paciente + tb_hospital + (última) tb_prorrogacao
 * ==========================================================
 * URL: export_capeante_pdf.php?id_capeante=123
 */

include_once("check_logado.php");
require_once("templates/header.php");

// Se seu projeto já possui config com $conn (PDO), mantenha-a.
// require_once "config.php";

require_once __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';

// ====================== INPUT ======================
$idCapeante = isset($_GET['id_capeante']) ? (int) $_GET['id_capeante'] : 0;
if ($idCapeante <= 0) {
    http_response_code(400);
    echo 'Parâmetro id_capeante obrigatório.';
    exit;
}

// ====================== HELPERS ======================
function brl(float $v): string
{
    return 'R$ ' . number_format($v, 2, ',', '.');
}
function dt(?string $d): string
{
    if (!$d) return '';
    $t = strtotime($d);
    return $t ? date('d/m/Y', $t) : '';
}
function safe(?string $s = null): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function linhaSetor(
    string $rotulo,
    int $qtd,
    float $cobrado,
    float $antesAud,
    float $glosado,
    float $aposAud
): string {
    return '
    <tr>
        <td style="width:26%;"><b>' . safe($rotulo) . '</b></td>
        <td style="width:6%; text-align:center;">' . ($qtd) . '</td>
        <td style="width:17%; text-align:right;">' . brl($cobrado) . '</td>
        <td style="width:17%; text-align:right;">' . brl($antesAud) . '</td>
        <td style="width:17%; text-align:right;">' . brl($glosado) . '</td>
        <td style="width:17%; text-align:right;">' . brl($aposAud) . '</td>
    </tr>';
}
function thGrupo(string $titulo): string
{
    return '
    <tr style="background-color:#f1f1f1;">
        <td colspan="6" style="padding:6px 8px;"><b>' . safe($titulo) . '</b></td>
    </tr>';
}

// ====================== DADOS (PREFIRA O DAO) ======================
/**
 * Se você já implementou o método no capeanteDAO:
 *   $capDao = new capeanteDAO($conn, $BASE_URL ?? '');
 *   $dados  = $capDao->getCapeanteForRAH($idCapeante);
 * Se não, usamos a query abaixo (compatível com os campos do seu schema).
 */

// Tenta usar DAO se existir
$dados = null;
if (class_exists('capeanteDAO')) {
    try {
        $capDao = new capeanteDAO($conn, $BASE_URL ?? '');
        if (method_exists($capDao, 'getCapeanteForRAH')) {
            $dados = $capDao->getCapeanteForRAH($idCapeante);
        }
    } catch (\Throwable $e) {
        // fallback para SQL cru abaixo
        $dados = null;
    }
}

if (!$dados) {
    // Fallback SQL (sem // dentro do SQL!)
    $sql = "
        SELECT
            -- HOSPITAL
            h.id_hospital,
            h.nome_hosp,
            h.cnpj_hosp,

            -- PACIENTE
            p.id_paciente,
            p.nome_pac                 AS paciente_nome,
            p.data_nasc_pac            AS paciente_nasc,
            p.cpf_pac                  AS paciente_cpf,
            p.matricula_pac            AS internacao_matricula,


            -- INTERNAÇÃO
            i.id_internacao,
            i.senha_int                AS senha_aut,
            i.num_atendimento_int      AS numero_atendimento,
            i.data_intern_int          AS data_internacao,
            i.data_visita_int          AS data_visita_ref,
            i.acomodacao_int,
            i.modo_internacao_int,
            i.tipo_admissao_int,
            i.rel_int                  AS relatorio_internacao,
            i.hora_intern_int,
            i.fk_patologia_int,
            i.fk_patologia2,
            i.fk_paciente_int,
            i.fk_hospital_int,
            i.internado_int,

            -- CAPEANTE
            c.id_capeante,
            c.fk_int_capeante,
            c.data_inicial_capeante,
            c.data_final_capeante,
            c.diarias_capeante,

            c.valor_diarias,
            c.valor_taxa,
            c.valor_matmed,
            c.valor_sadt,
            c.valor_honorarios,
            c.valor_oxig,
            c.valor_opme,
            c.valor_materiais,
            c.valor_medicamentos,

            c.glosa_diaria,
            c.glosa_taxas,
            c.glosa_matmed,
            c.glosa_sadt,
            c.glosa_honorarios,
            c.glosa_oxig,
            c.glosa_opme,
            c.glosa_materiais,
            c.glosa_medicamentos,
            c.glosa_total,

            c.valor_apresentado_capeante,
            c.valor_final_capeante,
            c.desconto_valor_cap         AS desconto_valor,
            c.parcial_capeante,
            c.parcial_num,
            c.senha_finalizada,
            c.adm_check,
            c.med_check,
            c.enfer_check,
            c.conta_faturada_cap,

            -- PRORROGAÇÃO mais recente (se houver)
            pr.prorrog1_ini_pror         AS prorrogacao_ini,
            pr.prorrog1_fim_pror         AS prorrogacao_fim

        FROM tb_capeante c
        LEFT JOIN tb_internacao i
               ON i.id_internacao = c.fk_int_capeante
        LEFT JOIN tb_paciente p
               ON p.id_paciente   = i.fk_paciente_int
        LEFT JOIN tb_hospital h
               ON h.id_hospital   = i.fk_hospital_int
        LEFT JOIN tb_prorrogacao pr
               ON pr.fk_internacao_pror = i.id_internacao
        WHERE c.id_capeante = :id
        ORDER BY pr.prorrog1_ini_pror DESC
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $idCapeante]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$dados) {
    http_response_code(404);
    echo 'Capeante não encontrado.';
    exit;
}

// ====================== MAPEAMENTO CABEÇALHO ======================
$hospitalNome    = $dados['nome_hosp'] ?? '';
$hospitalCNPJ    = $dados['cnpj_hosp'] ?? '';
$pacienteNome    = $dados['paciente_nome'] ?? '';
$pacienteIdade   = '';
if (!empty($dados['paciente_nasc'])) {
    try {
        $nasc = new DateTime($dados['paciente_nasc']);
        $hoje = new DateTime('today');
        $pacienteIdade = $nasc->diff($hoje)->y . ' anos';
    } catch (\Throwable $e) {
        $pacienteIdade = '';
    }
}
$matricula       = $dados['internacao_matricula'] ?? ($dados['matricula'] ?? '');
$senhaAut        = $dados['senha_aut'] ?? '';
$codigoOperadora = $dados['codigo_operadora'] ?? ''; // se existir no seu schema
$dataInternacao  = $dados['data_internacao'] ?? '';
// Alta: se você tiver tb_alta, pode mapear; por ora usa fim do capeante
$dataAlta        = $dados['data_final_capeante'] ?? ($dados['data_alta_internacao'] ?? '');
$periodoIni      = $dados['data_inicial_capeante'] ?? $dataInternacao;
$periodoFim      = $dados['data_final_capeante'] ?? ($dados['data_visita_ref'] ?? $dataInternacao);
$tipoConta       = $dados['tipo_conta'] ?? 'Conta Única';
$contaUnica      = 'Sim';
$auditorResp     = $dados['auditor_responsavel'] ?? '';

$prorrogacaoTxt = (!empty($dados['prorrogacao_ini']) || !empty($dados['prorrogacao_fim']))
    ? (dt($dados['prorrogacao_ini']) . ' a ' . dt($dados['prorrogacao_fim']))
    : '';

// ====================== QUADRO (enquanto sem tabela de itens) ======================
// Distribui valores/grupos do capeante em linhas “genéricas” para o RAH.
// Depois, quando ligar a tb_capeante_item, basta substituir por agregações reais.

$linhas = [];
$append = function (array $dst, string $grupo, string $rotulo, float $cobrado, float $glosa) {
    $antes = $cobrado;                  // “Antes da Auditoria” = cobrado
    $apos  = max(0.0, $cobrado - $glosa);
    $dst[] = [
        'grupo'   => $grupo ?: null,
        'rotulo'  => $rotulo,
        'qtd'     => 0,
        'cobrado' => $cobrado,
        'antes'   => $antes,
        'glosado' => $glosa,
        'apos'    => $apos,
    ];
    return $dst;
};

$val = fn(string $k) => (float)($dados[$k] ?? 0);
$linhas = $append($linhas, 'DESPESAS ENFERMARIA', 'DIÁRIAS (APTO/ENF.)', $val('valor_diarias'),   $val('glosa_diaria'));
$linhas = $append($linhas, 'DESPESAS ENFERMARIA', 'TAXAS / ALUGUÉIS',   $val('valor_taxa'),      $val('glosa_taxas'));
$linhas = $append($linhas, 'DESPESAS ENFERMARIA', 'MATERIAL DE CONSUMO', $val('valor_matmed'),    $val('glosa_matmed'));
$linhas = $append($linhas, 'DESPESAS ENFERMARIA', 'MEDICAMENTOS',       $val('valor_medicamentos'), $val('glosa_medicamentos'));
$linhas = $append($linhas, 'DESPESAS ENFERMARIA', 'GASES MEDICINAIS',   $val('valor_oxig'),      $val('glosa_oxig'));
$linhas = $append($linhas, 'DESPESAS ENFERMARIA', 'EXAMES / SADT',      $val('valor_sadt'),      $val('glosa_sadt'));

$linhas = $append($linhas, 'DESPESAS CENTRO CIRÚRGICO', 'MATERIAL ESPECIAL / OPME', $val('valor_opme'), $val('glosa_opme'));
$linhas = $append($linhas, 'DESPESAS CENTRO CIRÚRGICO', 'HONORÁRIOS',              $val('valor_honorarios'), $val('glosa_honorarios'));

$totCobrado = 0.0;
$totAntes   = 0.0;
$totGlosa   = 0.0;
$totApos    = 0.0;
foreach ($linhas as $l) {
    $totCobrado += (float)$l['cobrado'];
    $totAntes   += (float)$l['antes'];
    $totGlosa   += (float)$l['glosado'];
    $totApos    += (float)$l['apos'];
}
// Se existir desconto no capeante, usa
$desconto   = (float)($dados['desconto_valor'] ?? 0.0);
$valorFinal = max(0.0, $totApos - $desconto);

// ====================== TCPDF ======================
class PDFCapeanteRAH extends TCPDF
{
    public function Header() {}
    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 10, 'Gerado por FullCare • ' . date('d/m/Y H:i'), 0, 0, 'R');
    }
}

$pdf = new PDFCapeanteRAH('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('FullCare');
$pdf->SetAuthor('FullCare');
$pdf->SetTitle('Relatório de Auditoria Hospitalar');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 6, 'Relatório de Auditoria Hospitalar (RAH)', 0, 1, 'L');

$pdf->Ln(1);
$pdf->SetFont('helvetica', '', 9);

$headHtml = '
<table cellpadding="3" cellspacing="0" border="0" width="100%" style="line-height:1.3;">
  <tr>
    <td width="50%"><b>Referenciado:</b> ' . safe($hospitalNome) . '</td>
    <td width="25%"><b>Senha:</b> ' . safe($senhaAut) . '</td>
    <td width="25%"><b>Data de Internação:</b> ' . dt($dataInternacao) . '</td>
  </tr>
  <tr>
    <td><b>Cód. do Contratado:</b> ' . safe($codigoOperadora) . '</td>
    <td><b>Data de Alta:</b> ' . dt($dados['data_final_capeante'] ?? $dataAlta) . '</td>
    <td><b>Conta Auditada?</b> Sim</td>
  </tr>
  <tr>
    <td><b>Paciente:</b> ' . safe($pacienteNome) . '</td>
    <td><b>Idade:</b> ' . safe($pacienteIdade) . '</td>
    <td><b>Matrícula:</b> ' . safe($matricula) . '</td>
  </tr>
  <tr>
    <td><b>Período de Cobrança:</b> ' . dt($periodoIni) . ' a ' . dt($periodoFim) . '</td>
    <td><b>Tipo de Conta:</b> ' . safe($tipoConta) . '</td>
    <td><b>Conta Única?</b> ' . safe($contaUnica) . '</td>
  </tr>'
    . (!empty($prorrogacaoTxt) ? '<tr><td colspan="3"><b>Prorrogação vigente:</b> ' . $prorrogacaoTxt . '</td></tr>' : '')
    . '</table>';
$pdf->writeHTML($headHtml, true, false, false, false, '');

// Tabela de setores
$tbl = '
<style>
  .rah th { background-color:#e9ecef; font-weight:bold; }
  .rah td, .rah th { border:1px solid #cfcfcf; }
</style>

<table class="rah" cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <th style="width:26%;">Procedimento (em acordo p/ Pacote?)</th>
    <th style="width:6%;">Qtd.</th>
    <th style="width:17%;">Cobrado</th>
    <th style="width:17%;">Antes da Auditoria</th>
    <th style="width:17%;">Glosado</th>
    <th style="width:17%;">Após a Auditoria</th>
  </tr>';
$grupoAtual = null;
foreach ($linhas as $l) {
    if (!empty($l['grupo']) && $l['grupo'] !== $grupoAtual) {
        $grupoAtual = $l['grupo'];
        $tbl .= thGrupo($grupoAtual);
    }
    $tbl .= linhaSetor($l['rotulo'], (int)$l['qtd'], (float)$l['cobrado'], (float)$l['antes'], (float)$l['glosado'], (float)$l['apos']);
}
$tbl .= '</table>';

$pdf->Ln(2);
$pdf->writeHTML($tbl, true, false, true, false, '');

// Totais
$pdf->Ln(1);
$totHtml = '
<table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="25%"><b>Desconto:</b> ' . brl($desconto) . '</td>
    <td width="25%"><b>Valor Total:</b> ' . brl($totApos) . '</td>
    <td width="50%" style="text-align:right;"><b>Valor Final:</b> ' . brl($valorFinal) . '</td>
  </tr>
</table>';
$pdf->writeHTML($totHtml, true, false, false, false, '');

// Observações / Comentários
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 9);
$comentario = $dados['comentario_auditoria'] ?? '';
$pdf->MultiCell(0, 6, "Comentário: " . ($comentario !== '' ? $comentario : '—'), 0, 'L', false, 1);

// Assinatura / Identificação
$pdf->Ln(4);
$assinaturaHtml = '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
  <tr>
    <td width="60%">
      <b>Auditor(a):</b> ' . safe($auditorResp) . ' &nbsp;&nbsp;&nbsp;
      <b>Data:</b> ' . date('d/m/Y') . '
    </td>
    <td width="40%" style="text-align:right;">
      <b>' . safe($hospitalNome) . '</b> &nbsp;&nbsp; CNPJ: ' . safe($hospitalCNPJ) . '
    </td>
  </tr>
</table>';
$pdf->writeHTML($assinaturaHtml, true, false, false, false, '');

// Output
$pdf->Output('RAH_Capeante_' . $idCapeante . '.pdf', 'I');