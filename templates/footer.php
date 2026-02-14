<?php
$footerVersion = app_latest_version($conn);
?>
<footer id="myFooterSimple" aria-label="Rodapé FullCare">
    <div class="footer-simple-inner">
        <a href="<?= $BASE_URL ?>inicio" class="footer-simple-brand" aria-label="Ir para início">
            <img src="<?= $BASE_URL ?>img/full-03.png" alt="FullCare">
        </a>
        <span class="footer-simple-copy">© 2022 FullCare - Accert Consult. Todos os direitos reservados.</span>
        <span class="footer-simple-version">Versão <?= htmlspecialchars((string)$footerVersion, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</footer>

<style>
#myFooterSimple {
    width: 100%;
    margin-top: 18px;
    background: #5e2363;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
}

#myFooterSimple .footer-simple-inner {
    min-height: 34px;
    padding: 4px 12px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    position: relative;
}

#myFooterSimple .footer-simple-brand {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
}

#myFooterSimple .footer-simple-brand img {
    width: 58px;
    max-width: 100%;
    height: auto;
}

#myFooterSimple .footer-simple-copy {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    color: #f3e8ff;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-align: center;
}

#myFooterSimple .footer-simple-version {
    margin-left: auto;
    color: #f3e8ff;
    font-size: 0.62rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    white-space: nowrap;
}

@media (max-width: 640px) {
    #myFooterSimple .footer-simple-inner {
        min-height: 30px;
        padding: 3px 8px;
    }

    #myFooterSimple .footer-simple-brand img {
        width: 50px;
    }

    #myFooterSimple .footer-simple-copy {
        font-size: 0.62rem;
    }

    #myFooterSimple .footer-simple-version {
        font-size: 0.56rem;
    }
}
</style>
