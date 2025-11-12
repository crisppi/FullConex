<?php

$dados_acomodacao = ["UTI", "Apto", "Enfermaria", "Semi", "Day Clinic"];

$dados_UTI = [
    "Insuficência respiratória",
    "Choque cardiogênico",
    "Choque séptico",
    "Distúrbio metabólico severo"
];

$dados_saps = ["0-20", "21-40", "41-61", "61-80", ">81"];

$dados_especialidade = [
    "Clínica",
    "Pediatria",
    "Ginecologia",
    "Cirurgia Geral",
    "Cirurgia Vascular",
    "Cirurgia Torácica",
    "Dermatologia",
    "Reumatologia",
    "Cirurgia Pediátrica",
    "Cardiologia",
    "Nefrologia",
    "Otorrino",
    "Gastroenterologia",
    "Pneumologia",
    "Obstetrícia",
    "Ortopedia",
    "Psiquiatria",
    "Urologia"
];

$criterios_UTI = [
    "Instabilidade Clínica",
    "Pós operatório Neurocirurgia",
    "Pós operatório de Cir Cardíaca",
    "Distúrbio Metabólico severo",
    "Choque cardiogênico",
    "Choque hipovolêmico",
    "Choque séptico",
    "Insuficiência Respiratória",
    "Sepse",
    "IAM"
];

$modo_internacao = [
    "Clínica",
    "Cirúrgica",
    "Pediátrica",
    "Ortopédica",
    "Psiquiátrica",
    "Obstétrica"
];
$origem = [
    "Domicílio",
    "Consultório",
    "Transferido",
    "Home care"

];

$tipo_admissao = ["Eletiva", "Urgência"];

$dados_grupo_pat = [
    "Cardiologia",
    "Pediatria",
    "Oncologia",
    "Urologia",
    "Neurologia",
    "Pneumologia",
    "Infectologia",
    "Reumatologia",
    "Cirurgia Geral",
    "Cirurgia Vascular",
    "Cirurgia Oncológica",
    "Cirurgia Neurológica"
];

$dados_tipo_evento = [
    "",
    "Úlcera pressão",
    "Queda",
    "Broncoaspiração",
    "Infecção hospitalar",
    "Infecção cirúrgica",
    "Complicação pós operatória",
    "TVP",
    "Flebite",
    "Outros",
    "Reação transfusional"
];

$dados_alta = [
    "Atenção Domiciliar",
    "Instituição de longa permanência",
    "Óbito",
    "Melhorado",
    "a Pedido",
    "Transferência"
];

$cargo_user = ["Med_auditor", "Enf_Auditor", "Administrativo", "Diretoria", "Gerência", "Secretária", "Hospital"];

$depto_sel = ["Auditoria", "Adm", "Diretoria", "Gerência", "Secretaria"];

$vinculo_sel = ["CLT", "Terceiro", "Outras"];

$tipo_reg = ["CRM", "Coren", "Crefito"];

$estado_sel =
    ["AC", "AL", "AP", "AM", "BA", "CE", "ES", "GO", "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI", "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO", "DF"];

$tipos_dieta = [
    'Oral',
    'Enteral',
    'NPP',
    'Jejum',
    // se amanhã quiser “Parenteral”, é só acrescentar aqui 😉
];

$opcoes_nivel_consc = ['Consciente', 'Comatoso', 'Vigil'];

$opcoes_oxigenio = ['Cateter', 'Mascara', 'VNI', 'Alto Fluxo'];