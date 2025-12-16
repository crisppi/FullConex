<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare • Eventos adversos</title>
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
                    <p class="eyebrow">Internações • Eventos adversos</p>
                    <h1>Registro e monitoramento de eventos ligados à internação</h1>
                    <p class="lead">Cadastro completo dentro da internação garante rastreabilidade clínica, financeira e de qualidade,
                        conectando-se a negociações, TUSS e capeante.</p>
                    <div class="module-hero__stats">
                        <span>Fluxo com uploads e timeline</span>
                        <span>Integração com capeante e negociações</span>
                        <span>Painéis automatizados para diretoria</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="module-main">
            <section class="module-section">
                <h2>Registro completo</h2>
                <p class="module-section__intro">Eventos podem ser clínicos, administrativos ou estruturais.
                    Cada registro captura contexto, responsável e plano de ação.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Cadastro rápido</h3>
                        <ul class="module-list">
                            <li>Botão “Novo evento” preenche paciente/hospital automaticamente.</li>
                            <li>Campos obrigatórios: tipo, data/hora, descrição e ação tomada.</li>
                            <li>Uploads armazenados em uploads/eventos/.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Análise interdisciplinar</h3>
                        <ul class="module-list">
                            <li>Status: Em apuração → Resolvido → Reportado.</li>
                            <li>Responsável e prazo visíveis na timeline.</li>
                            <li>Eventos críticos disparam e-mail automático (lista do globals.php).</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Encerramento com impacto</h3>
                        <ul class="module-list">
                            <li>Campo “Impacto financeiro” indica necessidade de negociação.</li>
                            <li>Logs ficam no arquivo logs/eventos.log.</li>
                            <li>Checklist garante anexação do retorno do hospital.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="module-section">
                <h2>Integração com outros módulos</h2>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Capeante e envios</h3>
                        <ul class="module-list">
                            <li>Eventos marcados como “Financeiros” aparecem no PDF do capeante.</li>
                            <li>Envio automático inclui link direto para o evento.</li>
                            <li>Itens vinculados ficam bloqueados até o evento ser resolvido.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Negociações e TUSS</h3>
                        <ul class="module-list">
                            <li>Campo “Vincular negociação” garante rastreabilidade.</li>
                            <li>TUSS posterior ao evento exige justificativa extra.</li>
                            <li>Exportações trazem coluna “Evento associado”.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Visitas e qualidade</h3>
                        <ul class="module-list">
                            <li>Timeline sinaliza visitas que precisam comentar o evento.</li>
                            <li>Dashboard de qualidade mostra eventos por categoria.</li>
                            <li>Alertas automáticos para eventos críticos não resolvidos.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="module-section">
                <h2>Painéis automatizados</h2>
                <table class="module-table">
                    <thead>
                        <tr>
                            <th>Painel</th>
                            <th>Visão</th>
                            <th>Periodicidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>dashboard_operacional.php</td>
                            <td>Volume por tipo e severidade.</td>
                            <td>Tempo real.</td>
                        </tr>
                        <tr>
                            <td>dashboard_performance.php</td>
                            <td>Correlação entre eventos e permanência média.</td>
                            <td>Job diário (scripts/jobs_eventos.sh).</td>
                        </tr>
                        <tr>
                            <td>manual_eventos_export.csv</td>
                            <td>Feed para BI com linha do tempo e heatmap.</td>
                            <td>Gerado às 03h.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="module-section">
                <h2>Benefícios</h2>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <span class="module-tagline">Rastreabilidade</span>
                        <p>Tudo fica ligado ao paciente, internação e capeante.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Decisões rápidas</span>
                        <p>Alertas e painéis mostram impacto clínico e financeiro.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Transparência</span>
                        <p>Negociações e TUSS carregam a referência do evento para auditorias futuras.</p>
                    </article>
                </div>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>
