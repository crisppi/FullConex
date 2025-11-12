// js/rah-cadcentral.js
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-capeante-rah');
        if (!form) return;

        const selAtivar = document.getElementById('cadastro_central_cap');
        const selMed = document.getElementById('cad_central_med_id');
        const selEnf = document.getElementById('cad_central_enf_id');
        const selAdm = document.getElementById('cad_central_adm_id');
        const pill = document.getElementById('cc-pill');
        const hasCentralControls = !!selAtivar;

        // flags já existentes (s/n)
        const audMed = ensureFlagField('aud_med_capeante');
        const audEnf = ensureFlagField('aud_enf_capeante');
        const audAdm = ensureFlagField('aud_adm_capeante');

        const cbMed = ensureFlagField('med_check');
        const cbEnf = ensureFlagField('enfer_check');
        const cbAdm = ensureFlagField('adm_check');

        // >>> NOVO: campos ocultos com os NOMES <<<
        const nameMed = ensureNameField('aud_med_nome');
        const nameEnf = ensureNameField('aud_enf_nome');
        const nameAdm = ensureNameField('aud_adm_nome');

        const hasValue = (el) => {
            if (!el) return false;
            const v = String(el.value ?? '').trim();
            return v !== '' && v !== '0';
        };

        const readFlag = (el) => {
            if (!el) return 'n';
            if (el.type === 'checkbox') return el.checked ? 's' : 'n';
            return String(el.value || 'n') === 's' ? 's' : 'n';
        };

        const writeFlag = (el, v) => {
            if (!el) return;
            el.value = v;
            if (el.type === 'checkbox') el.checked = (v === 's');
        };

        const isAtivo = () => {
            if (hasCentralControls) return String(selAtivar.value) === 's';
            return [audMed, audEnf, audAdm].some((el) => readFlag(el) === 's');
        };

        const setRoleFlag = (selectEl, flagEl) => {
            const v = (isAtivo() && hasValue(selectEl)) ? 's' : 'n';
            writeFlag(flagEl, v);
        };

        // >>> NOVO: pega o TEXTO das opções e grava nos ocultos
        function setNames() {
            const getText = (sel) => {
                if (!sel) return '';
                const txt = (sel.selectedOptions && sel.selectedOptions[0] ? sel.selectedOptions[0].textContent : '').trim();
                if (!txt) return '';
                const low = txt.toLowerCase();
                if (low === 'selecione' || low === 'selecionar' || low === 'selecionar...' || low === 'selecionar …') {
                    return '';
                }
                return txt;
            };
            nameMed.value = getText(selMed);
            nameEnf.value = getText(selEnf);
            nameAdm.value = getText(selAdm);
        }

        function refreshFromSelects() {
            if (!hasCentralControls) {
                updatePill();
                setNames(); // mantém nomes atualizados mesmo sem o toggle
                return;
            }

            if (!isAtivo()) {
                [audMed, audEnf, audAdm, cbMed, cbEnf, cbAdm].forEach((el) => writeFlag(el, 'n'));
                setNames(); // zera nomes se desativado
                updatePill();
                return;
            }

            setRoleFlag(selMed, audMed);
            setRoleFlag(selEnf, audEnf);
            setRoleFlag(selAdm, audAdm);

            setRoleFlag(selMed, cbMed);
            setRoleFlag(selEnf, cbEnf);
            setRoleFlag(selAdm, cbAdm);

            setNames();
            updatePill();
        }

        function updatePill() {
            if (!pill) return;

            const medOn = readFlag(audMed) === 's' || readFlag(cbMed) === 's';
            const enfOn = readFlag(audEnf) === 's' || readFlag(cbEnf) === 's';
            const admOn = readFlag(audAdm) === 's' || readFlag(cbAdm) === 's';
            const ativo = isAtivo() || medOn || enfOn || admOn;

            if (!ativo) {
                pill.textContent = 'Desativado';
                pill.className = 'text-muted';
                return;
            }

            const partes = [];
            if (medOn) partes.push('Médico(a)');
            if (enfOn) partes.push('Enfermeiro(a)');
            if (admOn) partes.push('Adm');
            pill.textContent = partes.length ? ('Ativo • ' + partes.join(', ')) : 'Ativo';
            pill.className = '';
        }

        // listeners
        if (hasCentralControls) {
            selAtivar && selAtivar.addEventListener('change', refreshFromSelects);
            selMed && selMed.addEventListener('change', refreshFromSelects);
            selEnf && selEnf.addEventListener('change', refreshFromSelects);
            selAdm && selAdm.addEventListener('change', refreshFromSelects);

            // garante no submit
            form.addEventListener('submit', function () {
                const vAdm = (isAtivo() && hasValue(selAdm)) ? 's' : 'n';
                writeFlag(cbAdm, vAdm); writeFlag(audAdm, vAdm);

                const vMed = (isAtivo() && hasValue(selMed)) ? 's' : 'n';
                const vEnf = (isAtivo() && hasValue(selEnf)) ? 's' : 'n';
                writeFlag(cbMed, vMed); writeFlag(audMed, vMed);
                writeFlag(cbEnf, vEnf); writeFlag(audEnf, vEnf);

                setNames(); // <<< nomes garantidos no POST
            });
        }

        cbMed && cbMed.addEventListener('change', function () {
            writeFlag(audMed, this.checked ? 's' : 'n');
            updatePill();
        });
        cbEnf && cbEnf.addEventListener('change', function () {
            writeFlag(audEnf, this.checked ? 's' : 'n');
            updatePill();
        });
        cbAdm && cbAdm.addEventListener('change', function () {
            writeFlag(audAdm, this.checked ? 's' : 'n');
            updatePill();
        });

        // inicializa
        refreshFromSelects();
        setNames();

        // helpers
        function ensureFlagField(fieldName) {
            let el = form.querySelector(`[name="${cssEscape(fieldName)}"]`);
            if (el) return el;
            el = document.getElementById(fieldName);
            if (el && el.name === fieldName) return el;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = fieldName;
            hidden.value = 'n';           // flags -> 's' | 'n'
            form.appendChild(hidden);
            return hidden;
        }

        // >>> NOVO: campo oculto para NOME (string vazia por padrão)
        function ensureNameField(fieldName) {
            let el = form.querySelector(`[name="${cssEscape(fieldName)}"]`);
            if (el) return el;
            el = document.getElementById(fieldName);
            if (el && el.name === fieldName) return el;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = fieldName;
            hidden.value = '';            // nomes -> string
            form.appendChild(hidden);
            return hidden;
        }

        function cssEscape(str) {
            return String(str).replace(/([ !"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~])/g, '\\$1');
        }
    });
})();
