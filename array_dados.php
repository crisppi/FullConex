<?php

$dados_acomodacao = ["UTI", "UTI Pediátrica", "UTI NEO", "SEMI NEO", "Apto", "Enfermaria", "Semi", "Day Clinic", "Berçário"];

$dados_UTI = [
    "Insuficência respiratória",
    "Choque cardiogênico",
    "Choque séptico",
    "Distúrbio metabólico severo"
];

$dados_saps = ["0-20", "21-40", "41-61", "61-80", ">81"];

$dados_especialidade = [
    "Acupuntura",
    "Alergia e Imunologia",
    "Anestesiologia",
    "Angiologia",
    "Cancerologia / Oncologia",
    "Cardiologia",
    "Cirurgia Plástica",
    "Cirurgia Cardiovascular",
    "Cirurgia de Cabeça e Pescoço",
    "Cirurgia da Mão",
    "Cirurgia do Aparelho Digestivo",
    "Cirurgia Geral",
    "Cirurgia Pediátrica",
    "Cirurgia Torácica",
    "Cirurgia Vascular",
    "Clínica Médica",
    "Coloproctologia",
    "Dermatologia",
    "Endocrinologia e Metabologia",
    "Endoscopia",
    "Gastroenterologia",
    "Genética Médica",
    "Geriatria",
    "Ginecologia e Obstetrícia",
    "Hematologia e Hemoterapia",
    "Homeopatia",
    "Infectologia",
    "Mastologia",
    "Medicina de Família e Comunidade",
    "Medicina de Tráfego",
    "Medicina do Trabalho",
    "Medicina Física e Reabilitação",
    "Medicina Intensiva",
    "Medicina Legal e Perícia Médica",
    "Medicina Nuclear",
    "Medicina Preventiva e Social",
    "Nefrologia",
    "Neurocirurgia",
    "Nutrologia",
    "Oftalmologia",
    "Ortopedia e Traumatologia",
    "Otorrinolaringologia",
    "Patologia",
    "Patologia Clínica/Medicina Laboratorial",
    "Pediatria",
    "Pneumologia",
    "Psiquiatria",
    "Radiologia e Diagnóstico por imagem",
    "Radioterapia",
    "Reumatologia",
    "Urologia",
    "Buco-maxilo-facial",
    "Bucomaxilo – CRO",
    "Cirurgia Oncológica",
    "Internações Clínicas"
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
    "Cirúrgica"
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
    "Alta Curado",
    "Alta Melhorado",
    "Alta por evasão",
    "Residência",
    "Transferido",
    "Óbito",
    "A pedido",
    "Home-Care",
    "Alta Administrativa"
];

$cargo_user = ["Administrativo", "Analista", "Diretoria", "Enf_Auditor", "Gerência", "Hospital", "Med_auditor", "Secretária"];

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
