<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FullCare • Gestão hospitalar inteligente</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <div class="page">
        <header class="hero" id="home">
            <nav class="hero__nav">
                <div class="brand">
                    <img src="../img/logo_fullcare_branco_large.png" alt="FullCare • Gestão em Saúde" class="brand__full-logo">
                </div>
                <div class="hero__nav-links">
                    <a href="#sobre">Sobre</a>
                    <a href="#diferenciais">Diferenciais</a>
                    <a href="#fluxo">Fluxo</a>
<<<<<<< Updated upstream
                    <a href="#modulos">Módulos</a>
=======
                    <a href="./inteligencia.php">Inteligência</a>
>>>>>>> Stashed changes
                    <a href="#contato">Contato</a>
                </div>
                <a class="btn btn--ghost" href="../index.php">Entrar no sistema</a>
            </nav>

            <div class="hero__body">
                <div class="hero__copy">
                    <p class="eyebrow">Tecnologia + inteligência clínica</p>
                    <h1>
                        Gestão em auditoria <span class="hero__emphasis">inteligente</span><br>
                        e extraordinariamente eficiente
                    </h1>
                    <p class="lead">
                        Automatize auditorias, conecte equipes assistenciais e transforme dados clínicos em decisões
                        precisas. A FullCare integra processos críticos em um único fluxo, sem planilhas paralelas.
                    </p>
                    <ul class="hero__bullets">
                        <li>Auditoria online em tempo real</li>
                        <li>Insights preditivos sobre permanência e custos</li>
                        <li>BI integrado com indicadores de desempenho</li>
                    </ul>
                    <div class="hero__actions">
                        <a href="#contato" class="btn btn--primary">Solicitar demonstração</a>
                        <button class="btn btn--text" type="button" data-scroll="#sobre">Ver como funciona</button>
                    </div>
                    <div class="hero__stats">
                        <div>
                            <span class="hero__stats-number">24 mil+</span>
                            <span class="hero__stats-label">contas analisadas/ano</span>
                        </div>
                        <div>
                            <span class="hero__stats-number">18%</span>
                            <span class="hero__stats-label">redução média no tempo de alta</span>
                        </div>
                        <div>
                            <span class="hero__stats-number">LGPD ready</span>
                            <span class="hero__stats-label">segurança nível enterprise</span>
                        </div>
                    </div>
                </div>
                <div class="hero__visual">
                    <div class="visual-card">
                        <div class="visual-card__glow"></div>
                        <div class="visual-card__content">
                            <div class="visual-card__chip">FullCare Insights</div>
                            <p class="visual-card__value">UTI ocupada <strong>78%</strong></p>
                            <p class="visual-card__value">MP hospital <strong>8,6 dias</strong></p>
                            <div class="visual-card__divider"></div>
                            <p class="visual-card__value visual-card__value--highlight">Alertas ativos <strong>12</strong></p>
                            <span class="visual-card__tiny">Eventos adversos sinalizados e pacientes em risco elevado.</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <section class="section about" id="sobre">
                <div class="about__media">
                    <div class="about__poster">
                        <span class="spark spark--one"></span>
                        <span class="spark spark--two"></span>
                        <span class="spark spark--three"></span>
                        <div class="about__screen">
                            <p class="about__screen-label">Trilho assistencial</p>
                            <div class="about__screen-row">
                                <span>Internações em acompanhamento</span>
                                <strong>142</strong>
                            </div>
                            <div class="about__screen-row">
                                <span>Pacientes em UTI</span>
                                <strong>28</strong>
                            </div>
                            <div class="about__screen-row">
                                <span>Visitas pendentes</span>
                                <strong>07</strong>
                            </div>
                            <div class="about__screen-row">
                                <span>Negociações ativas</span>
                                <strong>19</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about__copy">
                    <span class="eyebrow">Sobre o sistema</span>
                    <h2>Monitoramento em tempo real e decisões baseadas em dados</h2>
                    <p>
                        A FullCare concentra auditoria concorrente, visitas in loco e gestão financeira em uma única
                        plataforma. Os fluxos são guiados por sugestões inteligentes, garantindo aderência aos protocolos
                        clínicos e rapidez nas negociações.
                    </p>
                    <div class="about__highlights">
                        <article>
                            <h3>Fluxos conectados</h3>
                            <p>Cadastro, visita, negociação e faturamento conversam entre si para evitar retrabalhos.</p>
                        </article>
                        <article>
                            <h3>Insights acionáveis</h3>
                            <p>Painéis preditivos apontam permanências atípicas, glosas prováveis e níveis de risco.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section diffs" id="diferenciais">
                <div class="section__intro">
                    <span class="eyebrow">Nossos diferenciais</span>
                    <h2>Mais que um sistema, um copiloto para a auditoria hospitalar</h2>
                    <p>Cards interativos que mostram o valor entregue em cada etapa do processo.</p>
                </div>
                <div class="diffs__grid">
                    <article class="diff-card" data-hover>
                        <div class="diff-card__icon">⚡</div>
                        <h3>Auditoria online</h3>
                        <p>Revise casos clínicos em tempo real, com trilhas de evidências e alertas automáticos de
                            inconsistências.</p>
                        <span class="diff-card__tag">Tempo médio de resposta: 4min</span>
                    </article>
                    <article class="diff-card" data-hover>
                        <div class="diff-card__icon">🛡️</div>
                        <h3>Segurança e LGPD</h3>
                        <p>Ambiente em cloud com criptografia, rastreabilidade completa e controles de acesso por perfil.</p>
                        <span class="diff-card__tag">Certificado ISO 27001</span>
                    </article>
                    <article class="diff-card" data-hover>
                        <div class="diff-card__icon">🧠</div>
                        <h3>Sistema inteligente</h3>
                        <p>Modelos de IA identificam tendências de permanência e sugerem próximos passos para a equipe.</p>
                        <span class="diff-card__tag">Insights diários</span>
                    </article>
                    <article class="diff-card" data-hover>
                        <div class="diff-card__icon">📊</div>
                        <h3>BI integrado</h3>
                        <p>Dashboards de desempenho com indicadores clínicos, financeiros e qualidade em um único clique.</p>
                        <span class="diff-card__tag">Atualização em tempo real</span>
                    </article>
                </div>
            </section>

            <section class="section flow" id="fluxo">
                <div class="section__intro">
                    <span class="eyebrow">Fluxo inteligente</span>
                    <h2>Da internação à alta com total visibilidade</h2>
                </div>
                <div class="flow__steps">
                    <article>
                        <span>1</span>
                        <h3>Cadastro assistido</h3>
                        <p>Campos condicionais e alertas de duplicidade evitam registros inconsistentes.</p>
                    </article>
                    <article>
                        <span>2</span>
                        <h3>Visita guiada</h3>
                        <p>Checklist contextual lembra eventos adversos, negociações e pendências do paciente.</p>
                    </article>
                    <article>
                        <span>3</span>
                        <h3>Negociação smart</h3>
                        <p>Comparativos históricos e simulador financeiro mostram o impacto de cada decisão.</p>
                    </article>
                    <article>
                        <span>4</span>
                        <h3>BI & indicadores</h3>
                        <p>Exportações automáticas, alertas proativos e integrações com o time executivo.</p>
                    </article>
                </div>
            </section>

            <section class="section modules" id="modulos">
                <div class="section__intro">
                    <span class="eyebrow">Módulos em foco</span>
                    <h2>Detalhe de funcionalidades avançadas</h2>
                    <p>Conheça fluxos críticos da operação que ganharam páginas dedicadas com exemplos e benefícios.</p>
                </div>
                <div class="modules__grid">
                    <a class="module-card" href="internacoes-tuss.php">
                        <span class="module-card__chip">Internações</span>
                        <h3>TUSS integrada</h3>
                        <p>Procedimentos autorizados e glosas com reflexo direto no capeante e nos envios automáticos.</p>
                        <span class="module-card__link">Ver detalhes →</span>
                    </a>
                    <a class="module-card" href="internacoes-negociacoes.php">
                        <span class="module-card__chip">Internações</span>
                        <h3>Negociações</h3>
                        <p>Workflow com aprovação, simulações e registros prontos para PDF e dashboards.</p>
                        <span class="module-card__link">Ver detalhes →</span>
                    </a>
                    <a class="module-card" href="internacoes-prorrogacao.php">
                        <span class="module-card__chip">Internações</span>
                        <h3>Prorrogações inteligentes</h3>
                        <p>Solicitação estruturada, atualização automática das datas e alertas proativos.</p>
                        <span class="module-card__link">Ver detalhes →</span>
                    </a>
                    <a class="module-card" href="internacoes-evento-adverso.php">
                        <span class="module-card__chip">Qualidade</span>
                        <h3>Eventos adversos</h3>
                        <p>Registro com impacto financeiro, vínculo ao capeante e visualização em painéis automatizados.</p>
                        <span class="module-card__link">Ver detalhes →</span>
                    </a>
                </div>
            </section>

            <section class="section cta" id="contato">
                <div>
                    <span class="eyebrow">Pronto para avançar?</span>
                    <h2>Leve a inteligência FullCare para sua auditoria</h2>
                    <p>Apresente os desafios atuais e receba um plano personalizado com as rotas mais rápidas de ganho de
                        eficiência.</p>
                </div>
                <div class="cta__actions">
                    <a href="mailto:contato@fullcare.com" class="btn btn--primary">Quero falar com especialista</a>
                    <a href="../manual.html" class="btn btn--ghost">Ver materiais</a>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>© <?php echo date('Y'); ?> FullCare • Gestão em saúde inteligente</p>
        </footer>
    </div>

    <script src="./script.js"></script>
</body>

</html>
