<?php $footerVersion = app_latest_version($conn); ?>
<!DOCTYPE html>
<html>

<head>
    <title>Rodapé FullCare</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $BASE_URL ?>css/footer-with-button-logo.css">
    <style>
        #myFooter {
            background: #ffffff;
            padding: 32px 0 16px;
            border-top: 1px solid #e0e0e0;
            font-family: "Inter", "Segoe UI", sans-serif;
            width: 100%;
        }

        #myFooter .footer-content {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
            padding: 0 5vw;
            box-sizing: border-box;
        }

        #myFooter .footer-menu {
            display: flex;
            gap: 48px;
            flex-wrap: wrap;
        }

        #myFooter .footer-menu-column {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        #myFooter .footer-menu-column h5 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
        }

        #myFooter .footer-menu-column a {
            text-transform: none;
            letter-spacing: 0;
            font-weight: 400;
            color: #222;
            text-decoration: none;
            font-size: 0.85rem;
        }

        #myFooter .footer-menu-column a:hover {
            text-decoration: underline;
        }

        #myFooter .footer-brands img {
            width: 90px;
            filter: saturate(1.1);
        }

        #myFooter .footer-social {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 0.85rem;
        }

        #myFooter .footer-social a {
            color: #2c2c2c;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #myFooter .footer-social i {
            font-size: 1.05rem;
        }

        .footer-cta {
            margin-top: 4px;
            border-radius: 999px;
            padding: 8px 26px;
            background: #5e2363;
            color: #fff;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.25em;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        #myFooter .footer-bottom {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 5vw;
            margin-top: 18px;
            border-top: none;
            font-size: 0.82rem;
            color: #fff;
            flex-wrap: wrap;
            gap: 8px;
            box-sizing: border-box;
            background: #5e2363;
        }

        #myFooter .footer-bottom span {
            color: #fff;
        }

        #myFooter .footer-version {
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        @media (max-width: 840px) {
            #myFooter .footer-content {
                justify-content: center;
            }

            #myFooter .footer-menu {
                justify-content: center;
            }

            #myFooter .footer-bottom {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <footer id="myFooter">
        <div class="footer-content">
            <div class="footer-brands">
                <a href="<?= $BASE_URL ?>inicio">
                    <img src="<?= $BASE_URL ?>img/full-03.png" alt="FullCare">
                </a>
            </div>
            <div class="footer-menu">
                <div class="footer-menu-column">
                    <h5>Início</h5>
                    <a href="https://accertconsult.com.br/" target="_blank">Home</a>
                    <a href="https://accertconsult.com.br/produtos" target="_blank">Produtos</a>
                    <a href="https://www.accertconsult.com.br/sinistralidade" target="_blank">Sinistralidade</a>
                </div>
                <div class="footer-menu-column">
                    <h5>Sobre nós</h5>
                    <a href="https://accertconsult.com.br/" target="_blank">Informações da Empresa</a>
                    <a href="https://accertconsult.com.br/contato" target="_blank">Contato</a>
                    <a href="https://blog.fullcare.cloud/" target="_blank">Blog</a>
                </div>
                <div class="footer-menu-column">
                    <h5>Suporte</h5>
                    <a href="https://accertconsult.com.br/" target="_blank">FAQ</a>
                    <a href="https://accertconsult.com.br/contato" target="_blank">Telefones</a>
                    <a href="https://accertconsult.com.br/" target="_blank">Área restrita</a>
                </div>
            </div>
            <div class="footer-social">
                <a href="https://www.linkedin.com/in/accertconsult/" target="_blank"><i class="bi bi-linkedin"></i>Linkedin</a>
                <a href="https://accertconsult.com.br/" target="_blank"><i class="bi bi-facebook"></i>Facebook</a>
                <a href="https://www.instagram.com/accert_consult/" target="_blank"><i class="bi bi-instagram"></i>Instagram</a>
                <a href="https://accertconsult.com.br/contato" target="_blank">
                    <button class="footer-cta">Contato</button>
                </a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2022 FullCare – Accert Consult</span>
            <span class="footer-version">Versão <?= htmlspecialchars($footerVersion) ?></span>
        </div>
    </footer>
</body>

</html>
