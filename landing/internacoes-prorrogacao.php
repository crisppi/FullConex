<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare • Prorrogações de internação</title>
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
                    <p class="eyebrow">Internações • Prorrogações</p>
                    <h1>Solicitações e aprovações de prorrogação com rastreabilidade total</h1>
                    <p class="lead">Aba especial garante que novas datas sejam solicitadas, aprovadas e aplicadas automaticamente
                        no paciente, capeante e visitas. Sem recalcular nada manualmente.</p>
                    <div class="module-hero__stats">
                        <span>Workflow com logs completos</span>
                        <span>Atualização automática da linha do tempo</span>
                        <span>Alertas proativos para vencimentos</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="module-main">
            <section class="module-section">
                <h2>Solicitar e acompanhar</h2>
                <p class="module-section__intro">Auditores registram prorrogações em poucos cliques, anexando cartas e indicando
                    responsáveis. A diretoria aprova ou nega com feedback instantâneo.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Solicitação estruturada</h3>
                        <ul class="module-list">
                            <li>Campos para nova data sugerida, justificativa e responsável.</li>
                            <li>Upload de laudos/pareceres (opcional, mas recomendado).</li>
                            <li>Status inicia em “Solicitada” e notifica a seguradora.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Aprovação com governança</h3>
                        <ul class="module-list">
                            <li>Diretoria muda para “Aprovada” ou “Negada” com comentários.</li>
                            <li>Datas finais são atualizadas automaticamente quando aprovadas.</li>
                            <li>Negativa gera banner vermelho e bloqueia novas solicitações até ajuste.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Logs e histórico</h3>
                        <ul class="module-list">
                            <li>Linha do tempo registra responsável, horário e anexos.</li>
                            <li>Logs ficam em logs/prorrogacao.log (IP, navegador, usuário).</li>
                            <li>Exportações trazem coluna “Prorrogações” para BI.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="module-section">
                <h2>Impacto automático na operação</h2>
                <p class="module-section__intro">Ao aprovar uma prorrogação, o sistema ajusta visitas, capeante e alertas,
                    evitando planilhas paralelas e inconsistências.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Visitas e timeline</h3>
                        <ul class="module-list">
                            <li>Novas datas liberam visitas automaticamente para o período estendido.</li>
                            <li>Timeline mostra badge com data e responsável pela aprovação.</li>
                            <li>Visitas atrasadas levam em conta o novo intervalo.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Capeante e PDFs</h3>
                        <ul class="module-list">
                            <li>Formulário atualiza período da parcial automaticamente.</li>
                            <li>PDF destaca no cabeçalho a extensão do período.</li>
                            <li>Envio automatizado inclui a última carta de autorização.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Alertas e negociações</h3>
                        <ul class="module-list">
                            <li>Dashboards exibem card “Prorrogações pendentes”.</li>
                            <li>Negociações associadas exigem revalidação após nova data.</li>
                            <li>Cron diário alerta prorrogações prestes a vencer.</li>
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
                            <th>Indicadores</th>
                            <th>Fonte</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>dashboard_operacional.php</td>
                            <td>% de prorrogações aprovadas e tempo médio de resposta.</td>
                            <td>process_prorrogacao.php</td>
                        </tr>
                        <tr>
                            <td>diversos/export_prorrogacao.csv</td>
                            <td>Mapa de calor por hospital/seguradora no BI.</td>
                            <td>Job diário às 06h.</td>
                        </tr>
                        <tr>
                            <td>list_internacao.php</td>
                            <td>Badge verde/cinza indicando status de prorrogação.</td>
                            <td>SELECT com campos adicionais.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="module-section">
                <h2>Benefícios</h2>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <span class="module-tagline">Previsibilidade</span>
                        <p>Visitas, capeantes e envios seguem a nova data sem ajustes manuais.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Conformidade</span>
                        <p>Logs completos e histórico visível para auditoria e seguradora.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Agilidade</span>
                        <p>Alertas proativos reduzem o risco de deixar prazos vencerem.</p>
                    </article>
                </div>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>
