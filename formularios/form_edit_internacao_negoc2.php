<?php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

/*──────────────────── helpers universais ───────────────────*/
if (!function_exists('h')) {
    function h($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
if (!function_exists('sel')) {
    /** selected — case-insensitive, suporta null/int  */
    function sel($v, $exp): string
    {
        return strcasecmp((string) $v, (string) $exp) === 0 ? 'selected' : '';
    }
}
if (!function_exists('fmtDate')) {
    /** Converte vários formatos para YYYY-MM-DD (para <input type="date">) */
    function fmtDate($d): string
    {
        if (!$d)
            return '';
        if ($d instanceof DateTimeInterface)
            return $d->format('Y-m-d');
        if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $d)) {
            $tmp = DateTime::createFromFormat('d/m/Y', $d);
            return $tmp ? $tmp->format('Y-m-d') : '';
        }
        if (preg_match('#^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$#', $d)) {
            return substr($d, 0, 10);
        }
        return (string) $d;
    }
}
if (!function_exists('getProp')) {
    function getProp($var, string $key): string
    {
        if (is_array($var) && isset($var[$key]))
            return (string) $var[$key];
        if (is_object($var) && isset($var->$key))
            return (string) $var->$key;
        return '';
    }
}

// Compatibilidade para PHP < 8.0
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

/*──────────────────── valida variáveis ─────────────────────*/
if (!isset($dados_acomodacao, $intern)) {
    exit('Variáveis necessárias não definidas');
}

/*──────────────────── normaliza negociações ─────────────────*/
/* ───────── normaliza NEGOCIAÇÕES + limpa id-hífen ───────── */
$negociacoesInt = array_map(static function ($n) {
    $n = (array) $n;                           // garante array

    // Se o valor tem hífen (ex.: 3-UTI), mantém só o que vem depois
    foreach (['troca_de', 'troca_para'] as $campo) {
        if (!empty($n[$campo]) && str_contains($n[$campo], '-')) {
            [$id, $nome] = explode('-', $n[$campo], 2);
            $n[$campo] = trim($nome);        // fica só “UTI”, “Apto” etc.
        }
    }
    return $n;
}, $negociacoesInt ?? []);

if (!$negociacoesInt) {
    $negociacoesInt[] = [
        'tipo_negociacao' => '',
        'data_inicio_neg' => '',
        'data_fim_neg' => '',
        'troca_de' => '',
        'troca_para' => '',
        'qtd' => '',
        'saving' => '',
    ];
}


/*──────────────────── funções de <option> ──────────────────*/
if (!function_exists('optionsTipoNegociacao')) {
    function optionsTipoNegociacao(string $sel = ''): string
    {
        $tipos = [
            'TROCA UTI/APTO',
            'TROCA UTI/SEMI',
            'TROCA SEMI/APTO',
            'VESPERA',
            'GLOSA UTI',
            'GLOSA APTO',
            'GLOSA SEMI',
            '1/2 DIARIA APTO',
            'TARDIA APTO',
            'TARDIA UTI',
            'DIARIA ADM'
        ];
        $html = '<option value="">Selecione</option>';
        foreach ($tipos as $t) {
            $html .= '<option value="' . h($t) . '" ' . sel($t, $sel) . '>' . h($t) . '</option>';
        }
        return $html;
    }
}
if (!function_exists('optionsAcomod')) {
    function optionsAcomod(array $acoms, string $sel = ''): string
    {
        sort($acoms, SORT_ASC);
        $html = '<option value=""></option>';

        foreach ($acoms as $ac) {
            // separa antes e depois do hífen (“3-UTI” → ["3", "UTI"])
            [$id, $nome] = array_pad(explode('-', $ac, 2), 2, '');
            $display = trim($nome) !== '' ? trim($nome) : $ac;  // se não houver hífen, mostra tudo
            $valorDia = rand(500, 1500);                         // troque pelo valor real

            $html .= '<option value="' . h($ac) . '" data-valor="' . $valorDia . '" '
                . sel($ac, $sel) . '>' . h($display) . '</option>';
        }
        return $html;
    }
}

if (!function_exists('sel')) {
    /**
     * Retorna 'selected'
     * - ignora maiúsc./minúsc.
     * - desconsidera espaços antes/depois
     */
    function sel($v, $exp): string
    {
        return strcasecmp(trim((string) $v), trim((string) $exp)) === 0
            ? 'selected' : '';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Negociações</title>

    <style>
        .negoc-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 12px;
            background: #fff
        }

        .negoc-row label {
            font-weight: 600;
            font-size: .9rem;
            margin-bottom: 4px
        }

        .negoc-row .form-control {
            font-size: .9rem;
            padding: 4px 6px
        }

        .negoc-row .btn {
            font-size: .85rem;
            padding: 4px 8px
        }

        .titulo-abas {
            background: #0d6efd;
            padding: 6px 10px;
            border-radius: 4px 4px 0 0;
            margin-bottom: 6px
        }

        .titulo-abas h7 {
            color: #fff;
            margin: 0
        }

        #container-negoc {
           
        }
    </style>
</head>


<div>
    <h4 class="mb-3">Editar Negociação</h4>


    <!-- garante que cai no fluxo UPDATE/CREATE -->
    <input type="hidden" name="type" value="update_editar">

    <!-- toggle que ativa/desativa o bloco de negociações -->
    <input type="hidden" name="select_negoc" id="select_negoc" value="s">

    <!-- aqui cai o JSON montado pelo JS -->
    <!-- chaves principais -->
    <input type="hidden" name="type" value="edit_negociacao">
    <input type="hidden" id="fk_id_int" value="<?= h(getProp($intern, 'id_internacao')) ?>">
    <input type="hidden" id="fk_usuario_neg" value="<?= h($_SESSION['id_usuario'] ?? '') ?>">
    <input type="hidden" id="negociacoes_json" name="negociacoes_json">


    <div id="negotiationFieldsContainer" >
        <?php foreach ($negociacoesInt as $neg): ?>
            <div  class="negociation-field-container negoc-row">
                <div class="form-group col-sm-2">
                    <label>
                        Tipo Negociação
                        <span class="assist-anchor" data-assist-key="negociacao_tipo"></span>
                    </label>
                    <select name="tipo_negociacao" class="form-control">
                        <?= optionsTipoNegociacao($neg['tipo_negociacao'] ?? '') ?>
                    </select>
                </div>

                <div class="form-group col-sm-1">
                    <label>Data inicial</label>
                    <input type="date" name="data_inicio_neg" class="form-control"
                        value="<?= h(fmtDate($neg['data_inicio_neg'] ?? $neg['data_inicio_neg'] ?? '')) ?>">
                </div>

                <div class="form-group col-sm-1">
                    <label>Data final</label>
                    <input type="date" name="data_fim_neg" class="form-control"
                        value="<?= h(fmtDate($neg['data_fim_neg'] ?? $neg['data_fim_neg'] ?? '')) ?>">
                </div>

                <div class="form-group col-sm-2">
                    <?php /* DEBUG */
                    echo '<!-- valor banco: [', h($neg['troca_de'] ?? ''), '] -->';
                    ?>

                    <label>Acomod. Solicitada</label>
                    <select name="troca_de" class="form-control">
                        <?= optionsAcomod($dados_acomodacao, $neg['troca_de'] ?? '') ?>
                    </select>
                </div>

                <div class="form-group col-sm-2">
                    <label>Acomod. Liberada</label>
                    <select name="troca_para" class="form-control">
                        <?= optionsAcomod($dados_acomodacao, $neg['troca_para'] ?? '') ?>
                    </select>
                </div>

                <div class="form-group col-sm-1">
                    <label>Quantidade</label>
                    <input type="number" name="qtd" class="form-control" min="1" max="30"
                        value="<?= h($neg['qtd'] ?? '') ?>">
                </div>

                <div class="form-group col-sm-1">
                    <label>Saving</label>
                    <input type="text" name="saving_show" class="form-control" readonly
                        value="<?= ($neg['saving'] ?? '') !== '' ? 'R$ ' . number_format((float) $neg['saving'], 2, ',', '.') : '' ?>">
                    <input type="hidden" name="saving" value="<?= h($neg['saving'] ?? '') ?>">
                </div>
                <div class="form-group col-md-1 mb-2" style="margin-top:25px;">
                    <button type="button" class="btn btn-success btn-sm" onclick="addNegotiationField()">+</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeNegotiationField(this)">−</button>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
    <hr>
</div>

<script>
    function safeNum(n) {
        const v = parseFloat(n);
        return isFinite(v) ? v : 0;
    }

    function calcSaving($c) {
        const tipo = $c.find('[name="tipo_negociacao"]').val().toUpperCase().trim();
        const de = safeNum($c.find('[name="troca_de"]   option:selected').data('valor'));
        const para = safeNum($c.find('[name="troca_para"] option:selected').data('valor'));
        const qtd = parseInt($c.find('[name="qtd"]').val(), 10) || 0;
        let s = 0;
        if (tipo.startsWith('TROCA')) s = (de - para) * qtd;
        else if (tipo.includes('1/2 DIARIA')) s = qtd * (de / 2);
        else s = qtd * de;
        $c.find('[name="saving"]').val(s.toFixed(2));
        $c.find('[name="saving_show"]').val(`R$ ${Math.abs(s).toFixed(2)}`)
            .css('color', s >= 0 ? 'green' : 'red');
    }

    function genJSON() {
        const arr = [];
        $('#negotiationFieldsContainer .negociation-field-container').each(function () {
            const $c = $(this);
            const item = {
                id: $c.find('[name$="[id]"]').val() || '',
                tipo_negociacao: $c.find('[name="tipo_negociacao"]').val(),
                data_inicio_neg: $c.find('[name="data_inicio_neg"]').val(),
                data_fim_neg: $c.find('[name="data_fim_neg"]').val(),
                troca_de: $c.find('[name="troca_de"]').val(),
                troca_para: $c.find('[name="troca_para"]').val(),
                qtd: parseInt($c.find('[name="qtd"]').val(), 10) || 0,
                saving: parseFloat($c.find('[name="saving"]').val()) || 0
            };
            // só adiciona se tiver tipo e qtd
            if (item.tipo_negociacao && item.qtd) {
                arr.push(item);
            }
        });
        $('#negociacoes_json').val(JSON.stringify(arr));
    }

    // dispara a cada mudança
    $('#negotiationFieldsContainer').on('input change', 'select,input', function () {
        const $row = $(this).closest('.negociation-field-container');
        calcSaving($row);
        genJSON();
    });
    // também no add/remove
    $('.btn-add-negoc, .btn-del-negoc').on('click', genJSON);
    // e no submit do form, pra garantir
    $('form').on('submit', genJSON);

    // inicializa
    $(function () {
        $('#negotiationFieldsContainer .negociation-field-container').each(function () {
            calcSaving($(this));
        });
        genJSON();
    });

    let debounce;
    $(document).on('input', '#negotiationFieldsContainer :input', function () {
        const $r = $(this).closest('.negociation-field-container');
        calcSaving($r);
        clearTimeout(debounce);
        debounce = setTimeout(genJSON, 200);
    });

    function addNegotiationField() {
        const $new = $('.negociation-field-container').last().clone();
        $new.find('input,select').not('[type="hidden"]').val('');
        $new.insertAfter($('.negociation-field-container').last());
    }

    function removeNegotiationField(btn) {
        if ($('.negociation-field-container').length > 1) {
            $(btn).closest('.negociation-field-container').remove();
            genJSON();
        }
    }

    $(function () {
        $('#negotiationFieldsContainer .negociation-field-container').each(function () {
            calcSaving($(this));
        });
        genJSON();
    });
</script>

</html>
