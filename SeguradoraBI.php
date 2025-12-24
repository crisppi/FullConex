<?php
include_once("check_logado.php");
require_once("templates/header.php");
?>

<link rel="stylesheet" href="<?= $BASE_URL ?>css/bi.css?v=20251226">
<script src="<?= $BASE_URL ?>js/bi.js?v=20251221"></script>
<script>document.addEventListener('DOMContentLoaded', () => document.body.classList.add('bi-theme'));</script>

<div class="bi-wrapper bi-theme">
    <div class="bi-header">
        <h1 class="bi-title">Seguradora</h1>
        <div class="bi-header-actions">
            <div class="text-end text-muted"></div>
            <a class="bi-nav-icon" href="<?= $BASE_URL ?>bi_navegacao.php" title="Navegacao">
                <i class="bi bi-grid-3x3-gap"></i>
            </a>
        </div>
    </div>

    <form class="bi-panel bi-filters" method="get">
        <div class="bi-filter">
            <label>Hospitais</label>
            <select>
                <option>Todos</option>
                <option>Bp Mirante - Hospital Sao Jose</option>
                <option>Hospital Do Coracao</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Mes</label>
            <select>
                <option>Todos</option>
                <option>Outubro</option>
                <option>Novembro</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Ano</label>
            <select>
                <option>Todos</option>
                <option>2024</option>
                <option>2025</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Nome Auditor</label>
            <select>
                <option>Todos</option>
                <option>Regina Silva</option>
                <option>Jorge Lemos</option>
            </select>
        </div>
        <div class="bi-filter">
            <label>Profissional Auditor</label>
            <select>
                <option>Todos</option>
                <option>Enfermeiro</option>
                <option>Medico</option>
            </select>
        </div>
        <div class="bi-actions">
            <button class="bi-btn" type="submit">Aplicar</button>
        </div>
    </form>

    <div class="bi-panel" style="margin-top:16px;">
        <h3 class="text-center" style="margin-bottom:12px;">Quantidade de Visitas</h3>
        <div class="table-responsive">
            <table class="bi-table">
                <thead>
                    <tr>
                        <th>Seguradora</th>
                        <th>Bp Mirante - Hospital Sao Jose</th>
                        <th>Hospital Do Coracao</th>
                        <th>Hospital Israelita Albert Einstein</th>
                        <th>Humana Magna</th>
                        <th>Santa Joana</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>BRADESCO</td>
                        <td>6</td>
                        <td>10</td>
                        <td>2</td>
                        <td>1</td>
                        <td>1</td>
                        <td>20</td>
                    </tr>
                    <tr>
                        <td>MEDISERVICE</td>
                        <td>15</td>
                        <td>4</td>
                        <td>4</td>
                        <td>0</td>
                        <td>0</td>
                        <td>23</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>21</td>
                        <td>14</td>
                        <td>6</td>
                        <td>1</td>
                        <td>1</td>
                        <td>43</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once("templates/footer.php"); ?>
