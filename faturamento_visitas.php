<?php
// Página dedicada para o faturamento de visitas.
// Força o contexto "faturamento" e reutiliza a listagem principal.
$_GET['context'] = 'faturamento';
include __DIR__ . '/lista_visitas.php';
