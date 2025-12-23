<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare • Inteligência aplicada</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div class="page ai-page">
        <header class="hero hero--ai" id="inteligencia">
            <nav class="hero__nav">
                <div class="brand">
                    <img src="../img/LogoFullCare.png" alt="FullCare" class="brand__logo">
                    <span class="brand__tagline">Gestão em saúde</span>
                </div>
                <div class="hero__nav-links">
                    <a href="./index.php#sobre">Sobre</a>
                    <a href="./index.php#diferenciais">Diferenciais</a>
                    <a href="./index.php#fluxo">Fluxo</a>
                    <a href="./inteligencia.php" class="is-active">Inteligência</a>
                    <a href="./index.php#contato">Contato</a>
                </div>
                <a class="btn btn--ghost" href="../index.php">Entrar no sistema</a>
            </nav>

            <div class="hero__body ai-hero">
                <div class="hero__copy">
                    <p class="eyebrow">IA proprietária FullCare</p>
                    <h1>
                        Inteligência aplicada que antecipa riscos,<br>
                        sugere prioridades e acelera a alta
                    </h1>
                    <p class="lead">
                        Nosso motor preditivo conecta dados assistenciais, faturamento e histórico clínico para gerar
                        recomendações acionáveis. A cada nova internação o modelo recalibra parâmetros e explica o que
                        motivou cada alerta.
                    </p>
                    <ul class="hero__bullets ai-bullets">
                        <li>Previsão de permanência com intervalo de confiança e alta estimada</li>
                        <li>Alertas dinâmicos de risco assistencial e glosa provável</li>
                        <li>Insights explicáveis para apoiar decisões entre equipe e operadora</li>
                    </ul>
                    <div class="ai-hero__metrics">
                        <div>
                            <span class="ai-metric__label">Previsões/ano</span>
                            <strong>40 mil+</strong>
                            <small>cross-hospital</small>
                        </div>
                        <div>
                            <span class="ai-metric__label">Precisão média</span>
                            <strong>92%</strong>
                            <small>jan/23 – dez/23</small>
                        </div>
                        <div>
                            <span class="ai-metric__label">Alertas diários</span>
                            <strong>~180</strong>
                            <small>priorizados por risco</small>
                        </div>
                    </div>
                </div>
                <div class="ai-hero__console">
                    <div class="ai-console__header">Motor preditivo • permanência</div>
                    <div class="ai-console__body">
                        <div class="ai-console__row">
                            <span>Paciente</span>
                            <strong>V.M.L (UTI)</strong>
                        </div>
                        <div class="ai-console__row">
                            <span>Dias atuais</span>
                            <strong>11</strong>
                        </div>
                        <div class="ai-console__row">
                            <span>Previsto total</span>
                            <strong>14.3 dias</strong>
                        </div>
                        <div class="ai-console__row">
                            <span>Alta estimada</span>
                            <strong>19/04</strong>
                        </div>
                        <div class="ai-console__row">
                            <span>Explicabilidade</span>
                            <strong>Ventilação 32% · Escore NEWS 21%</strong>
                        </div>
                        <div class="ai-console__row">
                            <span>Confiança</span>
                            <strong>94%</strong>
                        </div>
                    </div>
                    <div class="ai-console__footer">
                        Atualizado há 4 minutos · Modelo permanencia-lite-v1
                    </div>
                </div>
            </div>
        </header>

        <main>
            <section class="section ai-section">
                <div class="ai-section__intro">
                    <span class="eyebrow ai-eyebrow">Pilares da IA</span>
                    <h2>Data lake clínico + modelos proprietários</h2>
                    <p>
                        A inteligência FullCare recebe dados de prontuário, visitas, faturamento e eventos adversos em um
                        data lake único. Os modelos proprietários transformam essas entradas em previsões de permanência,
                        risco assistencial e oportunidades financeiras.
                    </p>
                </div>
                <div class="ai-grid">
                    <article class="ai-card" data-hover>
                        <div class="ai-card__icon">01</div>
                        <h3>Forecast de permanência</h3>
                        <p>Modelos regressivos e gradient boosting apontam o tempo restante indicado para cada
                            internação e recomendam ações de desospitalização segura.</p>
                        <span class="ai-card__tag">Atualização diária</span>
                    </article>
                    <article class="ai-card" data-hover>
                        <div class="ai-card__icon">02</div>
                        <h3>Score assistencial</h3>
                        <p>Combina sinais de visitas, UTI, oxigenoterapia e eventos críticos para ranquear pacientes por
                            risco e orientar a agenda da equipe.</p>
                        <span class="ai-card__tag">Explicável por fatores</span>
                    </article>
                    <article class="ai-card" data-hover>
                        <div class="ai-card__icon">03</div>
                        <h3>Insights financeiros</h3>
                        <p>Classificadores apontam glosas prováveis, negociações urgentes e contas paradas que precisam
                            de intervenção proativa.</p>
                        <span class="ai-card__tag">Integração RAH</span>
                    </article>
                </div>
            </section>

            <section class="section ai-section ai-section--split">
                <div class="ai-split">
                    <div>
                        <span class="eyebrow ai-eyebrow">Como operamos</span>
                        <h2>Pipeline de aprendizado contínuo</h2>
                        <p>
                            Cada entrega de dados retorna como feedback para o modelo. Monitoramos drifts de base,
                            validamos explicabilidade e liberamos atualizações com versionamento audível, garantindo
                            aderência à LGPD.
                        </p>
                        <ul class="ai-list">
                            <li><strong>Ingestão segura:</strong> ETLs automatizados com anonimização opcional.</li>
                            <li><strong>Feature store clínica:</strong> 200+ atributos assistenciais e financeiros.</li>
                            <li><strong>Monitoramento:</strong> dashboards de performance e alarmes de deriva.</li>
                            <li><strong>Governança:</strong> versionamento, aprovação e rollback em um clique.</li>
                        </ul>
                    </div>
                    <div class="ai-diagram">
                        <div class="ai-diagram__step">
                            <span>1</span>
                            <h4>Sinais assistenciais</h4>
                            <p>Visitas, checklists e protocolos alimentam o motor de risco.</p>
                        </div>
                        <div class="ai-diagram__step">
                            <span>2</span>
                            <h4>Modelagem híbrida</h4>
                            <p>Combina modelos lineares e árvores para equilibrar precisão e transparência.</p>
                        </div>
                        <div class="ai-diagram__step">
                            <span>3</span>
                            <h4>Explicabilidade</h4>
                            <p>Cada insight traz fatores explicativos para facilitar a negociação.</p>
                        </div>
                        <div class="ai-diagram__step">
                            <span>4</span>
                            <h4>Ação orquestrada</h4>
                            <p>Alertas enviados para dashboards e notificações dentro do fluxo.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section ai-section ai-section--panel">
                <div class="ai-panel">
                    <div>
                        <span class="eyebrow ai-eyebrow">Benefícios tangíveis</span>
                        <h2>IA aplicada ao dia a dia da auditoria</h2>
                        <p>
                            Crie listas priorizadas de pacientes, reduza permanências atípicas e negocie com precisão.
                            A inteligência do FullCare traduz dados complexos em ações práticas, com contexto clínico e
                            financeiro em um único lugar.
                        </p>
                    </div>
                    <div class="ai-panel__stats">
                        <div>
                            <strong>−18%</strong>
                            <span>Tempo médio de alta</span>
                        </div>
                        <div>
                            <strong>+27%</strong>
                            <span>Produtividade da visita</span>
                        </div>
                        <div>
                            <strong>−35%</strong>
                            <span>Glosas evitadas</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section cta ai-cta">
                <div>
                    <span class="eyebrow ai-eyebrow">Pronto para evoluir?</span>
                    <h2>Conheça o laboratório de IA FullCare</h2>
                    <p>Agende uma demonstração focada na inteligência: apresentamos dados reais, roadmaps e como integrar
                        o motor preditivo ao seu ecossistema.</p>
                </div>
                <div class="cta__actions">
                    <a href="mailto:contato@fullcare.com" class="btn btn--primary">Quero ver em ação</a>
                    <a href="./index.php#contato" class="btn btn--ghost">Retornar à home</a>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>© <?php echo date('Y'); ?> FullCare • Inteligência aplicada à auditoria</p>
        </footer>
    </div>

    <script src="./script.js"></script>
</body>

</html>
