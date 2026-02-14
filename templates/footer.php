<?php
$footerVersion = '1.3.2';
$footerYear = date('Y');
?>
<footer id="myFooterSimple" aria-label="Rodapé FullCare">
    <div class="footer-simple-inner">
        <div class="footer-simple-topline"></div>

        <div class="footer-simple-main">
            <a href="https://fullcare.cloud" target="_blank" rel="noopener noreferrer"
                class="footer-simple-brand" aria-label="Abrir fullcare.cloud">
                <img src="<?= $BASE_URL ?>img/full-03.png" alt="FullCare">
                <span class="footer-simple-brand-text">Gestão em Saúde</span>
            </a>

            <nav class="footer-simple-links" aria-label="Links rápidos">
                <a href="<?= $BASE_URL ?>inicio">Início</a>
                <a href="<?= $BASE_URL ?>internacoes/lista">Internações</a>
                <a href="<?= $BASE_URL ?>visitas/lista">Visitas</a>
                <a href="<?= $BASE_URL ?>bi/navegacao">BI</a>
            </nav>

            <div class="footer-simple-meta">
                <span class="footer-simple-copy">© <?= (int)$footerYear ?> FullCare - Accert Consult.</span>
                <span class="footer-simple-version">Versão <?= htmlspecialchars((string)$footerVersion, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>
</footer>

<style>
#myFooterSimple {
    width: 100%;
    margin-top: 20px;
    background: linear-gradient(90deg, #2f1640 0%, #5e2363 52%, #2e92be 100%);
    box-shadow: 0 -6px 18px rgba(26, 9, 39, 0.18);
}

#myFooterSimple .footer-simple-inner {
    position: relative;
}

#myFooterSimple .footer-simple-topline {
    height: 1px;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0.08));
}

#myFooterSimple .footer-simple-main {
    min-height: 54px;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    gap: 16px;
}

#myFooterSimple .footer-simple-brand {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

#myFooterSimple .footer-simple-brand img {
    width: 64px;
    max-width: 100%;
    height: auto;
}

#myFooterSimple .footer-simple-brand-text {
    color: rgba(244, 238, 255, 0.92);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

#myFooterSimple .footer-simple-links {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin: 0 auto;
}

#myFooterSimple .footer-simple-links a {
    color: rgba(246, 242, 255, 0.94);
    text-decoration: none;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.02em;
}

#myFooterSimple .footer-simple-links a:hover {
    color: #ffffff;
    text-decoration: underline;
    text-decoration-color: rgba(255, 255, 255, 0.55);
}

#myFooterSimple .footer-simple-meta {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

#myFooterSimple .footer-simple-copy,
#myFooterSimple .footer-simple-version {
    color: rgba(246, 242, 255, 0.95);
    font-size: 0.66rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    white-space: nowrap;
}

@media (max-width: 920px) {
    #myFooterSimple .footer-simple-main {
        flex-wrap: wrap;
        justify-content: center;
        row-gap: 6px;
    }

    #myFooterSimple .footer-simple-meta {
        margin-left: 0;
    }
}

@media (max-width: 640px) {
    #myFooterSimple .footer-simple-main {
        min-height: 64px;
        padding: 9px 10px;
        gap: 8px;
    }

    #myFooterSimple .footer-simple-brand img {
        width: 56px;
    }

    #myFooterSimple .footer-simple-brand-text,
    #myFooterSimple .footer-simple-links a,
    #myFooterSimple .footer-simple-copy {
        font-size: 0.6rem;
    }

    #myFooterSimple .footer-simple-version {
        font-size: 0.58rem;
    }
}
</style>
