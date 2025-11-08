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

        const audMed = ensureField('aud_med_capeante');
        const audEnf = ensureField('aud_enf_capeante');
        const audAdm = ensureField('aud_adm_capeante');

        const cbMed = ensureField('med_check');
        const cbEnf = ensureField('enfer_check');
        const cbAdm = ensureField('adm_check');

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

        function refreshFromSelects() {
            if (!hasCentralControls) {
                updatePill();
                return;
            }

            if (!isAtivo()) {
                [audMed, audEnf, audAdm, cbMed, cbEnf, cbAdm].forEach((el) => writeFlag(el, 'n'));
                updatePill();
                return;
            }

            setRoleFlag(selMed, audMed);
            setRoleFlag(selEnf, audEnf);
            setRoleFlag(selAdm, audAdm);

            setRoleFlag(selMed, cbMed);
            setRoleFlag(selEnf, cbEnf);
            setRoleFlag(selAdm, cbAdm);

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

        if (hasCentralControls) {
            selAtivar && selAtivar.addEventListener('change', refreshFromSelects);
            selMed && selMed.addEventListener('change', refreshFromSelects);
            selEnf && selEnf.addEventListener('change', refreshFromSelects);

            selAdm && selAdm.addEventListener('change', function () {
                const v = (isAtivo() && hasValue(selAdm)) ? 's' : 'n';
                writeFlag(audAdm, v);
                writeFlag(cbAdm, v);
                updatePill();
            });

            form.addEventListener('submit', function () {
                const vAdm = (isAtivo() && hasValue(selAdm)) ? 's' : 'n';
                writeFlag(cbAdm, vAdm);
                writeFlag(audAdm, vAdm);

                const vMed = (isAtivo() && hasValue(selMed)) ? 's' : 'n';
                const vEnf = (isAtivo() && hasValue(selEnf)) ? 's' : 'n';
                writeFlag(cbMed, vMed); writeFlag(audMed, vMed);
                writeFlag(cbEnf, vEnf); writeFlag(audEnf, vEnf);
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

        refreshFromSelects();

        function ensureField(fieldName) {
            let el = form.querySelector(`[name="${cssEscape(fieldName)}"]`);
            if (el) return el;

            el = document.getElementById(fieldName);
            if (el && el.name === fieldName) return el;

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = fieldName;
            hidden.value = 'n';
            form.appendChild(hidden);
            return hidden;
        }

        function cssEscape(str) {
            return String(str).replace(/([ !"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~])/g, '\\$1');
        }
    });
})();
