<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare • Internações & Negociações</title>
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
                    <p class="eyebrow">Internações • Negociações</p>
                    <h1>Workflow completo de negociação dentro da internação</h1>
                    <p class="lead">Aba dedicada reúne histórico, simulações e aprovações. Capeante e dashboards passam a refletir os acordos automaticamente.</p>
                    <div class="module-hero__stats">
                        <span>Fluxo com aprovação da diretoria</span>
                        <span>Integração com capeante e faturamento</span>
                        <span>Dados enviados ao BI em tempo real</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="module-main">
            <section class="module-section">
                <h2>Cadastro orientado</h2>
                <p class="module-section__intro">Negociações podem ser abertas via internação ou diretamente pelo menu.
                    Cada registro leva motivo, valores, percentual e anexos obrigatórios para manter a trilha de auditoria completa.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Solicitação estruturada</h3>
                        <ul class="module-list">
                            <li>Campos obrigatórios guiam o auditor (motivo, valores, responsável).</li>
                            <li>Uploads aceitam PDFs/JPGs até 5MB e ficam vinculados ao histórico.</li>
                            <li>Botão “Salvar negociação” envia tudo para process_negociacao.php.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Simulação e comparativos</h3>
                        <ul class="module-list">
                            <li>Simulador mostra impacto direto na conta/RAH.</li>
                            <li>Indicadores exibem economia acumulada vs. meta.</li>
                            <li>Badge alerta quando há outra negociação em análise para o mesmo paciente.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Aprovação e notificações</h3>
                        <ul class="module-list">
                            <li>Status em análise, aprovado ou rejeitado com workflow definido.</li>
                            <li>E-mails automáticos para diretoria quando auditor marca “Aguardando aprovação”.</li>
                            <li>Logs completos com usuário, IP e horário.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="module-section">
                <h2>Integração com capeante e faturamento</h2>
                <p class="module-section__intro">Assim que um acordo é aprovado, valores negociados
                    alimentam os campos do capeante e travam alterações que possam gerar inconsistências.</p>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <h3>Edição sincronizada</h3>
                        <ul class="module-list">
                            <li>Campos críticos do capeante ficam bloqueados enquanto houver negociação pendente.</li>
                            <li>Aprovação preenche automaticamente os campos “Negociado”.</li>
                            <li>Resumo é exibido no topo do formulário com link para o histórico.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>PDF enriquecido</h3>
                        <ul class="module-list">
                            <li>PDF do capeante traz quadro com negociações aprovadas.</li>
                            <li>Corpo do e-mail descreve cada acordo como checklist.</li>
                            <li>Anexos enviados no fluxo são reaproveitados para o envio final.</li>
                        </ul>
                    </article>
                    <article class="module-detail-card">
                        <h3>Exportações e BI</h3>
                        <ul class="module-list">
                            <li>exportar_excel_negociacoes.php respeita filtros aplicados.</li>
                            <li>negociacoes_graficos.php expõe API usada pelo painel automatizado.</li>
                            <li>Jobs em export_negociacoes_graficos.php alimentam BI três vezes ao dia.</li>
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
                            <th>Destaque</th>
                            <th>Atualização</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>dashboard_mensal.php</td>
                            <td>Card “Economia por negociação” e metas por hospital.</td>
                            <td>Consulta em tempo real.</td>
                        </tr>
                        <tr>
                            <td>negociacoes_graficos.php</td>
                            <td>Gráficos de ticket médio, status e volume.</td>
                            <td>Re-renderiza a cada alteração aprovada.</td>
                        </tr>
                        <tr>
                            <td>export_negociacoes_graficos.php</td>
                            <td>CSV consolidado para BI externo.</td>
                            <td>Cron 06h / 12h / 18h.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="module-section">
                <h2>Por que importa</h2>
                <div class="module-grid">
                    <article class="module-detail-card">
                        <span class="module-tagline">Governança</span>
                        <p>Aprovações ficam registradas com logs e anexos, reduzindo retrabalhos.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Velocidade</span>
                        <p>Valores negociados já entram no capeante, agilizando geração de PDFs e envios.</p>
                    </article>
                    <article class="module-detail-card">
                        <span class="module-tagline">Transparência</span>
                        <p>Painéis automatizados mostram economia real e status por hospital.</p>
                    </article>
                </div>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>
