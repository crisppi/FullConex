<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare • Internações & TUSS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div class="page page--module">
        <header class="hero module-hero">
            <nav class="hero__nav">
                <div class="brand">
                    <a href="index.php">
                        <img src="../img/logo_fullcare_branco_large.png" alt="FullCare • Gestão em Saúde" class="brand__full-logo">
                    </a>
                </div>
                <div class="hero__nav-links">
                    <a href="index.php#sobre">Sobre</a>
                    <a href="index.php#diferenciais">Diferenciais</a>
                    <a href="index.php#fluxo">Fluxo</a>
                    <a href="index.php#modulos">Módulos</a>
                    <a href="index.php#contato">Contato</a>
                </div>
                <a class="btn btn--ghost" href="index.php">← Voltar</a>
            </nav>
            <div class="hero__body">
                <div class="module-hero__content">
                    <p class="eyebrow">Internações • TUSS conectada</p>
                    <h1>Controle absoluto dos procedimentos TUSS dentro da internação</h1>
                    <p class="lead">Capturamos solicitações de materiais, OPME e diárias especiais no mesmo fluxo da internação.
                        Tudo fica amarrado ao capeante, PDFs e painéis automatizados sem planilhas paralelas.</p>
                    <div class="module-hero__stats">
                        <span>Integração direta com process_tuss.php</span>
                        <span>Alertas de glosa em tempo real</span>
                        <span>Reflexo automático no capeante</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="module-main">
            <section class="module-section">
                <h2>Fluxo guiado de TUSS</h2>
                <p class="module-section__intro">O time registra códigos TUSS com validações de data, status e impacto financeiro.
                    O sistema sugere próximos passos e destaca itens críticos antes mesmo do faturamento.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Registro inteligente</h3>
                        <ul class="module-list">
                            <li>Modal único com código, descrição, quantidade e valores.</li>
                            <li>Status “Em análise”, “Autorizado” ou “Negado” com badge na linha.</li>
                            <li>Validação automática com período da internação/prorrogação vigente.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Histórico e auditoria</h3>
                        <ul class="module-list">
                            <li>Edição inline com log de usuário e horário.</li>
                            <li>Exclusões pedem justificativa e mantêm rastro no PDF.</li>
                            <li>Bloqueio de duplicidade (paciente + código + data) para manter consistência.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Alertas de glosa</h3>
                        <ul class="module-list">
                            <li>Itens negados destacam tooltip com motivo.</li>
                            <li>Badge vermelho quando 30%+ do TUSS é glosado.</li>
                            <li>Lista geral de internações recebe filtro “Glosas TUSS”.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="module-section">
                <h2>Capeante, edição e PDFs</h2>
                <p class="module-section__intro">Os mesmos dados alimentam o capeante sem retrabalho. Assim que o auditor abre o formulário,
                    as tabelas já trazem totais organizados por tipo de procedimento.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Edição dinâmica</h3>
                        <ul class="module-list">
                            <li>Aba Capeante mostra TUSS agrupado (diárias, taxas, OPME).</li>
                            <li>Recalcula totais ao ajustar valores negociados.</li>
                            <li>Bloqueia envio se houver item “Em análise”.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Geração de PDF</h3>
                        <ul class="module-list">
                            <li>process_capeante_pdf.php imprime cada TUSS com subtotais.</li>
                            <li>Itens glosados aparecem com destaque vermelho e legenda.</li>
                            <li>Resumo das negociações aprovadas surge antes da assinatura.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Envio automatizado</h3>
                        <ul class="module-list">
                            <li>process_capeante_pdf (email OK).php dispara arquivos para seguradora.</li>
                            <li>Assunto padrão “Capeante • Paciente • Parcial N”.</li>
                            <li>Logs detalham qualquer falha em export_capeante_pdf.error.log.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="module-section">
                <h2>Painéis automatizados</h2>
                <p class="module-section__intro">Os registros abastecem dashboards operacionais e exportações para BI sem intervenção manual.</p>
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Origem</th>
                            <th>O que mostra</th>
                            <th>Atualização</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>dashboard_operacional.php</td>
                            <td>KPIs de TUSS autorizado x negado por hospital.</td>
                            <td>Tempo real.</td>
                        </tr>
                        <tr>
                            <td>export_negociacoes_graficos.php</td>
                            <td>Base CSV para painéis automatizados no BI.</td>
                            <td>Agendado 3x ao dia.</td>
                        </tr>
                        <tr>
                            <td>Alertas de glosa</td>
                            <td>Notificações e filtros nas listas de internação.</td>
                            <td>Imediato ao salvar TUSS.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="module-section">
                <h2>Benefícios para a operação</h2>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <span class="module-tagline">Precisão</span>
                        <p>Sem TUSS fora de período e sem números divergentes entre internação e capeante.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Agilidade</span>
                        <p>PDFs e envios automáticos saem com o mesmo dado registrado em tela.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Visibilidade</span>
                        <p>Dashboards e exports mostram tendências de glosa e materiais críticos.</p>
                    </article>
                </div>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>
