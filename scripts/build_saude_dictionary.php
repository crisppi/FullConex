<?php

/**
 * Gera o arquivo data/saude_terms.json com uma lista deduplicada de termos de saúde.
 * Fonte principal: scripts/correlacaotuss-rol_2021_site.csv (ROL/ANS) +
 * arrays internos (acomodação, especialidade etc.).
 *
 * Uso:
 *   php scripts/build_saude_dictionary.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$rootDir = dirname(__DIR__);
$csvFile = $rootDir . '/scripts/correlacaotuss-rol_2021_site.csv';
$outputDir = $rootDir . '/data';
$outputFile = $outputDir . '/saude_terms.json';
$maxTerms = 2000;

require_once $rootDir . '/array_dados.php';

$collector = [];

$addTerm = function (?string $term) use (&$collector) {
    if (!$term) {
        return;
    }
    $normalized = trim($term);
    if ($normalized === '') {
        return;
    }
    if (!preg_match('/[0-9A-Za-zÀ-ÿ]/u', $normalized)) {
        return;
    }
    // Mantém caixa original, mas usa chave em minúsculas para deduplicar
    $key = mb_strtolower($normalized, 'UTF-8');
    $collector[$key] = $normalized;
};

$addArray = function ($data) use ($addTerm) {
    if (!is_iterable($data)) {
        return;
    }
    foreach ($data as $value) {
        if (is_array($value)) {
            $addTerm(implode(' ', $value));
            continue;
        }
        $addTerm((string) $value);
    }
};

$arraySources = [
    $dados_acomodacao ?? [],
    $dados_UTI ?? [],
    $dados_saps ?? [],
    $dados_especialidade ?? [],
    $criterios_UTI ?? [],
    $modo_internacao ?? [],
    $origem ?? [],
    $tipo_admissao ?? [],
    $dados_grupo_pat ?? [],
    $dados_tipo_evento ?? [],
    $dados_alta ?? [],
    $cargo_user ?? [],
    $depto_sel ?? [],
    $vinculo_sel ?? [],
    $tipo_reg ?? [],
    $estado_sel ?? [],
    $tipos_dieta ?? [],
    $opcoes_nivel_consc ?? [],
    $opcoes_oxigenio ?? [],
];

foreach ($arraySources as $source) {
    $addArray($source);
}

if (is_file($csvFile)) {
    $fh = new SplFileObject($csvFile);
    $fh->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
    $fh->setCsvControl(';');
    foreach ($fh as $row) {
        if (!is_array($row)) {
            continue;
        }
        $row = array_map('trim', $row);
        // Colunas 1 e 3/4 possuem descrições úteis
        $addTerm($row[1] ?? null);
        $addTerm($row[3] ?? null);
        $addTerm($row[4] ?? null);
        if (count($collector) >= $maxTerms * 1.5) {
            break;
        }
    }
}

$terms = array_values($collector);
natcasesort($terms);
$terms = array_values($terms);
$terms = array_slice($terms, 0, $maxTerms);

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0775, true);
}

$json = json_encode($terms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false) {
    throw new RuntimeException('Falha ao gerar JSON: ' . json_last_error_msg());
}

file_put_contents($outputFile, $json);

echo sprintf("Gerados %d termos em %s\n", count($terms), $outputFile);
